<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Role;
use App\Models\Permission;
use App\Models\RolePermission;
use App\Models\AdminRole;
use App\Models\Admin;

/**
 * Admin Role Controller
 * Manages roles and permissions administration functionality
 */
class RoleController extends Controller
{
    /**
     * Display unified roles and permissions management page
     */
    public function index(): void
    {
        $this->requireAdmin();
        $this->requirePermission('manage_roles');
        
        try {
            $db = $this->container->get('db');
            
            // Get roles with permission and user counts
            $query = "
                SELECT r.*, 
                       COUNT(DISTINCT rp.permission_id) as permission_count,
                       COUNT(DISTINCT ar.admin_id) as user_count
                FROM roles r
                LEFT JOIN role_permissions rp ON r.id = rp.role_id
                LEFT JOIN admin_roles ar ON r.id = ar.role_id
                GROUP BY r.id
                ORDER BY r.name
            ";
            
            $stmt = $db->prepare($query);
            $stmt->execute();
            $roles = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Get all permissions
            $stmt = $db->prepare("SELECT * FROM permissions ORDER BY name");
            $stmt->execute();
            $permissions = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Get role-permission matrix
            $stmt = $db->prepare("SELECT role_id, permission_id FROM role_permissions");
            $stmt->execute();
            $rolePermissionData = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Build role-permission matrix
            $rolePermissionMatrix = [];
            foreach ($rolePermissionData as $rp) {
                if (!isset($rolePermissionMatrix[$rp['role_id']])) {
                    $rolePermissionMatrix[$rp['role_id']] = [];
                }
                $rolePermissionMatrix[$rp['role_id']][] = $rp['permission_id'];
            }
            
            $permissionGroups = $this->groupPermissions($permissions);
            
            $data = [
                'roles' => $roles,
                'permissions' => $permissions,
                'permissionGroups' => $permissionGroups,
                'rolePermissionMatrix' => $rolePermissionMatrix
            ];
            
            $this->render('admin/roles/index', $data, 'admin');
            
        } catch (\Exception $e) {
            logger('Role management page error: ' . $e->getMessage(), 'error');
            $this->session->error('Error loading roles management');
            $this->redirect('/admin');
        }
    }

    /**
     * Update role-permission assignments (bulk update)
     */
    public function updatePermissions(): void
    {
        $this->requireAdmin();
        $this->requirePermission('manage_roles');
        $this->requireCsrfToken();
        
        $input = json_decode(file_get_contents('php://input'), true);
        $assignments = $input['assignments'] ?? [];
        
        try {
            $db = $this->container->get('db');
            $db->beginTransaction();
            
            foreach ($assignments as $roleId => $permissionIds) {
                // Skip Super Admin role
                $stmt = $db->prepare("SELECT name FROM roles WHERE id = ?");
                $stmt->execute([$roleId]);
                $role = $stmt->fetch(\PDO::FETCH_ASSOC);
                
                if ($role && $role['name'] === 'Super Admin') {
                    continue;
                }
                
                // Remove existing permissions for this role
                $stmt = $db->prepare("DELETE FROM role_permissions WHERE role_id = ?");
                $stmt->execute([$roleId]);
                
                // Add new permissions
                if (!empty($permissionIds)) {
                    $stmt = $db->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
                    foreach ($permissionIds as $permissionId) {
                        $stmt->execute([$roleId, $permissionId]);
                    }
                }
            }
            
            $db->commit();
            $this->json(['success' => true, 'message' => 'Permissions updated successfully']);
            
        } catch (\Exception $e) {
            $db->rollback();
            logger('Permission update error: ' . $e->getMessage(), 'error');
            $this->json(['success' => false, 'message' => 'Error updating permissions']);
        }
    }

    /**
     * Store new role (for inline creation)
     */
    public function store(): void
    {
        $this->requireAdmin();
        $this->requirePermission('manage_roles');
        $this->requireCsrfToken();
        
        $data = $this->validate($this->all(), [
            'name' => 'required|max:50|unique:roles',
            'description' => 'nullable|max:255'
        ]);
        
        try {
            $db = $this->container->get('db');
            
            $stmt = $db->prepare("
                INSERT INTO roles (name, description, created_at, updated_at) 
                VALUES (?, ?, NOW(), NOW())
            ");
            $stmt->execute([$data['name'], $data['description']]);
            
            $this->json(['success' => true, 'message' => 'Role created successfully']);
            
        } catch (\Exception $e) {
            logger('Role create error: ' . $e->getMessage(), 'error');
            $this->json(['success' => false, 'message' => 'Error creating role']);
        }
    }

    /**
     * Update role (for inline editing)
     */
    public function update($id = null): void
    {
        $this->requireAdmin();
        $this->requirePermission('manage_roles');
        $this->requireCsrfToken();
        
        if (!$id || !is_numeric($id)) {
            $this->json(['success' => false, 'message' => 'Role not found']);
            return;
        }
        
        $data = $this->validate($this->all(), [
            'name' => "required|max:50|unique:roles,name,$id",
            'description' => 'nullable|max:255'
        ]);
        
        try {
            $db = $this->container->get('db');
            
            // Check if role exists
            $stmt = $db->prepare("SELECT name FROM roles WHERE id = ?");
            $stmt->execute([$id]);
            $role = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$role) {
                $this->json(['success' => false, 'message' => 'Role not found']);
                return;
            }
            
            // Prevent editing Super Admin role name
            if ($role['name'] === 'Super Admin' && $data['name'] !== 'Super Admin') {
                $this->json(['success' => false, 'message' => 'Cannot modify Super Admin role name']);
                return;
            }
            
            // Update role
            $stmt = $db->prepare("
                UPDATE roles 
                SET name = ?, description = ?, updated_at = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$data['name'], $data['description'], $id]);
            
            $this->json(['success' => true, 'message' => 'Role updated successfully']);
            
        } catch (\Exception $e) {
            logger('Role update error: ' . $e->getMessage(), 'error');
            $this->json(['success' => false, 'message' => 'Error updating role']);
        }
    }

    /**
     * Delete role
     */
    public function delete($id = null): void
    {
        $this->requireAdmin();
        $this->requirePermission('manage_roles');
        $this->requireCsrfToken();
        
        // Get ID from URL parameter or request
        $id = $id ?: $this->input('id');
        
        if (!$id || !is_numeric($id)) {
            $this->json(['success' => false, 'message' => 'Role not found']);
            return;
        }
        
        try {
            $db = $this->container->get('db');
            
            // Get role
            $stmt = $db->prepare("SELECT name FROM roles WHERE id = ?");
            $stmt->execute([$id]);
            $role = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$role) {
                $this->json(['success' => false, 'message' => 'Role not found']);
                return;
            }
            
            // Prevent deletion of Super Admin
            if ($role['name'] === 'Super Admin') {
                $this->json(['success' => false, 'message' => 'Cannot delete Super Admin role']);
                return;
            }
            
            // Check if role has users
            $stmt = $db->prepare("SELECT COUNT(*) FROM admin_roles WHERE role_id = ?");
            $stmt->execute([$id]);
            $userCount = $stmt->fetchColumn();
            
            if ($userCount > 0) {
                $this->json(['success' => false, 'message' => 'Cannot delete role with assigned users']);
                return;
            }
            
            $db->beginTransaction();
            
            // Delete role permissions
            $stmt = $db->prepare("DELETE FROM role_permissions WHERE role_id = ?");
            $stmt->execute([$id]);
            
            // Delete role
            $stmt = $db->prepare("DELETE FROM roles WHERE id = ?");
            $stmt->execute([$id]);
            
            $db->commit();
            $this->json(['success' => true, 'message' => 'Role deleted successfully']);
            
        } catch (\Exception $e) {
            $db->rollback();
            logger('Role delete error: ' . $e->getMessage(), 'error');
            $this->json(['success' => false, 'message' => 'Error deleting role']);
        }
    }

    /**
     * Create new permission (inline)
     */
    public function createPermission(): void
    {
        $this->requireAdmin();
        $this->requirePermission('manage_roles');
        $this->requireCsrfToken();
        
        try {
            $db = $this->container->get('db');
            
            // Get and validate input manually
            $name = trim($this->input('name', ''));
            $description = trim($this->input('description', ''));
            
            if (empty($name)) {
                $this->json(['success' => false, 'message' => 'Permission name is required']);
                return;
            }
            
            if (strlen($name) > 100) {
                $this->json(['success' => false, 'message' => 'Permission name too long']);
                return;
            }
            
            // Check for duplicates
            $stmt = $db->prepare("SELECT id FROM permissions WHERE name = ?");
            $stmt->execute([$name]);
            if ($stmt->fetch()) {
                $this->json(['success' => false, 'message' => 'Permission already exists']);
                return;
            }
            
            // Create permission
            $stmt = $db->prepare("
                INSERT INTO permissions (name, description, created_at, updated_at) 
                VALUES (?, ?, NOW(), NOW())
            ");
            $stmt->execute([$name, $description]);
            
            $this->json(['success' => true, 'message' => 'Permission created successfully']);
            
        } catch (\Exception $e) {
            logger('Permission create error: ' . $e->getMessage(), 'error');
            $this->json(['success' => false, 'message' => 'Error creating permission']);
        }
    }

    /**
     * Update permission (inline)
     */
    public function updatePermission($id = null): void
    {
        $this->requireAdmin();
        $this->requirePermission('manage_roles');
        $this->requireCsrfToken();
        
        if (!$id || !is_numeric($id)) {
            $this->json(['success' => false, 'message' => 'Permission not found']);
            return;
        }
        
        $data = $this->validate($this->all(), [
            'name' => "required|max:100|unique:permissions,name,$id",
            'description' => 'nullable|max:255'
        ]);
        
        try {
            $db = $this->container->get('db');
            
            // Check if permission exists and if it's critical
            $stmt = $db->prepare("SELECT name FROM permissions WHERE id = ?");
            $stmt->execute([$id]);
            $permission = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$permission) {
                $this->json(['success' => false, 'message' => 'Permission not found']);
                return;
            }
            
            $criticalPermissions = ['manage_all', 'manage_roles'];
            if (in_array($permission['name'], $criticalPermissions) && !in_array($data['name'], $criticalPermissions)) {
                $this->json(['success' => false, 'message' => 'Cannot modify critical permission']);
                return;
            }
            
            $stmt = $db->prepare("
                UPDATE permissions 
                SET name = ?, description = ?, updated_at = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$data['name'], $data['description'], $id]);
            
            $this->json(['success' => true, 'message' => 'Permission updated successfully']);
            
        } catch (\Exception $e) {
            logger('Permission update error: ' . $e->getMessage(), 'error');
            $this->json(['success' => false, 'message' => 'Error updating permission']);
        }
    }

    /**
     * Delete permission
     */
    public function deletePermission($id = null): void
    {
        $this->requireAdmin();
        $this->requirePermission('manage_roles');
        $this->requireCsrfToken();
        
        // Get ID from URL parameter or request
        $id = $id ?: $this->input('id');
        
        if (!$id || !is_numeric($id)) {
            $this->json(['success' => false, 'message' => 'Permission not found']);
            return;
        }
        
        try {
            $db = $this->container->get('db');
            
            // Check if permission exists
            $stmt = $db->prepare("SELECT name FROM permissions WHERE id = ?");
            $stmt->execute([$id]);
            $permission = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$permission) {
                $this->json(['success' => false, 'message' => 'Permission not found']);
                return;
            }
            
            // Prevent deletion of critical permissions
            $criticalPermissions = ['manage_all', 'manage_roles'];
            if (in_array($permission['name'], $criticalPermissions)) {
                $this->json(['success' => false, 'message' => 'Cannot delete critical permission']);
                return;
            }
            
            $db->beginTransaction();
            
            // Remove from role assignments
            $stmt = $db->prepare("DELETE FROM role_permissions WHERE permission_id = ?");
            $stmt->execute([$id]);
            
            // Delete permission
            $stmt = $db->prepare("DELETE FROM permissions WHERE id = ?");
            $stmt->execute([$id]);
            
            $db->commit();
            $this->json(['success' => true, 'message' => 'Permission deleted successfully']);
            
        } catch (\Exception $e) {
            $db->rollback();
            logger('Permission delete error: ' . $e->getMessage(), 'error');
            $this->json(['success' => false, 'message' => 'Error deleting permission']);
        }
    }

    /**
     * Group permissions by category for better UI organization
     */
    private function groupPermissions($permissions): array
    {
        $groups = [];
        
        foreach ($permissions as $permission) {
            $parts = explode('_', $permission['name']);
            $action = $parts[0]; // manage, view, edit, etc.
            
            if (!isset($groups[$action])) {
                $groups[$action] = [];
            }
            
            $groups[$action][] = $permission;
        }
        
        // Define custom group order
        $order = ['manage', 'view', 'edit', 'send'];
        $orderedGroups = [];
        
        foreach ($order as $key) {
            if (isset($groups[$key])) {
                $orderedGroups[$key] = $groups[$key];
                unset($groups[$key]);
            }
        }
        
        // Add remaining groups
        foreach ($groups as $key => $group) {
            $orderedGroups[$key] = $group;
        }
        
        return $orderedGroups;
    }
}
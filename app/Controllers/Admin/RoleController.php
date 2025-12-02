<?php

namespace App\Controllers\Admin;

use App\Core\Controller;

class RoleController extends Controller
{
    public function index(): void
    {
        $this->requireAdmin();
        $this->requirePermission('manage_roles');

        try {
            $db = $this->container->get('db');
            $query = "
                SELECT r.*,
                       COUNT(DISTINCT rp.permission_id) as permission_count,
                       COUNT(DISTINCT ar.admin_id) as user_count
                FROM roles r
                LEFT JOIN role_permissions rp ON r.id = rp.role_id
                LEFT JOIN admin_roles ar ON r.id = ar.role_id
                GROUP BY r.id ORDER BY r.name
            ";
            $roles = $db->query($query)->fetchAll(\PDO::FETCH_ASSOC);

            $permissions = $db->query("SELECT * FROM permissions ORDER BY name")->fetchAll(\PDO::FETCH_ASSOC);
            $permissionGroups = $this->groupPermissions($permissions);

            $rolePermissionData = $db->query("SELECT role_id, permission_id FROM role_permissions")->fetchAll(\PDO::FETCH_ASSOC);
            $rolePermissionMatrix = [];
            foreach ($rolePermissionData as $rp) {
                $rolePermissionMatrix[$rp['role_id']][] = $rp['permission_id'];
            }
            
            $this->render('admin/roles/index', compact('roles', 'permissions', 'permissionGroups', 'rolePermissionMatrix'), 'admin');

        } catch (\Exception $e) {
            logger('Role management page error: ' . $e->getMessage(), 'error');
            $this->session->error('Error loading roles management page.');
            $this->redirect('/admin');
        }
    }

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
            $stmt = $db->prepare("INSERT INTO roles (name, description) VALUES (?, ?)");
            $stmt->execute([$data['name'], $data['description'] ?? null]);
            $this->session->success('Role created successfully.');
        } catch (\Exception $e) {
            logger('Role create error: ' . $e->getMessage(), 'error');
            $this->session->error('Error creating role.');
        }
        $this->redirect('/admin/roles');
    }

    public function update($id = null): void
    {
        $this->requireAdmin();
        $this->requirePermission('manage_roles');
        $this->requireCsrfToken();
        
        // **THIS IS THE CRITICAL FIX**
        // The validation rule now correctly excludes the current role's ID.
        $data = $this->validate($this->all(), [
            'name' => "required|max:50|unique:roles,{$id}",
            'description' => 'nullable|max:255'
        ]);

        try {
            $db = $this->container->get('db');
            $stmt = $db->prepare("UPDATE roles SET name = ?, description = ? WHERE id = ?");
            $stmt->execute([$data['name'], $data['description'] ?? null, $id]);
            $this->session->success('Role updated successfully.');
        } catch (\Exception $e) {
            logger('Role update error: ' . $e->getMessage(), 'error');
            $this->session->error('Error updating role.');
        }
        $this->redirect('/admin/roles');
    }

    public function delete($id = null): void
    {
        $this->requireAdmin();
        $this->requirePermission('manage_roles');
        $this->requireCsrfToken();
        
        try {
            $db = $this->container->get('db');
            // NOTE: Add checks here to prevent deleting roles in use
            $stmt = $db->prepare("DELETE FROM roles WHERE id = ?");
            $stmt->execute([$id]);
            $this->session->success('Role deleted successfully.');
        } catch (\Exception $e) {
            logger('Role delete error: ' . $e->getMessage(), 'error');
            $this->session->error('Error deleting role.');
        }
        $this->redirect('/admin/roles');
    }
    
    public function updatePermissions(): void
    {
        $this->requireAdmin();
        $this->requirePermission('manage_roles');
        $this->requireCsrfToken();
        
        $assignments = $this->input('assignments', []);

        try {
            $db = $this->container->get('db');
            $db->beginTransaction();

            $db->exec("DELETE FROM role_permissions"); // Simple approach: clear all first

            $stmt = $db->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
            foreach ($assignments as $roleId => $permissionIds) {
                if (is_array($permissionIds)) {
                    foreach ($permissionIds as $permissionId) {
                        $stmt->execute([$roleId, $permissionId]);
                    }
                }
            }
            
            $db->commit();
            $this->session->success('Permissions updated successfully.');
        } catch (\Exception $e) {
            $db->rollBack();
            logger('Permission update error: ' . $e->getMessage(), 'error');
            $this->session->error('Error updating permissions.');
        }
        $this->redirect('/admin/roles');
    }

    private function groupPermissions($permissions): array
    {
        $groups = [];
        foreach ($permissions as $permission) {
            $groupName = ucfirst(explode('_', $permission['name'])[0]);
            $groups[$groupName][] = $permission;
        }
        return $groups;
    }
}
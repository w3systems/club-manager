<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Admin;
use App\Models\Role;

class UserController extends Controller
{
    /**
     * Display a list of admin users.
     */
    public function index(): void
    {
        $this->requireAdmin();
        $this->requirePermission('manage_users');
        
        $db = $this->container->get('db');
        $query = "
            SELECT a.id, a.first_name, a.last_name, a.email, a.created_at, GROUP_CONCAT(r.name SEPARATOR ', ') as roles
            FROM admins a
            LEFT JOIN admin_roles ar ON a.id = ar.admin_id
            LEFT JOIN roles r ON ar.role_id = r.id
            GROUP BY a.id
            ORDER BY a.last_name, a.first_name
        ";
        $admins = $db->query($query)->fetchAll(\PDO::FETCH_ASSOC);

        $this->render('admin/users/index', ['admins' => $admins], 'admin');
    }

    /**
     * Show the form for creating a new admin user.
     */
    public function create(): void
    {
        $this->requireAdmin();
        $this->requirePermission('manage_users');

        $roles = Role::all();
        // Pass an empty admin object and an empty array for roles
        $this->render('admin/users/form', [
            'roles' => $roles,
            'admin' => (object)[], 
            'adminRoles' => []
        ], 'admin');
    }

    /**
     * Store a new admin user in the database.
     */
    public function store(): void
    {
        $this->requireAdmin();
        $this->requirePermission('manage_users');
        $this->requireCsrfToken();

        $data = $this->validate($this->all(), [
            'first_name' => 'required|max:50',
            'last_name' => 'required|max:50',
            'email' => 'required|email|unique:admins',
            'password' => 'required|min:8|confirmed',
            'roles' => 'required|array'
        ]);

        try {
            $db = $this->container->get('db');
            $db->beginTransaction();

            // Create the admin user
            $stmt = $db->prepare("INSERT INTO admins (first_name, last_name, email, password_hash) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $data['first_name'],
                $data['last_name'],
                $data['email'],
                password_hash($data['password'], PASSWORD_DEFAULT)
            ]);
            $adminId = $db->lastInsertId();

            // Assign roles
            $stmt = $db->prepare("INSERT INTO admin_roles (admin_id, role_id) VALUES (?, ?)");
            foreach ($data['roles'] as $roleId) {
                $stmt->execute([$adminId, $roleId]);
            }

            $db->commit();
            $this->session->success('Admin user created successfully.');
        } catch (\Exception $e) {
            $db->rollBack();
            logger('Admin user create error: ' . $e->getMessage(), 'error');
            $this->session->error('Error creating admin user.');
        }

        $this->redirect('/admin/users');
    }

    /**
     * Show the form for editing an admin user.
     */
    public function edit($id): void
    {
        $this->requireAdmin();
        $this->requirePermission('manage_users');

        $admin = Admin::find($id);
        if (!$admin) {
            $this->session->error('Admin user not found.');
            $this->redirect('/admin/users');
            return; // Add return to stop execution
        }

        $roles = Role::all();
        // Get an array of just the role IDs for easy checking in the view
        $adminRoles = array_map(fn($role) => $role->id, $admin->getRoles());

        $this->render('admin/users/form', [
            'admin' => $admin,
            'roles' => $roles,
            'adminRoles' => $adminRoles
        ], 'admin');
    }

    /**
     * Update an admin user in the database.
     */
    public function update($id): void
    {
        $this->requireAdmin();
        $this->requirePermission('manage_users');
        $this->requireCsrfToken();

        $rules = [
            'first_name' => 'required|max:50',
            'last_name' => 'required|max:50',
            'email' => "required|email|unique:admins,{$id}",
            'roles' => 'required|array'
        ];

        // Only validate password if it's being changed
        if (!empty($this->input('password'))) {
            $rules['password'] = 'min:8|confirmed';
        }

        $data = $this->validate($this->all(), $rules);

        try {
            $db = $this->container->get('db');
            $db->beginTransaction();

            // Update admin details
            if (!empty($data['password'])) {
                $stmt = $db->prepare("UPDATE admins SET first_name = ?, last_name = ?, email = ?, password_hash = ? WHERE id = ?");
                $stmt->execute([$data['first_name'], $data['last_name'], $data['email'], password_hash($data['password'], PASSWORD_DEFAULT), $id]);
            } else {
                $stmt = $db->prepare("UPDATE admins SET first_name = ?, last_name = ?, email = ? WHERE id = ?");
                $stmt->execute([$data['first_name'], $data['last_name'], $data['email'], $id]);
            }

            // Sync roles
            $stmt = $db->prepare("DELETE FROM admin_roles WHERE admin_id = ?");
            $stmt->execute([$id]);

            $stmt = $db->prepare("INSERT INTO admin_roles (admin_id, role_id) VALUES (?, ?)");
            foreach ($data['roles'] as $roleId) {
                $stmt->execute([$id, $roleId]);
            }

            $db->commit();
            $this->session->success('Admin user updated successfully.');
        } catch (\Exception $e) {
            $db->rollBack();
            logger('Admin user update error: ' . $e->getMessage(), 'error');
            $this->session->error('Error updating admin user.');
        }

        $this->redirect('/admin/users');
    }

    /**
     * Delete an admin user.
     */
    public function delete($id): void
    {
        $this->requireAdmin();
        $this->requirePermission('manage_users');
        $this->requireCsrfToken();

        // Prevent user from deleting themselves
        if ($id == $this->auth->id()) {
            $this->session->error('You cannot delete your own account.');
            $this->redirect('/admin/users');
        }

        try {
            $db = $this->container->get('db');
            $db->beginTransaction();
            // Remove role assignments first
            $stmt = $db->prepare("DELETE FROM admin_roles WHERE admin_id = ?");
            $stmt->execute([$id]);
            // Then delete the admin
            $stmt = $db->prepare("DELETE FROM admins WHERE id = ?");
            $stmt->execute([$id]);
            $db->commit();
            $this->session->success('Admin user deleted successfully.');
        } catch (\Exception $e) {
            $db->rollBack();
            logger('Admin user delete error: ' . $e->getMessage(), 'error');
            $this->session->error('Error deleting admin user.');
        }

        $this->redirect('/admin/users');
    }
}
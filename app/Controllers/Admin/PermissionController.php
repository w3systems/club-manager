<?php

namespace App\Controllers\Admin;

use App\Core\Controller;

class PermissionController extends Controller
{
    /**
     * Store a new permission.
     */
    public function store(): void
    {
        $this->requireAdmin();
        $this->requirePermission('manage_roles');
        $this->requireCsrfToken();
        
        $data = $this->validate($this->all(), [
            'name' => 'required|max:50|unique:permissions',
            'description' => 'nullable|max:255'
        ]);
        
        try {
            $db = $this->container->get('db');
            $stmt = $db->prepare("INSERT INTO permissions (name, description) VALUES (?, ?)");
            $stmt->execute([$data['name'], $data['description'] ?? null]);
            $this->session->success('Permission created successfully.');
        } catch (\Exception $e) {
            logger('Permission create error: ' . $e->getMessage(), 'error');
            $this->session->error('Error creating permission.');
        }
        $this->redirect('/admin/roles');
    }

    /**
     * Update an existing permission.
     */
    public function update($id = null): void
    {
        $this->requireAdmin();
        $this->requirePermission('manage_roles');
        $this->requireCsrfToken();
        
        // **THIS IS THE CRITICAL FIX**
        // The validation rule now correctly excludes the current permission's ID,
        // preventing the "unique" rule from failing on the record itself.
        $data = $this->validate($this->all(), [
            'name' => "required|max:50|unique:permissions,{$id}",
            'description' => 'nullable|max:255'
        ]);

        try {
            $db = $this->container->get('db');
            $stmt = $db->prepare("UPDATE permissions SET name = ?, description = ? WHERE id = ?");
            $stmt->execute([$data['name'], $data['description'] ?? null, $id]);
            $this->session->success('Permission updated successfully.');
        } catch (\Exception $e) {
            logger('Permission update error: ' . $e->getMessage(), 'error');
            $this->session->error('Error updating permission.');
        }
        $this->redirect('/admin/roles');
    }

    /**
     * Delete a permission.
     */
    public function delete($id = null): void
    {
        $this->requireAdmin();
        $this->requirePermission('manage_roles');
        $this->requireCsrfToken();
        
        try {
            $db = $this->container->get('db');
            // NOTE: Add checks here to prevent deleting critical permissions if needed
            $stmt = $db->prepare("DELETE FROM permissions WHERE id = ?");
            $stmt->execute([$id]);
            $this->session->success('Permission deleted successfully.');
        } catch (\Exception $e) {
            logger('Permission delete error: ' . $e->getMessage(), 'error');
            $this->session->error('Error deleting permission.');
        }
        $this->redirect('/admin/roles');
    }
}
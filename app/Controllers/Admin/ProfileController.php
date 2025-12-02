<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Admin;

class ProfileController extends Controller
{
    /**
     * Show the currently logged-in admin's profile edit form.
     */
    public function show(): void
    {
        $this->requireAdmin();
        
        $admin = $this->auth->user();
        
        // Get the user's roles and format them into a string
        $roles = $admin->getRoles();
        $roleNames = !empty($roles) ? implode(', ', array_map(fn($role) => $role->name, $roles)) : 'No roles assigned';
        
        $this->render('admin/profile/edit', [
            'admin' => $admin,
            'roleNames' => $roleNames
        ], 'admin');
    }

    /**
     * Update the currently logged-in admin's profile.
     */
    public function update(): void
    {
        $this->requireAdmin();
        $this->requireCsrfToken();

        $admin = $this->auth->user();
        $id = $admin->id;

        $rules = [
            'first_name' => 'required|max:50',
            'last_name' => 'required|max:50',
        ];

        // Only validate password if it's being changed
        if (!empty($this->input('password'))) {
            $rules['password'] = 'min:8|confirmed';
        }

        $data = $this->validate($this->all(), $rules);

        try {
            $admin->first_name = $data['first_name'];
            $admin->last_name = $data['last_name'];

            if (!empty($data['password'])) {
                $admin->password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
            }
            
            $admin->save();
            
            $this->session->success('Your profile has been updated successfully.');
        } catch (\Exception $e) {
            logger('Admin profile update error: ' . $e->getMessage(), 'error');
            $this->session->error('An error occurred while updating your profile.');
        }

        $this->redirect('/admin/profile');
    }
}
<?php
// app/Controllers/Auth/RegisterController.php

namespace App\Controllers\Auth;

use App\Core\Controller;
use App\Models\Member;

/**
 * Registration Controller
 * Handles member registration
 */
class RegisterController extends Controller
{
    /**
     * Show registration form
     */
    public function showRegister(): void
    {
        // Redirect if already authenticated
        if ($this->auth->check()) {
            $this->auth->redirectToDashboard();
        }
        
        $this->render('auth/register', [], 'auth');
    }
    
    /**
     * Handle member registration
     */
    public function register(): void
    {
        $this->requireCsrfToken();
        
        $data = $this->validate($this->all(), [
            'first_name' => 'required|max:100',
            'last_name' => 'required|max:100',
            'email' => 'required|email|unique:members',
            'mobile' => 'required|phone',
            'password' => 'required|min:8|confirmed',
            'date_of_birth' => 'required|date',
            'consent_photography' => 'required',
            'consent_first_aid' => 'required',
            'terms_conditions_acceptance' => 'required',
            'emergency_contact_name' => 'required|max:255',
            'emergency_contact_number' => 'required|phone',
            'emergency_contact_relationship' => 'required|max:100',
        ]);
        
        // Check if under 18 for parent/guardian details
        $birthDate = new \DateTime($data['date_of_birth']);
        $age = (new \DateTime())->diff($birthDate)->y;
        
        if ($age < 18) {
            $parentData = $this->validate($this->all(), [
                'parent_guardian_email' => 'required|email',
                'parent_guardian_mobile' => 'required|phone',
            ]);
            
            $data = array_merge($data, $parentData);
        }
        
        try {
            // Hash password
            $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
            unset($data['password'], $data['password_confirmation']);
            
            // Convert consent fields to boolean
            $data['consent_photography'] = $data['consent_photography'] === 'on';
            $data['consent_first_aid'] = $data['consent_first_aid'] === 'on';
            $data['terms_conditions_acceptance'] = $data['terms_conditions_acceptance'] === 'on';
            
            // Create member
            $member = Member::create($data);
            
            // Log them in
            $this->auth->loginMember($member);
            
            $this->session->success('Registration successful! Welcome to our club.');
            $this->redirect('/');
            
        } catch (\Exception $e) {
            logger('Registration failed: ' . $e->getMessage(), 'error');
            $this->errorAndBack('Registration failed. Please try again.');
        }
    }
}

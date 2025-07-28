<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\Member;
use App\Models\Admin;
use App\Models\ClassModel;
use App\Models\ClassBooking; // Assuming this model exists for bookFreeTrial

class AuthController extends Controller
{

	public function showLogin()
	{
		// If user is already logged in, send them to the correct dashboard.
		if (Auth::check()) {
			if (Auth::isAdmin()) {
				header('Location: /admin'); // Redirect admin to admin dashboard
				exit();
			} else {
				header('Location: /'); // Redirect regular member to member dashboard
				exit();
			}
		}

		// If not logged in, just show the login view.
		$this->view('auth.login');
	}



    /*public function showLogin()
    {
        if (Auth::check()) {
            if (Auth::isAdmin()) {
                $this->redirect('/admin');
            } else {
                $this->redirect('/');
            }
        }
        $this->view('auth.login');
    }*/



/*public function showLogin()
{
    echo "DEBUG 1: Entered showLogin method."; // Debug message 1

    if (Auth::check()) {
        echo "DEBUG 2: User is authenticated, attempting redirect."; // Debug message 2
        if (Auth::isAdmin()) {
            $this->redirect('/admin');
        } else {
            $this->redirect('/');
        }
    }

    echo "DEBUG 3: Calling the view method."; // Debug message 3
    $this->view('auth.login');
    echo "DEBUG 4: This will probably not be seen."; // Debug message 4
    die(); // Stop the script
}*/

    public function login()
    {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $userType = $_POST['user_type'] ?? 'member'; // 'member' or 'admin'

        $errors = $this->validate($_POST, [
            'email' => ['required', 'email'],
            'password' => ['required']
        ]);

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_input'] = $_POST;
            $this->redirect('/login');
        }

        if ($userType === 'admin') {
            $attempt = Auth::attemptAdmin($email, $password);
            if ($attempt) {
                $_SESSION['success_message'] = 'Admin login successful!';
                $this->redirect('/admin');
            } else {
                $_SESSION['error_message'] = 'Invalid admin email or password.';
            }
        } else { // Default to member
            $attempt = Auth::attemptMember($email, $password);
            if ($attempt) {
                $_SESSION['success_message'] = 'Member login successful!';
                $this->redirect('/');
            } else {
                $_SESSION['error_message'] = 'Invalid member email or password.';
            }
        }

        $_SESSION['old_input'] = $_POST;
        $this->redirect('/login');
    }

    public function showRegister()
    {
        if (Auth::check()) {
            $this->redirect('/');
        }
        $freeTrialClasses = ClassModel::getAvailableFreeTrialClasses();
        $this->view('auth.register', ['freeTrialClasses' => $freeTrialClasses]);
    }

    public function register()
    {
        $data = $_POST;

        $rules = [
            'first_name' => ['required'],
            'last_name' => ['required'],
            'email' => ['required', 'email'],
            'mobile' => ['required'],
            'date_of_birth' => ['required'],
            'password' => ['required', 'min' => 6],
            'password_confirm' => ['required', 'matches' => 'password'],
            'consent_photography' => ['required'],
            'consent_first_aid' => ['required'],
            'terms_conditions_acceptance' => ['required'],
            'emergency_contact_name' => ['required'],
            'emergency_contact_number' => ['required'],
            'emergency_contact_relationship' => ['required'],
        ];

        // This assumes you have a global helper function `calculateAge`
        if (isset($data['date_of_birth']) && function_exists('calculateAge') && calculateAge($data['date_of_birth']) < 18) {
            $rules['parent_guardian_email'] = ['required', 'email'];
            $rules['parent_guardian_mobile'] = ['required'];
        }

        $errors = $this->validate($data, $rules);

        if (Member::findByEmail($data['email'])) {
            $errors['email'][] = 'An account with this email already exists.';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_input'] = $data;
            $this->redirect('/register');
        }

        $memberData = [
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'mobile' => $data['mobile'],
            'date_of_birth' => $data['date_of_birth'],
            'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            'consent_photography' => isset($data['consent_photography']) ? 1 : 0,
            'consent_first_aid' => isset($data['consent_first_aid']) ? 1 : 0,
            'terms_conditions_acceptance' => isset($data['terms_conditions_acceptance']) ? 1 : 0,
            'emergency_contact_name' => $data['emergency_contact_name'],
            'emergency_contact_number' => $data['emergency_contact_number'],
            'emergency_contact_relationship' => $data['emergency_contact_relationship'],
            'parent_guardian_email' => $data['parent_guardian_email'] ?? null,
            'parent_guardian_mobile' => $data['parent_guardian_mobile'] ?? null,
        ];

        try {
            $memberId = Member::create($memberData);

            if ($memberId && isset($data['free_trial_class_id']) && $data['free_trial_class_id']) {
                $classId = (int) $data['free_trial_class_id'];
                ClassBooking::bookFreeTrial($memberId, $classId);
            }

            $_SESSION['success_message'] = 'Registration successful! Please log in.';
            $this->redirect('/login');
        } catch (\Exception $e) {
            // Log the actual error for debugging
            error_log('Registration Error: ' . $e->getMessage());
            $_SESSION['error_message'] = 'An unexpected error occurred during registration.';
            $_SESSION['old_input'] = $data;
            $this->redirect('/register');
        }
    }

    public function logout()
    {
        Auth::logout();
        $_SESSION['success_message'] = 'You have been logged out.';
        $this->redirect('/login');
    }
}
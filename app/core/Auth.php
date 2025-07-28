<?php
// app/core/Auth.php
namespace App\Core;

use App\Models\Member; // For member authentication
use App\Models\Admin; // For admin authentication
use App\Models\AdminRole; // For checking admin roles/permissions

class Auth
{
    private static $loggedInUser = null;

    // Attempts to log in a member
    public static function attemptMember($email, $password)
    {
        $member = Member::findByEmail($email);

        if ($member && password_verify($password, $member['password_hash'])) {
            $_SESSION['user_id'] = $member['id'];
            $_SESSION['user_type'] = 'member';
            $_SESSION['user_first_name'] = $member['first_name'];
            $_SESSION['user_last_name'] = $member['last_name'];
            self::$loggedInUser = $member;
            return true;
        }
        return false;
    }

    // Attempts to log in an admin
    public static function attemptAdmin($email, $password)
    {
        $admin = Admin::findByEmail($email);

        if ($admin && password_verify($password, $admin['password_hash'])) {
            $_SESSION['user_id'] = $admin['id'];
            $_SESSION['user_type'] = 'admin';
            $_SESSION['user_first_name'] = $admin['first_name'];
            $_SESSION['user_last_name'] = $admin['last_name'];
            self::$loggedInUser = $admin;
            return true;
        }
        return false;
    }

    public static function check()
    {
        return isset($_SESSION['user_id']) && isset($_SESSION['user_type']);
    }

    public static function member()
    {
        if (self::check() && $_SESSION['user_type'] === 'member') {
            if (self::$loggedInUser === null || self::$loggedInUser['id'] !== $_SESSION['user_id'] || $_SESSION['user_type'] !== 'member') {
                self::$loggedInUser = Member::findById($_SESSION['user_id']);
            }
            return self::$loggedInUser;
        }
        return null;
    }

    public static function admin()
    {
        if (self::check() && $_SESSION['user_type'] === 'admin') {
            if (self::$loggedInUser === null || self::$loggedInUser['id'] !== $_SESSION['user_id'] || $_SESSION['user_type'] !== 'admin') {
                self::$loggedInUser = Admin::findById($_SESSION['user_id']);
            }
            return self::$loggedInUser;
        }
        return null;
    }

    public static function isAdmin()
    {
        return self::check() && $_SESSION['user_type'] === 'admin';
    }

    // Checks if the logged-in admin has a specific permission
    public static function hasPermission($permissionName)
    {
		//error_log( $permissionName);
        if (!self::isAdmin()) {
            return false;
        }
        $adminId = $_SESSION['user_id'];
        return AdminRole::hasPermission($adminId, $permissionName);
    }

    // Utility function to require a permission and redirect if not granted

	public static function checkPermission($permission)
    {
        // Assuming you have a hasPermission method that returns true or false
        if (!self::hasPermission($permission)) {
            http_response_code(403); // Set HTTP status to 403 Forbidden

            // You can create a dedicated error view for a nicer message
            // For now, a simple message will work and stop the loop.
            echo "<h1>403 Forbidden</h1>";
            echo "<p>You do not have the required permission ('{$permission}') to access this page.</p>";
            exit(); // Stop the script
        }
    }



	/**
	 * Get the currently authenticated user's data from the session.
	 *
	 * @return array|null The user data array or null if not logged in.
	 */
	public static function user()
	{
		if (self::check()) {
			// Assumes user data is stored in $_SESSION['user']
			return $_SESSION['user'] ?? null;
		}
		return null;
	}









    /*public static function checkPermission($permissionName)
    {
        if (!self::hasPermission($permissionName)) {
            $_SESSION['error_message'] = "You do not have permission to access this resource or perform this action.";
            // Redirect based on user type if not admin, or to a generic forbidden page
            if (self::check() && $_SESSION['user_type'] === 'member') {
                header('Location: /');
            } else {
                // For admin, redirect to admin dashboard or a specific forbidden page
                header('Location: /admin');
            }
            exit();
        }
    }*/







    public static function logout()
    {
        session_unset();
        session_destroy();
        self::$loggedInUser = null; // Clear cached user data
    }

    public static function requireMemberLogin()
    {
        if (!self::check() || $_SESSION['user_type'] !== 'member') {
            header('Location: /login');
            exit();
        }
    }

    public static function requireAdminLogin()
    {
        if (!self::check() || $_SESSION['user_type'] !== 'admin') {
            header('Location: /login');
            exit();
        }
    }



	public static function requireAdmin()
	{
		// Get the current request URI
		$currentUri = $_SERVER['REQUEST_URI'];

		// Check if user is not an admin AND not already on the login page
		if (!self::isAdmin() && $currentUri !== '/login') {
			$_SESSION['error_message'] = 'You must be an administrator to access this page.';
			header('Location: /login');
			exit();
		}
	}

    /*public static function requireAdmin()
    {
        if (!self::isAdmin()) {
            $_SESSION['error_message'] = 'You must be an administrator to access this page.';
            header('Location: /login');
            exit();
        }
    }*/

}

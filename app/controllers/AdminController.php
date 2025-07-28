<?php
// app/controllers/AdminController.php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\Member; // Now using Member model for general members
use App\Models\Admin; // New Admin model for admin users
use App\Models\Subscription;
use App\Models\ClassModel;
use App\Models\ClassBooking;
use App\Models\Payment;
use App\Models\Message;
use App\Models\Setting;
use App\Models\Role; // For roles management
use App\Services\StripeService;
use App\Services\MicrosoftGraphService;

class AdminController extends Controller
{
    public function __construct()
    {
        Auth::requireAdmin(); // Ensure user is admin for all admin actions
    }

    public function dashboard()
    {
        Auth::checkPermission('view_dashboard'); // Example permission check
        $totalMembers = Member::countAll();
        $activeSubscriptionsCount = Subscription::countActiveSubscriptions();
        $pendingPayments = Payment::countPendingPayments();
        $failedPayments = Payment::countFailedPayments();
        $unreadMessages = Message::countUnreadAdminMessages();
        $upcomingClasses = ClassModel::getUpcomingClasses(5); // Top 5 upcoming
        $this->view('admin.dashboard', [
            'totalMembers' => $totalMembers,
            'activeSubscriptionsCount' => $activeSubscriptionsCount,
            'pendingPayments' => $pendingPayments,
            'failedPayments' => $failedPayments,
            'unreadMessages' => $unreadMessages,
            'upcomingClasses' => $upcomingClasses
        ]);
    }

    public function members()
    {
        Auth::checkPermission('view_members');
        $members = Member::getAll();
        $this->view('admin.members.index', ['members' => $members]);
    }

    public function memberProfile($memberId)
    {
        Auth::checkPermission('view_members');
        $member = Member::findById($memberId);
        if (!$member) {
            $_SESSION['error_message'] = 'Member not found.';
            $this->redirect('/admin/members');
        }
        $subscriptions = Subscription::getMemberAllSubscriptions($memberId);
        $payments = Payment::getMemberPayments($memberId);
        $classesBooked = ClassBooking::getMemberBookedClasses($memberId);
        $messages = Message::getMessagesForMember($memberId); // Get messages specific to this member

        $this->view('admin.members.profile', [
            'member' => $member,
            'subscriptions' => $subscriptions,
            'payments' => $payments,
            'classesBooked' => $classesBooked,
            'messages' => $messages,
            'availableSubscriptions' => Subscription::getAllSubscriptions() // For adding new subscriptions
        ]);
    }

    public function updateMember($memberId)
    {
        Auth::checkPermission('edit_members');
        $data = $_POST;
        try {
            Member::update($memberId, $data);
            $_SESSION['success_message'] = 'Member details updated.';
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Error updating member: ' . $e->getMessage();
        }
        $this->redirect('/admin/members/' . $memberId);
    }

    public function addMemberSubscription($memberId)
    {
        Auth::checkPermission('manage_subscriptions');
        $subscriptionId = $_POST['subscription_id'] ?? null;
        $startDate = $_POST['start_date'] ?? null;
        $overrideFee = $_POST['override_fee'] ?? null; // Optional override

        if (!$subscriptionId || !$startDate) {
            $_SESSION['error_message'] = 'Subscription and start date are required.';
            $this->redirect('/admin/members/' . $memberId);
        }

        try {
            Subscription::adminEnrollMember($memberId, $subscriptionId, $startDate, $overrideFee);
            $_SESSION['success_message'] = 'Subscription added for member.';
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Error adding subscription: ' . $e->getMessage();
        }
        $this->redirect('/admin/members/' . $memberId);
    }

    public function updateMemberSubscription($memberId)
    {
        Auth::checkPermission('manage_subscriptions');
        $memberSubscriptionId = $_POST['member_subscription_id'] ?? null;
        $action = $_POST['action'] ?? null; // e.g., 'upgrade', 'downgrade', 'suspend', 'activate'
        $effectiveDate = $_POST['effective_date'] ?? date('Y-m-d'); // Date for change to become active

        if (!$memberSubscriptionId || !$action) {
            $_SESSION['error_message'] = 'Subscription and action are required.';
            $this->redirect('/admin/members/' . $memberId);
        }

        try {
            switch ($action) {
                case 'upgrade':
                case 'downgrade':
                    $newSubscriptionId = $_POST['new_subscription_id'] ?? null;
                    Subscription::changeMemberSubscription($memberId, $memberSubscriptionId, $newSubscriptionId, $effectiveDate);
                    $_SESSION['success_message'] = 'Member subscription changed.';
                    break;
                case 'suspend':
                    Subscription::suspendMemberSubscription($memberSubscriptionId, $effectiveDate);
                    $_SESSION['success_message'] = 'Member subscription suspended.';
                    break;
                case 'activate':
                    Subscription::activateMemberSubscription($memberSubscriptionId, $effectiveDate);
                    $_SESSION['success_message'] = 'Member subscription activated.';
                    break;
                case 'cancel':
                    Subscription::cancelMemberSubscription($memberSubscriptionId, $effectiveDate);
                    $_SESSION['success_message'] = 'Member subscription cancelled.';
                    break;
                case 'delete': // Hard delete, use with caution
                    Subscription::deleteMemberSubscription($memberSubscriptionId);
                    $_SESSION['success_message'] = 'Member subscription deleted.';
                    break;
                default:
                    $_SESSION['error_message'] = 'Invalid action.';
            }
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Error performing subscription action: ' . $e->getMessage();
        }
        $this->redirect('/admin/members/' . $memberId);
    }

    public function subscriptions()
    {
        Auth::checkPermission('view_subscriptions');
        $subscriptions = Subscription::getAllSubscriptions();
        $this->view('admin.subscriptions.index', ['subscriptions' => $subscriptions]);
    }

    public function classes()
    {
        Auth::checkPermission('view_classes');
        $classes = ClassModel::getAllClasses();
        $this->view('admin.classes.index', ['classes' => $classes]);
    }

    public function classCalendar()
    {
        Auth::checkPermission('view_classes');
        $classes = ClassModel::getAllClassesWithBookings(); // Fetch classes with member bookings
        $this->view('admin.classes.calendar', ['classes' => $classes]);
    }

    public function payments()
    {
        Auth::checkPermission('view_payments');
        $payments = Payment::getAllPayments();
        $this->view('admin.payments.index', ['payments' => $payments]);
    }

    public function settings()
    {
        Auth::checkPermission('manage_settings');
        $settings = Setting::getAllSettings();
        $this->view('admin.settings.index', ['settings' => $settings]);
    }

    public function saveSettings()
    {
        Auth::checkPermission('manage_settings');
        $data = $_POST;
        try {
            Setting::updateSettings($data);

            // Special handling for Microsoft Graph API settings
            // These values are typically used to initiate the OAuth flow, not directly stored as settings
            // For now, these are picked up from the .env file as constants.
            // The setupAuth method will generate the consent URL.

            $_SESSION['success_message'] = 'Settings saved successfully.';
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Error saving settings: ' . $e->getMessage();
        }
        $this->redirect('/admin/settings');
    }

    public function users()
    {
        Auth::checkPermission('manage_users'); // Permission to manage admin users/roles
        $admins = Admin::getAll(); // Get all admin users
        $roles = Role::getAll(); // Get all available roles
        $this->view('admin.users.index', ['admins' => $admins, 'roles' => $roles]);
    }

    public function createUser()
    {
        Auth::checkPermission('manage_users');
        $data = $_POST;
        $rules = [
            'first_name' => ['required'],
            'last_name' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'min' => 6],
            'role_ids' => ['required'], // Array of role IDs
        ];

        $errors = $this->validate($data, $rules);

        if (Admin::findByEmail($data['email'])) {
            $errors['email'][] = 'An admin account with this email already exists.';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_input'] = $data;
            $this->redirect('/admin/users'); // Redirect with errors
        }

        $adminData = [
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            'mobile' => $data['mobile'] ?? null,
        ];

        try {
            $adminId = Admin::create($adminData);
            if ($adminId && !empty($data['role_ids'])) {
                foreach ($data['role_ids'] as $roleId) {
                    \App\Models\AdminRole::assignRole($adminId, (int)$roleId);
                }
            }
            $_SESSION['success_message'] = 'Admin user created successfully.';
        } catch (\Exception $e) {
            $_SESSION['error_message'] = 'Error creating admin user: ' . $e->getMessage();
        }
        $this->redirect('/admin/users');
    }









	/**
	 * Displays the form to edit an existing admin user.
	 *
	 * @param int $id The ID of the admin user to edit.
	 */
	public function editUser($id)
	{
		Auth::checkPermission('manage_users');

		$admin = \App\Models\Admin::findById($id);

		if (!$admin) {
			$_SESSION['error_message'] = 'Admin user not found.';
			header('Location: /admin/users');
			exit();
		}

		// Fetch all available roles for the dropdown
		$roles = \App\Models\Role::getAll();
		
		// Fetch the IDs of the roles currently assigned to this user
		$assignedRoleIds = \App\Models\AdminRole::getRoleIdsForAdmin($id);

		$this->view('admin.users.edit', [
			'admin' => $admin,
			'roles' => $roles,
			'assignedRoleIds' => $assignedRoleIds
		]);
	}

	/**
	 * Processes the form submission for updating an admin user.
	 *
	 * @param int $id The ID of the admin user to update.
	 */
	public function updateUser($id)
	{
		Auth::checkPermission('manage_users');

		$data = $_POST;
		$adminData = [
			'first_name' => trim($data['first_name']),
			'last_name' => trim($data['last_name']),
			'email' => trim($data['email']),
			'mobile' => trim($data['mobile'] ?? null),
		];

		// --- Password Update (only if a new password is provided) ---
		if (!empty($data['password'])) {
			if ($data['password'] !== $data['password_confirm']) {
				$_SESSION['error_message'] = 'Passwords do not match.';
				$_SESSION['old_input'] = $data;
				header('Location: /admin/users/edit/' . $id);
				exit();
			}
			$adminData['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
		}

		try {
			// 1. Update the user's main details
			\App\Models\Admin::update($id, $adminData);

			// 2. Update their assigned roles
			$roleIds = $data['role_ids'] ?? [];
			\App\Models\AdminRole::updateRolesForAdmin($id, $roleIds);

			$_SESSION['success_message'] = 'Admin user updated successfully.';
		} catch (\Exception $e) {
			$_SESSION['error_message'] = 'Error updating user: ' . $e->getMessage();
		}
		
		header('Location: /admin/users');
		exit();
	}



















    public function adminMessages()
    {
        Auth::checkPermission('view_member_messages'); // Permission to view messages
        $adminId = Auth::user()['id']; // The current admin sending the message
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Auth::checkPermission('send_member_messages'); // Permission to send replies
            $memberId = $_POST['member_id'] ?? null;
            $messageContent = $_POST['message_content'] ?? '';
            if ($memberId && !empty($messageContent)) {
                try {
                    Message::createMessage($memberId, $adminId, $messageContent, 'admin_to_member');
                    $_SESSION['success_message'] = 'Reply sent to member.';
                } catch (\Exception $e) {
                    $_SESSION['error_message'] = 'Error sending reply: ' . $e->getMessage();
                }
            } else {
                $_SESSION['error_message'] = 'Member and message content are required.';
            }
            $this->redirect('/admin/members/' . $memberId); // Redirect back to member profile
        } else {
            $messages = Message::getAllAdminMessages(); // Get all messages requiring admin attention
            $this->view('admin.messages.index', ['messages' => $messages]);
        }
    }

	/**
	 * Displays the main roles and permissions management page.
	 */
	public function roles()
	{
		Auth::checkPermission('manage_roles'); // Protect the page

		// Fetch all roles with their currently assigned permissions
		$roles = \App\Models\Role::getAllWithPermissions();
		
		// Fetch all available permissions to populate the checkboxes
		$permissions = \App\Models\Permission::getAll();

		$this->view('admin.roles.index', [
			'roles' => $roles,
			'permissions' => $permissions
		]);
	}

	/**
	 * Handles the creation of a new role.
	 */
	/*public function createRole()
	{
		Auth::checkPermission('manage_roles');
		
		$roleName = $_POST['role_name'] ?? '';
		if (!empty($roleName)) {
			\App\Models\Role::create(['name' => $roleName]);
			$_SESSION['success_message'] = 'New role created successfully.';
		} else {
			$_SESSION['error_message'] = 'Role name cannot be empty.';
		}
		$this->redirect('/admin/roles');
	}*/
	
	/*public function createRole()
	{
		Auth::checkPermission('manage_roles');
		
		$roleName = trim($_POST['role_name'] ?? '');
		$roleDescription = trim($_POST['role_description'] ?? ''); // Get the description

		if (!empty($roleName)) {
			// Pass both name and description to the create method
			\App\Models\Role::create([
				'name' => $roleName,
				'description' => $roleDescription
			]);
			$_SESSION['success_message'] = 'New role created successfully.';
		} else {
			$_SESSION['error_message'] = 'Role name cannot be empty.';
		}
		header('Location: /admin/roles');
		exit();
	}*/






	public function createRole()
	{
		Auth::checkPermission('manage_roles');
		
		$roleName = trim($_POST['role_name'] ?? '');
		// ... validation ...

		try {
			\App\Models\Role::create([
				'name' => $roleName,
				'description' => trim($_POST['role_description'] ?? '')
			]);
			$_SESSION['success_message'] = 'New role created successfully.';

		} catch (\App\Core\Exceptions\DuplicateEntryException $e) {
			// Now we catch our specific, clean exception
			$_SESSION['error_message'] = "A role with the name '{$roleName}' already exists.";
		} catch (\Exception $e) {
			// Catch any other general errors
			$_SESSION['error_message'] = 'An unexpected error occurred.';
			error_log($e->getMessage());
		}

		header('Location: /admin/roles');
		exit();
	}








	/**
	 * Handles the creation of a new permission.
	 */
	/*public function createPermission()
	{
		Auth::checkPermission('manage_roles');

		$permissionName = $_POST['permission_name'] ?? '';
		if (!empty($permissionName)) {
			\App\Models\Permission::create(['permission_name' => $permissionName, 'description' => $_POST['permission_description'] ?? '']);
			$_SESSION['success_message'] = 'New permission created successfully.';
		} else {
			$_SESSION['error_message'] = 'Permission name cannot be empty.';
		}
		$this->redirect('/admin/roles');
	}*/


	public function createPermission()
	{
		Auth::checkPermission('manage_roles');

		$permissionName = trim($_POST['permission_name'] ?? '');
		// ... validation ...

		try {
			\App\Models\Permission::create([
				'permission_name' => $permissionName,
				'description' => trim($_POST['permission_description'] ?? '')
			]);
			$_SESSION['success_message'] = 'New permission created successfully.';

		} catch (\App\Core\Exceptions\DuplicateEntryException $e) {
			$_SESSION['error_message'] = "A permission with the name '{$permissionName}' already exists.";
		} catch (\Exception $e) {
			$_SESSION['error_message'] = 'An unexpected error occurred.';
			error_log($e->getMessage());
		}

		header('Location: /admin/roles');
		exit();
	}








	/**
	 * Updates the permissions assigned to a specific role.
	 */
	public function updateRolePermissions($roleId)
	{
		Auth::checkPermission('manage_roles');

		// Get the submitted permission IDs from the form checkboxes
		$permissionIds = $_POST['permissions'] ?? [];

		try {
			// This function will handle deleting old permissions and inserting new ones
			\App\Models\RolePermission::updatePermissionsForRole($roleId, $permissionIds);
			$_SESSION['success_message'] = 'Role permissions updated successfully.';
		} catch (\Exception $e) {
			$_SESSION['error_message'] = 'Error updating permissions: ' . $e->getMessage();
		}
		
		$this->redirect('/admin/roles');
	}

}

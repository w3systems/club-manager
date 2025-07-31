<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Member;

/**
 * Admin Member Controller
 * Manages member administration functionality
 */
class MemberController extends Controller
{
    /**
     * Display member list
     */
	/**
	 * Display member list
	 */
	public function index(): void
	{
		$this->requireAdmin();
		$this->requirePermission('view_members');
		
		try {
			$db = $this->container->get('db');
			
			// Get search and filter parameters
			$search = $this->input('search', '');
			$status = $this->input('status', '');
			$page = max(1, (int)$this->input('page', 1));
			$perPage = 20;
			
			// Build query
			$whereConditions = [];
			$params = [];
			
			if (!empty($search)) {
				$whereConditions[] = "(first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)";
				$params[] = "%$search%";
				$params[] = "%$search%";
				$params[] = "%$search%";
			}
			
			if (!empty($status)) {
				$whereConditions[] = "status = ?";
				$params[] = $status;
			}
			
			$whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
			
			// Get total count
			$countQuery = "SELECT COUNT(*) FROM members $whereClause";
			$stmt = $db->prepare($countQuery);
			$stmt->execute($params);
			$totalMembers = $stmt->fetchColumn();
			
			// Get members with pagination
			$offset = ($page - 1) * $perPage;
			$query = "
				SELECT m.*, 
					   COUNT(ms.id) as subscription_count,
					   MAX(ms.created_at) as latest_subscription
				FROM members m
				LEFT JOIN member_subscriptions ms ON m.id = ms.member_id AND ms.status = 'active'
				$whereClause
				GROUP BY m.id
				ORDER BY m.created_at DESC
				LIMIT $perPage OFFSET $offset
			";
			
			$stmt = $db->prepare($query);
			$stmt->execute($params);
			$members = $stmt->fetchAll(\PDO::FETCH_ASSOC);
			
			// Format members data - simplified approach
			$formattedMembers = array_map(function($member) {
				// Create status badge HTML
				$statusClasses = match($member['status']) {
					'active' => 'bg-green-100 text-green-800',
					'inactive' => 'bg-gray-100 text-gray-800',
					'suspended' => 'bg-red-100 text-red-800',
					default => 'bg-gray-100 text-gray-800'
				};
				$statusBadge = "<span class='badge $statusClasses'>" . ucfirst($member['status']) . "</span>";
				
				return (object) [
					'id' => $member['id'],
					'first_name' => $member['first_name'],
					'last_name' => $member['last_name'],
					'email' => $member['email'],
					'status' => $member['status'],
					'created_at' => $member['created_at'],
					'subscription_count' => $member['subscription_count'],
					'latest_subscription' => $member['latest_subscription'],
					'full_name' => trim($member['first_name'] . ' ' . $member['last_name']),
					'status_badge' => $statusBadge,
					'formatted_date' => date('M j, Y', strtotime($member['created_at']))
				];
			}, $members);
			
			// Pagination data
			$pagination = [
				'current_page' => $page,
				'per_page' => $perPage,
				'total' => $totalMembers,
				'total_pages' => ceil($totalMembers / $perPage),
				'has_prev' => $page > 1,
				'has_next' => $page < ceil($totalMembers / $perPage)
			];
			
			$data = [
				'members' => $formattedMembers,
				'pagination' => $pagination,
				'search' => $search,
				'status' => $status,
				'total' => $totalMembers
			];
			
			$this->render('admin/members/index', $data, 'admin');
			
		} catch (\Exception $e) {
			logger('Member list error: ' . $e->getMessage(), 'error');
			$this->session->error('Error loading members');
			$this->redirect('/admin');
		}
	}


    public function indexold(): void
    {
        $this->requireAdmin();
        $this->requirePermission('view_members');
        
        try {
            $db = $this->container->get('db');
            
            // Get search and filter parameters
            $search = $this->input('search', '');
            $status = $this->input('status', '');
            $page = max(1, (int)$this->input('page', 1));
            $perPage = 20;
            
            // Build query
            $whereConditions = [];
            $params = [];
            
            if (!empty($search)) {
                $whereConditions[] = "(first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }
            
            if (!empty($status)) {
                $whereConditions[] = "status = ?";
                $params[] = $status;
            }
            
            $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
            
            // Get total count
            $countQuery = "SELECT COUNT(*) FROM members $whereClause";
            $stmt = $db->prepare($countQuery);
            $stmt->execute($params);
            $totalMembers = $stmt->fetchColumn();
            
            // Get members with pagination
            $offset = ($page - 1) * $perPage;
            $query = "
                SELECT m.*, 
                       COUNT(ms.id) as subscription_count,
                       MAX(ms.created_at) as latest_subscription
                FROM members m
                LEFT JOIN member_subscriptions ms ON m.id = ms.member_id AND ms.status = 'active'
                $whereClause
                GROUP BY m.id
                ORDER BY m.created_at DESC
                LIMIT $perPage OFFSET $offset
            ";
            
            $stmt = $db->prepare($query);
            $stmt->execute($params);
            $members = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Format members data
            /*$formattedMembers = array_map(function($member) {
                return (object) [
                    'id' => $member['id'],
                    'first_name' => $member['first_name'],
                    'last_name' => $member['last_name'],
                    'email' => $member['email'],
                    'status' => $member['status'],
                    'created_at' => $member['created_at'],
                    'subscription_count' => $member['subscription_count'],
                    'latest_subscription' => $member['latest_subscription'],
                    'getFullName' => function() use ($member) {
                        return trim($member['first_name'] . ' ' . $member['last_name']);
                    },
                    'getStatusBadge' => function() use ($member) {
                        $status = $member['status'];
                        $classes = match($status) {
                            'active' => 'bg-green-100 text-green-800',
                            'inactive' => 'bg-gray-100 text-gray-800',
                            'suspended' => 'bg-red-100 text-red-800',
                            default => 'bg-gray-100 text-gray-800'
                        };
                        return "<span class='badge $classes'>" . ucfirst($status) . "</span>";
                    }
                ];
            }, $members);*/







			$formattedMembers = array_map(function($member) {
				$memberObj = (object) [
					'id' => $member['id'],
					'first_name' => $member['first_name'],
					'last_name' => $member['last_name'],
					'email' => $member['email'],
					'status' => $member['status'],
					'created_at' => $member['created_at'],
					'subscription_count' => $member['subscription_count'],
					'latest_subscription' => $member['latest_subscription'],
					'full_name' => trim($member['first_name'] . ' ' . $member['last_name']), // Add as property
				];
				
				// Add methods that actually work
				$memberObj->getStatusBadge = function() use ($member) {
					$status = $member['status'];
					$classes = match($status) {
						'active' => 'bg-green-100 text-green-800',
						'inactive' => 'bg-gray-100 text-gray-800',
						'suspended' => 'bg-red-100 text-red-800',
						default => 'bg-gray-100 text-gray-800'
					};
					return "<span class='badge $classes'>" . ucfirst($status) . "</span>";
				};
				
				return $memberObj;
			}, $members);






            
            // Pagination data
            $pagination = [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $totalMembers,
                'total_pages' => ceil($totalMembers / $perPage),
                'has_prev' => $page > 1,
                'has_next' => $page < ceil($totalMembers / $perPage)
            ];
            
            $data = [
                'members' => $formattedMembers,
                'pagination' => $pagination,
                'search' => $search,
                'status' => $status,
                'total' => $totalMembers
            ];
            
            $this->render('admin/members/index', $data, 'admin');
            
        } catch (\Exception $e) {
            logger('Member list error: ' . $e->getMessage(), 'error');
            $this->session->error('Error loading members');
            $this->redirect('/admin');
        }
    }
    
    /**
     * Show member details
     */
    public function show($id = null): void
    {
        $this->requireAdmin();
        $this->requirePermission('view_members');

		if (!$id|| !is_numeric($id)) {
			echo "<!-- DEBUG: No ID found, redirecting -->";
			$this->session->error('Member not found');
			$this->redirect('/admin/members');
		}

        try {
            $db = $this->container->get('db');
            
            // Get member details
            $stmt = $db->prepare("SELECT * FROM members WHERE id = ?");
            $stmt->execute([$id]);
            $member = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$member) {
                $this->session->error('Member not found');
                $this->redirect('/admin/members');
            }

            // Get member subscriptions
            $stmt = $db->prepare("
				SELECT ms.*, s.name as subscription_name, s.price, s.term_length, s.term_unit, s.type
				FROM member_subscriptions ms
				INNER JOIN subscriptions s ON ms.subscription_id = s.id
				WHERE ms.member_id = ?
				ORDER BY ms.created_at DESC
            ");
            $stmt->execute([$id]);
            $subscriptions = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Get member payments
            $stmt = $db->prepare("
                SELECT * FROM payments 
                WHERE member_id = ? 
                ORDER BY payment_date DESC 
                LIMIT 10
            ");
            $stmt->execute([$id]);
            $payments = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Get member class bookings
            $stmt = $db->prepare("
                SELECT cb.*, c.name as class_name, c.instance_date_time
                FROM class_bookings cb
                INNER JOIN classes c ON cb.class_instance_id = c.id
                WHERE cb.member_id = ?
                ORDER BY c.instance_date_time DESC
                LIMIT 10
            ");
            $stmt->execute([$id]);
            $bookings = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $data = [
                'member' => (object) $member,
                'subscriptions' => $subscriptions,
                'payments' => $payments,
                'bookings' => $bookings
            ];
            $this->render('admin/members/show', $data, 'admin');
            
        } catch (\Exception $e) {
            logger('Member show error: ' . $e->getMessage(), 'error');
            $this->session->error('Error loading member details');
            $this->redirect('/admin/members');
        }
    }
    
    /**
     * Show create member form
     */
	public function create(): void
	{
		$this->requireAdmin();
		$this->requirePermission('create_members');
		
		$this->render('admin/members/form', [], 'admin');
	}

    
    /**
     * Store new member
     */
    public function store(): void
    {
        $this->requireAdmin();
        $this->requirePermission('create_members');
        $this->requireCsrfToken();
		
        /*$data = $this->validate($this->all(), [
            'first_name' => 'required|max:50',
            'last_name' => 'required|max:50',
            'email' => 'required|email|unique:members',
            'date_of_birth' => 'required|date',
            'phone' => 'max:20',
            'emergency_contact_name' => 'max:100',
            'emergency_contact_phone' => 'max:20',
            'status' => 'required|in:active,inactive',
			'accept_terms' => 'required'
        ]);*/

		$data = $this->validate($this->all(), [
			'first_name' => 'required|max:50',
			'last_name' => 'required|max:50',
			'email' => 'required|email|unique:members',
			'date_of_birth' => 'required|date',
			'phone' => 'max:20',
			'status' => 'required|in:active,inactive,prospect,suspended',
			'emergency_contact_name' => 'required|max:100',
			'emergency_contact_phone' => 'required|max:20',
			'emergency_contact_relationship' => 'max:50',
			'terms_conditions_acceptance' => 'required',
			// Optional consents (no validation needed)
			'consent_photography' => 'nullable',
			'consent_first_aid' => 'nullable', 
			'consent_marketing' => 'nullable',
			// Parent/guardian fields (conditionally required)
			'parent_guardian_first_name' => 'nullable|max:50',
			'parent_guardian_last_name' => 'nullable|max:50',
			'parent_guardian_email' => 'nullable|email',
			'parent_guardian_phone' => 'nullable|max:20',
			'parent_guardian_relationship' => 'nullable|in:parent,guardian,grandparent,other'
		]);

        
        try {
            $db = $this->container->get('db');
            
            $stmt = $db->prepare("
                INSERT INTO members (
                    first_name, last_name, email, date_of_birth, 
                    phone, emergency_contact_name, emergency_contact_phone,
                    status, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $data['first_name'],
                $data['last_name'],
                $data['email'],
                $data['date_of_birth'],
                $data['phone'] ?? null,
                $data['emergency_contact_name'] ?? null,
                $data['emergency_contact_phone'] ?? null,
                $data['status']
            ]);
            
            $this->session->success('Member created successfully');
            $this->redirect('/admin/members');
            
        } catch (\Exception $e) {
            logger('Member create error: ' . $e->getMessage(), 'error');
            $this->session->error('Error creating member');
            $this->back();
        }
    }
    
    /**
     * Show edit member form
     */
    public function editold($id = null): void
    {
        $this->requireAdmin();
        $this->requirePermission('edit_members');
        
        //$id = $this->input('id');
        if (!$id) {
            $this->session->error('Member not found');
            $this->redirect('/admin/members');
        }
        
        try {
            $db = $this->container->get('db');
            $stmt = $db->prepare("SELECT * FROM members WHERE id = ?");
            $stmt->execute([$id]);
            $member = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$member) {
                $this->session->error('Member not found');
                $this->redirect('/admin/members');
            }
            
            $data = ['member' => (object) $member];
            $this->render('admin/members/edit', $data, 'admin');
            
        } catch (\Exception $e) {
            logger('Member edit error: ' . $e->getMessage(), 'error');
            $this->session->error('Error loading member');
            $this->redirect('/admin/members');
        }
    }
    

	public function edit($id = null): void
	{
		$this->requireAdmin();
		$this->requirePermission('edit_members');
		
		if (!$id || !is_numeric($id)) {
			$this->session->error('Member not found');
			$this->redirect('/admin/members');
		}
		
		try {
			$db = $this->container->get('db');
			$stmt = $db->prepare("SELECT * FROM members WHERE id = ?");
			$stmt->execute([$id]);
			$member = $stmt->fetch(\PDO::FETCH_ASSOC);
			
			if (!$member) {
				$this->session->error('Member not found');
				$this->redirect('/admin/members');
			}
			
			$data = ['member' => (object) $member];
			$this->render('admin/members/form', $data, 'admin');
			
		} catch (\Exception $e) {
			logger('Member edit error: ' . $e->getMessage(), 'error');
			$this->session->error('Error loading member');
			$this->redirect('/admin/members');
		}
	}


    /**
     * Update member
     */
    public function update($id = null): void
    {
        $this->requireAdmin();
        $this->requirePermission('edit_members');
        $this->requireCsrfToken();
        
        if (!$id) {
            $this->session->error('Member not found');
            $this->redirect('/admin/members');
        }
        
        /*$data = $this->validate($this->all(), [
            'first_name' => 'required|max:50',
            'last_name' => 'required|max:50',
            'email' => "required|email|unique:members,email,$id",
            'email' => "required|email|unique:members",
            'date_of_birth' => 'required|date',
            'phone' => 'max:20',
            'emergency_contact_name' => 'max:100',
            'emergency_contact_phone' => 'max:20',
            'status' => 'required|in:active,inactive,suspended'
        ]);*/




		$data = $this->validate($this->all(), [
			'first_name' => 'required|max:50',
			'last_name' => 'required|max:50',
			'email' => 'required|email|unique:members,' . $id,
			'date_of_birth' => 'required|date',
			'phone' => 'max:20',
			'status' => 'required|in:active,inactive,prospect,suspended',
			'emergency_contact_name' => 'required|max:100',
			'emergency_contact_phone' => 'required|max:20',
			'emergency_contact_relationship' => 'max:50',
			'terms_conditions_acceptance' => 'required',
			// Optional consents (no validation needed)
			'consent_photography' => 'nullable',
			'consent_first_aid' => 'nullable', 
			'consent_marketing' => 'nullable',
			// Parent/guardian fields (conditionally required)
			'parent_guardian_first_name' => 'nullable|max:50',
			'parent_guardian_last_name' => 'nullable|max:50',
			'parent_guardian_email' => 'nullable|email',
			'parent_guardian_phone' => 'nullable|max:20',
			'parent_guardian_relationship' => 'nullable|in:parent,guardian,grandparent,other'
		]);



        
        try {
            $db = $this->container->get('db');
            
			$stmt = $db->prepare("
				UPDATE members SET
					first_name = ?, last_name = ?, email = ?, date_of_birth = ?,
					phone = ?, emergency_contact_name = ?, emergency_contact_phone = ?,
					emergency_contact_relationship = ?, status = ?, terms_conditions_acceptance = ?,
					consent_photography = ?, consent_first_aid = ?, consent_marketing = ?,
					parent_guardian_first_name = ?, parent_guardian_last_name = ?, 
					parent_guardian_email = ?, parent_guardian_phone = ?, 
					parent_guardian_relationship = ?, updated_at = NOW()
				WHERE id = ?
			");
            
			$stmt->execute([
				$data['first_name'],
				$data['last_name'], 
				$data['email'],
				$data['date_of_birth'],
				$data['phone'],
				$data['emergency_contact_name'],
				$data['emergency_contact_phone'],
				$data['emergency_contact_relationship'],
				$data['status'],
				$data['terms_conditions_acceptance'],
				$data['consent_photography'] ?? 0,
				$data['consent_first_aid'] ?? 0,
				$data['consent_marketing'] ?? 0,
				$data['parent_guardian_first_name'],
				$data['parent_guardian_last_name'],
				$data['parent_guardian_email'],
				$data['parent_guardian_phone'],
				$data['parent_guardian_relationship'],
				$id
			]);
            
            $this->session->success('Member updated successfully');
            $this->redirect('/admin/members');
            
        } catch (\Exception $e) {
            logger('Member update error: ' . $e->getMessage(), 'error');
            $this->session->error('Error updating member');
            $this->back();
        }
    }
    
    /**
     * Delete member
     */
    public function delete($id = null): void
    {
        $this->requireAdmin();
        $this->requirePermission('delete_members');
        $this->requireCsrfToken();
        
        $id = $this->input('id');
        if (!$id) {
            $this->json(['success' => false, 'message' => 'Member not found']);
        }
        
        try {
            $db = $this->container->get('db');
            
            // Check if member has active subscriptions
            $stmt = $db->prepare("SELECT COUNT(*) FROM member_subscriptions WHERE member_id = ? AND status = 'active'");
            $stmt->execute([$id]);
            $activeSubscriptions = $stmt->fetchColumn();
            
            if ($activeSubscriptions > 0) {
                $this->json(['success' => false, 'message' => 'Cannot delete member with active subscriptions']);
            }
            
            // Soft delete - just update status
            $stmt = $db->prepare("UPDATE members SET status = 'deleted', updated_at = NOW() WHERE id = ?");
            $stmt->execute([$id]);
            
            $this->json(['success' => true, 'message' => 'Member deleted successfully']);
            
        } catch (\Exception $e) {
            logger('Member delete error: ' . $e->getMessage(), 'error');
            $this->json(['success' => false, 'message' => 'Error deleting member']);
        }
    }
}
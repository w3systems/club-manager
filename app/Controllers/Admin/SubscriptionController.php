<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Subscription;
use App\Models\ClassModel;

class SubscriptionController extends Controller
{
    /**
     * Display a list of all subscription plans.
     */
    public function index(): void
    {
        $this->requireAdmin();
        $this->requirePermission('view_subscriptions');
        
        $subscriptions = Subscription::all('name');
        
        $this->render('admin/subscriptions/index', ['subscriptions' => $subscriptions], 'admin');
    }

    /**
     * Show the form for creating a new subscription plan.
     */
    public function create(): void
    {
        $this->requireAdmin();
        $this->requirePermission('manage_subscriptions');

        $this->render('admin/subscriptions/form', [
            'subscription' => (object)[],
            'allClasses' => ClassModel::all('name'),
            'selectedClasses' => []
        ], 'admin');
    }

    /**
     * Store a new subscription plan in the database.
     */
    public function store(): void
    {
        $this->requireAdmin();
        $this->requirePermission('manage_subscriptions');
        $this->requireCsrfToken();

        $data = $this->validate($this->all(), $this->validationRules());
        
        try {
            $db = $this->container->get('db');
            $db->beginTransaction();

            $subscriptionData = $this->prepareData($data);
            $subscription = Subscription::create($subscriptionData);
            
            $classIds = $data['classes'] ?? [];
            $subscription->syncClasses($classIds);

            // Generate instances for newly linked classes
            $this->generateInstancesForSubscription($subscription, $classIds);

            $db->commit();
            $this->session->success('Subscription plan created successfully.');
        } catch (\Exception $e) {
            $db->rollBack();
            logger('Subscription create error: ' . $e->getMessage(), 'error');
            $this->session->error('Error creating subscription plan.');
        }

        $this->redirect('/admin/subscriptions');
    }

    /**
     * Show the form for editing an existing subscription plan.
     */
    public function edit($id): void
    {
        $this->requireAdmin();
        $this->requirePermission('manage_subscriptions');

        $subscription = Subscription::find($id);
        if (!$subscription) {
            $this->session->error('Subscription plan not found.');
            $this->redirect('/admin/subscriptions');
            return;
        }

        $this->render('admin/subscriptions/form', [
            'subscription' => $subscription,
            'allClasses' => ClassModel::all('name'),
            'selectedClasses' => $subscription->getClassIds()
        ], 'admin');
    }

    /**
     * Update an existing subscription plan in the database.
     */
    public function update($id): void
    {
        $this->requireAdmin();
        $this->requirePermission('manage_subscriptions');
        $this->requireCsrfToken();
        
        $subscription = Subscription::find($id);
        if (!$subscription) {
            $this->session->error('Subscription plan not found.');
            $this->redirect('/admin/subscriptions');
            return;
        }

        $data = $this->validate($this->all(), $this->validationRules($id));
        
        try {
            $db = $this->container->get('db');
            $db->beginTransaction();

            $subscriptionData = $this->prepareData($data);
            $subscription->fill($subscriptionData)->save();
            
            $classIds = $data['classes'] ?? [];
            $subscription->syncClasses($classIds);
            
            // Generate instances for newly linked classes
            $this->generateInstancesForSubscription($subscription, $classIds);

            $db->commit();
            $this->session->success('Subscription plan updated successfully.');
        } catch (\Exception $e) {
            $db->rollBack();
            logger('Subscription update error: ' . $e->getMessage(), 'error');
            $this->session->error('Error updating subscription plan.');
        }

        $this->redirect('/admin/subscriptions');
    }

    /**
     * Delete a subscription plan.
     */
    public function delete($id): void
    {
        $this->requireAdmin();
        $this->requirePermission('manage_subscriptions');
        $this->requireCsrfToken();

        try {
            $subscription = Subscription::findOrFail($id);
            $subscription->delete();
            $this->session->success('Subscription plan deleted successfully.');
        } catch (\Exception $e) {
            logger('Subscription delete error: ' . $e->getMessage(), 'error');
            $this->session->error('Error deleting subscription plan. It may be in use by members.');
        }

        $this->redirect('/admin/subscriptions');
    }

    /**
     * Defines the validation rules for the subscription form.
     */
    private function validationRules($id = null): array
    {
        return [
            'name' => "required|max:255|unique:subscriptions,{$id}",
            'description' => 'nullable',
            'status' => 'required|in:active,inactive',
            'price' => 'required|numeric|min:0',
            'type' => 'required|in:recurring,fixed_length,session_based',
            'term_length' => 'required_if:type,recurring,fixed_length|integer|min:1',
            'term_unit' => 'required_if:type,recurring,fixed_length|in:day,week,month,year',
            'sessions' => 'required_if:type,session_based|integer|min:1',
            'fixed_start_day' => 'nullable|integer|min:1|max:28',
            'prorata_price' => 'nullable|numeric|min:0',
            'capacity' => 'nullable|integer|min:0',
            'min_age' => 'nullable|integer|min:0',
            'max_age' => 'nullable|integer|min:0|gte:min_age',
            'classes' => 'nullable|array'
        ];
    }

    /**
     * Prepares form data for database insertion/update.
     */
    private function prepareData(array $data): array
    {
        $data['prorata_enabled'] = isset($data['prorata_enabled']) ? 1 : 0;
        $data['free_trial_enabled'] = isset($data['free_trial_enabled']) ? 1 : 0;
        $data['charge_on_start_date'] = isset($data['charge_on_start_date']) ? 1 : 0;
        $data['auto_book'] = isset($data['auto_book']) ? 1 : 0;
        
        if ($data['type'] === 'recurring') {
            $data['sessions'] = null;
            if (!$data['prorata_enabled']) {
                $data['prorata_price'] = null;
            }
        } elseif ($data['type'] === 'session_based') {
            $data['term_length'] = $data['sessions'];
            $data['term_unit'] = 'session';
            $data['fixed_start_day'] = null;
            $data['prorata_enabled'] = 0;
            $data['prorata_price'] = null;
        } else { // fixed_length
            $data['fixed_start_day'] = null;
            $data['prorata_enabled'] = 0;
            $data['prorata_price'] = null;
        }
        
        $data['status'] = $data['status'] ?? 'active';

        return $data;
    }

    /**
     * Helper method to generate class instances based on a subscription's term.
     */
    private function generateInstancesForSubscription(Subscription $subscription, array $classIds): void
    {
        if (empty($classIds)) {
            return;
        }

        $today = new \DateTime();
        $startDate = null;
        $endDate = null;

        // **THE FIX**: Determine the correct start date of the CURRENT billing period.
        if ($subscription->fixed_start_day) {
            $currentDay = (int)$today->format('d');
            $fixedDay = $subscription->fixed_start_day;

            if ($currentDay >= $fixedDay) {
                // The period started this month on the fixed day.
                $startDate = new \DateTime($today->format("Y-m-{$fixedDay}"));
            } else {
                // The period started last month on the fixed day.
                $startDate = (new \DateTime($today->format("Y-m-{$fixedDay}")))->modify('-1 month');
            }
        } else {
            // For rolling subscriptions, the period effectively starts from today for generation purposes.
			//$startDate = $today;
            $startDate = new \DateTime($today->format("Y-m-01"));
        }

        // Now, calculate the end date based on this correct start date.
        if (in_array($subscription->type, ['recurring', 'fixed_length'])) {
            $length = $subscription->term_length;
            $unit = $subscription->term_unit;
            
            // Create a precise interval to add to the period's start date
            $endDate = (clone $startDate)->modify("+{$length} {$unit}")->modify('-1 day');
        
        } elseif ($subscription->type === 'session_based') {
             // For session-based, generate for a reasonable future period to find slots
             $endDate = (clone $startDate)->add(new \DateInterval('P3M'));
        }

        if ($endDate) {
            // Ensure we don't generate instances for dates in the past.
            $generationStartDate = max($today, $startDate);

            foreach ($classIds as $classId) {
                $class = ClassModel::find($classId);
                if ($class && $class->class_type === 'recurring_parent') {
                    // Call the powerful method with the specific, corrected date range
                    $class->generateInstances($generationStartDate, $endDate);
                }
            }
        }
    }
}
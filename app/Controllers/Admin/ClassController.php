<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\ClassModel;
use App\Models\Subscription;

class ClassController extends Controller
{
    public function index(): void
    {
        $this->requireAdmin();
        $this->requirePermission('view_classes');
        $classes = ClassModel::all('name');
        $this->render('admin/classes/index', ['classes' => $classes], 'admin');
    }

    public function create(): void
    {
        $this->requireAdmin();
        $this->requirePermission('manage_classes');
        $this->render('admin/classes/form', [
            'class' => (object)[],
            'allSubscriptions' => Subscription::all('name'),
            'selectedSubscriptions' => []
        ], 'admin');
    }

    public function store(): void
    {
        $this->requireAdmin();
        $this->requirePermission('manage_classes');
        $this->requireCsrfToken();

        $data = $this->validate($this->all(), $this->validationRules());
        
        try {
            $classData = $this->prepareData($data);
            ClassModel::create($classData);
            // NOTE: We no longer sync subscriptions or generate instances here.
            $this->session->success('Class template created successfully.');
        } catch (\Exception $e) {
            logger('Class create error: ' . $e->getMessage(), 'error');
            $this->session->error('Error creating class: ' . $e->getMessage());
        }

        $this->redirect('/admin/classes');
    }


    public function edit($id): void
    {
        $this->requireAdmin();
        $this->requirePermission('manage_classes');
        $class = ClassModel::find($id);
        if (!$class) {
            $this->session->error('Class not found.');
            $this->redirect('/admin/classes');
            return;
        }
        $this->render('admin/classes/form', [
            'class' => $class,
            'allSubscriptions' => Subscription::all('name'),
            'selectedSubscriptions' => $class->getSubscriptionIds()
        ], 'admin');
    }

    public function update($id): void
    {
        $this->requireAdmin();
        $this->requirePermission('manage_classes');
        $this->requireCsrfToken();
        
        $class = ClassModel::find($id);
        if (!$class) { /* ... error handling ... */ }

        $data = $this->validate($this->all(), $this->validationRules($id));
        
        try {
            $classData = $this->prepareData($data);
            $class->fill($classData)->save();
            // NOTE: We no longer sync subscriptions or generate instances here.
            $this->session->success('Class template updated successfully.');
        } catch (\Exception $e) {
            logger('Class update error: ' . $e->getMessage(), 'error');
            $this->session->error('Error updating class: ' . $e->getMessage());
        }

        $this->redirect('/admin/classes');
    }

    /**
     * Delete a class.
     */
    public function delete($id): void
    {
        $this->requireAdmin();
        $this->requirePermission('manage_classes');
        $this->requireCsrfToken();

        try {
            $class = ClassModel::findOrFail($id);
            $class->delete(); 
            $this->session->success('Class and all its scheduled instances deleted successfully.');
        } catch (\Exception $e) {
            logger('Class delete error: ' . $e->getMessage(), 'error');
            $this->session->error('Error deleting class. It may have active member bookings.');
        }

        $this->redirect('/admin/classes');
    }

    /**
     * Defines validation rules for the class form.
     */
    private function validationRules($id = null): array
    {
        // **THIS IS THE CRITICAL FIX**
        // The unique rule is now correctly formatted to ignore the current ID during updates.
        return [
            'name' => "required|max:255|unique:classes,{$id}",
            'description' => 'nullable',
            'class_type' => 'required|in:single,recurring_parent',
            'start_date' => 'required_if:class_type,single|date',
            'start_time' => 'required',
            'original_start_date' => 'required_if:class_type,recurring_parent|date',
            'frequency' => 'required_if:class_type,recurring_parent',
            'day_of_week' => 'required_if:class_type,recurring_parent',
            'duration_minutes' => 'required|integer|min:1',
            'capacity' => 'nullable|integer|min:0',
            'session_price' => 'nullable|numeric|min:0',
            'allow_booking_outside_subscription' => 'nullable',
            'auto_book' => 'nullable',
            'subscriptions' => 'nullable|array'
        ];
    }

    /**
     * Prepares form data for database insertion/update.
     */
    private function prepareData(array $data): array
    {
        $data['auto_book'] = isset($data['auto_book']) ? 1 : 0;
        $data['allow_booking_outside_subscription'] = isset($data['allow_booking_outside_subscription']) ? 1 : 0;
        
        if ($data['class_type'] === 'single') {
            $data['original_start_date'] = $data['start_date'];
            $data['frequency'] = null;
            $data['day_of_week'] = null;
        }

        if ($data['allow_booking_outside_subscription'] == 0) {
            $data['session_price'] = null;
        }

        return $data;
    }
}
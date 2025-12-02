<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\ClassModel;
use App\Models\ClassInstance;

class ClassInstanceController extends Controller
{
    /**
     * Display a list of upcoming instances for a specific class.
     */
    public function index($classId): void
    {
        $this->requireAdmin();
        $this->requirePermission('manage_classes');

        $class = ClassModel::find($classId);
        if (!$class) {
            $this->session->error('Class not found.');
            $this->redirect('/admin/classes');
            return;
        }

        // Fetch upcoming instances for this class
        $instances = ClassInstance::where('class_parent_id', '=', $classId)
                                  ->andWhere('instance_date_time', '>=', date('Y-m-d H:i:s'))
                                  ->orderBy('instance_date_time', 'ASC')
                                  ->fetchAll();

        $this->render('admin/classes/instances', [
            'class' => $class,
            'instances' => $instances
        ], 'admin');
    }

    /**
     * Delete a specific class instance.
     */
    public function delete($classId, $instanceId): void
    {
        $this->requireAdmin();
        $this->requirePermission('manage_classes');
        $this->requireCsrfToken();

        try {
            $instance = ClassInstance::find($instanceId);
            // You could add a check here to ensure the instance has no active bookings before deleting
            if ($instance) {
                $instance->delete();
                $this->session->success('Class session deleted successfully.');
            } else {
                $this->session->error('Session not found.');
            }
        } catch (\Exception $e) {
            logger('Class instance delete error: ' . $e->getMessage(), 'error');
            $this->session->error('Error deleting session.');
        }

        $this->redirect('/admin/classes/' . $classId . '/instances');
    }
}
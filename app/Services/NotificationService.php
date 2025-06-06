<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Collection;

class NotificationService
{
    /**
     * Create notification for single user
     */
    public function createForUser(
        int $userId,
        string $type,
        string $title,
        string $message,
        array $options = []
    ): Notification {
        return Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $options['data'] ?? null,
            'sender_id' => $options['sender_id'] ?? null,
            'is_important' => $options['is_important'] ?? false,
            'priority' => $options['priority'] ?? Notification::PRIORITY_NORMAL,
            'category' => $options['category'] ?? Notification::CATEGORY_GENERAL,
            'action_url' => $options['action_url'] ?? null,
            'metadata' => $options['metadata'] ?? null,
        ]);
    }

    /**
     * Create notification for multiple users
     */
    public function createForUsers(
        array $userIds,
        string $type,
        string $title,
        string $message,
        array $options = []
    ): void {
        $notificationData = [
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $options['data'] ?? null,
            'sender_id' => $options['sender_id'] ?? null,
            'is_important' => $options['is_important'] ?? false,
            'priority' => $options['priority'] ?? Notification::PRIORITY_NORMAL,
            'category' => $options['category'] ?? Notification::CATEGORY_GENERAL,
            'action_url' => $options['action_url'] ?? null,
            'metadata' => $options['metadata'] ?? null,
        ];

        Notification::createBulkForUsers($userIds, $notificationData);
    }

    /**
     * Create notification for all users
     */
    public function createForAllUsers(
        string $type,
        string $title,
        string $message,
        array $options = []
    ): void {
        $userIds = User::pluck('id')->toArray();
        $this->createForUsers($userIds, $type, $title, $message, $options);
    }

    /**
     * Create notification for users by role
     */
    public function createForRole(
        string $role,
        string $type,
        string $title,
        string $message,
        array $options = []
    ): void {
        $userIds = User::role($role)->pluck('id')->toArray();
        $this->createForUsers($userIds, $type, $title, $message, $options);
    }

    /**
     * Create notification for users by department
     */
    public function createForDepartment(
        string $department,
        string $type,
        string $title,
        string $message,
        array $options = []
    ): void {
        $userIds = User::where('department', $department)->pluck('id')->toArray();
        $this->createForUsers($userIds, $type, $title, $message, $options);
    }

    /**
     * Leave request notification helpers
     */
    public function notifyLeaveRequest(User $employee, array $leaveData): void
    {
        // Notify HR and managers
        $hrUsers = User::role('hr')->pluck('id')->toArray();
        $managers = User::role('manager')->pluck('id')->toArray();
        $notifyUsers = array_unique(array_merge($hrUsers, $managers));

        $this->createForUsers(
            $notifyUsers,
            Notification::TYPE_LEAVE_REQUEST,
            'New Leave Request',
            "{$employee->name} has submitted a leave request from {$leaveData['start_date']} to {$leaveData['end_date']}",
            [
                'category' => Notification::CATEGORY_LEAVE,
                'priority' => Notification::PRIORITY_HIGH,
                'data' => $leaveData,
                'action_url' => "/leave-requests/{$leaveData['id']}"
            ]
        );
    }

    public function notifyLeaveApproved(User $employee, array $leaveData): void
    {
        $this->createForUser(
            $employee->id,
            Notification::TYPE_LEAVE_APPROVED,
            'Leave Request Approved',
            "Your leave request from {$leaveData['start_date']} to {$leaveData['end_date']} has been approved",
            [
                'category' => Notification::CATEGORY_LEAVE,
                'priority' => Notification::PRIORITY_HIGH,
                'data' => $leaveData,
                // 'action_url' => "/leave-requests/{$leaveData['id']}"
            ]
        );
    }

    public function notifyLeaveRejected(User $employee, array $leaveData, string $reason = ''): void
    {
        $message = "Your leave request from {$leaveData['start_date']} to {$leaveData['end_date']} has been rejected";
        if ($reason) {
            $message .= ". Reason: {$reason}";
        }

        $this->createForUser(
            $employee->id,
            Notification::TYPE_LEAVE_REJECTED,
            'Leave Request Rejected',
            $message,
            [
                'category' => Notification::CATEGORY_LEAVE,
                'priority' => Notification::PRIORITY_HIGH,
                'data' => array_merge($leaveData, ['rejection_reason' => $reason]),
                // 'action_url' => "/leave-requests/{$leaveData['id']}"
            ]
        );
    }

    /**
     * Overtime request notification helpers
     */
    public function notifyOvertimeRequest(User $employee, array $overtimeData): void
    {
        // Notify HR and managers
        $hrUsers = User::role('hr')->pluck('id')->toArray();
        $managers = User::role('manager')->pluck('id')->toArray();
        $notifyUsers = array_unique(array_merge($hrUsers, $managers));

        $duration = $overtimeData['duration'] ?? 'N/A';
        $date = $overtimeData['date'] ?? 'N/A';

        $this->createForUsers(
            $notifyUsers,
            Notification::TYPE_OVERTIME_REQUEST,
            'New Overtime Request',
            "{$employee->name} has submitted an overtime request for {$duration} hours on {$date}",
            [
                'category' => Notification::CATEGORY_OVERTIME,
                'priority' => Notification::PRIORITY_HIGH,
                'data' => $overtimeData,
                'action_url' => "/overtime-requests/{$overtimeData['id']}"
            ]
        );
    }

    public function notifyOvertimeApproved(User $employee, array $overtimeData): void
    {
        $duration = $overtimeData['number_of_days'] ?? 'N/A';
        $date = $overtimeData['overtime_date'] ?? 'N/A';

        $this->createForUser(
            $employee->id,
            Notification::TYPE_OVERTIME_APPROVED,
            'Overtime Request Approved',
            "Your overtime request for {$duration} hours on {$date} has been approved",
            [
                'category' => Notification::CATEGORY_OVERTIME,
                'priority' => Notification::PRIORITY_HIGH,
                'data' => $overtimeData,
                // 'action_url' => "/overtime-requests/{$overtimeData['id']}"
            ]
        );
    }

    public function notifyOvertimeRejected(User $employee, array $overtimeData, string $reason = ''): void
    {
        $duration = $overtimeData['number_of_days'] ?? 'N/A';
        $date = $overtimeData['overtime_date'] ?? 'N/A';

        $message = "Your overtime request for {$duration} hours on {$date} has been rejected";
        if ($reason) {
            $message .= ". Reason: {$reason}";
        }

        $this->createForUser(
            $employee->id,
            Notification::TYPE_OVERTIME_REJECTED,
            'Overtime Request Rejected',
            $message,
            [
                'category' => Notification::CATEGORY_OVERTIME,
                'priority' => Notification::PRIORITY_HIGH,
                'data' => array_merge($overtimeData, ['rejection_reason' => $reason]),
                // 'action_url' => "/overtime-requests/{$overtimeData['id']}"
            ]
        );
    }

    /**
     * Reimburse request notification helpers
     */
    public function notifyReimburseRequest(User $employee, array $reimburseData): void
    {
        // Notify HR, managers, and finance
        $hrUsers = User::role('hr')->pluck('id')->toArray();
        $managers = User::role('manager')->pluck('id')->toArray();
        $financeUsers = User::role('finance')->pluck('id')->toArray();
        $notifyUsers = array_unique(array_merge($hrUsers, $managers, $financeUsers));

        $amount = $reimburseData['amount'] ?? 'N/A';
        $description = $reimburseData['description'] ?? 'No description';

        $this->createForUsers(
            $notifyUsers,
            Notification::TYPE_REIMBURSE_REQUEST,
            'New Reimbursement Request',
            "{$employee->name} has submitted a reimbursement request for Rp " . number_format($amount) . " - {$description}",
            [
                'category' => Notification::CATEGORY_FINANCE,
                'priority' => Notification::PRIORITY_HIGH,
                'data' => $reimburseData,
                'action_url' => "/reimburse-requests/{$reimburseData['id']}"
            ]
        );
    }

    public function notifyReimburseApproved(User $employee, array $reimburseData): void
    {
        $amount = $reimburseData['amount'] ?? 'N/A';
        $remark = $reimburseData['remark'] ?? 'No remark';

        $this->createForUser(
            $employee->id,
            Notification::TYPE_REIMBURSE_APPROVED,
            'Reimbursement Request Approved',
            "Your reimbursement request for Rp " . number_format($amount) . " ({$remark}) has been approved",
            [
                'category' => Notification::CATEGORY_FINANCE,
                'priority' => Notification::PRIORITY_HIGH,
                'data' => $reimburseData,
                // 'action_url' => "/reimburse-requests/{$reimburseData['id']}"
            ]
        );
    }

    public function notifyReimburseRejected(User $employee, array $reimburseData, string $reason = ''): void
    {
        $amount = $reimburseData['amount'] ?? 'N/A';
        $remark = $reimburseData['remark'] ?? 'No remark';

        $message = "Your reimbursement request for Rp " . number_format($amount) . " has been rejected";
        if ($reason) {
            $message .= ". Reason: {$reason}";
        }

        $this->createForUser(
            $employee->id,
            Notification::TYPE_REIMBURSE_REJECTED,
            'Reimbursement Request Rejected',
            $message,
            [
                'category' => Notification::CATEGORY_FINANCE,
                'priority' => Notification::PRIORITY_HIGH,
                'data' => array_merge($reimburseData, ['rejection_reason' => $reason]),
                // 'action_url' => "/reimburse-requests/{$reimburseData['id']}"
            ]
        );
    }

    public function notifyReimbursePaid(User $employee, array $reimburseData): void
    {
        $amount = $reimburseData['amount'] ?? 'N/A';
        $remark = $reimburseData['remark'] ?? 'No remark';

        $this->createForUser(
            $employee->id,
            Notification::TYPE_REIMBURSE_PAID,
            'Reimbursement Paid',
            "Your reimbursement for Rp " . number_format($amount) . " ({$remark}) has been processed and paid",
            [
                'category' => Notification::CATEGORY_FINANCE,
                'priority' => Notification::PRIORITY_HIGH,
                'data' => $reimburseData,
                // 'action_url' => "/reimburse-requests/{$reimburseData['id']}"
            ]
        );
    }

    /**
     * Attendance reminder notifications
     */
    public function notifyAttendanceReminder(Collection $users): void
    {
        $userIds = $users->pluck('id')->toArray();

        $this->createForUsers(
            $userIds,
            Notification::TYPE_ATTENDANCE_REMINDER,
            'Attendance Reminder',
            'Don\'t forget to check in for today',
            [
                'category' => Notification::CATEGORY_ATTENDANCE,
                'priority' => Notification::PRIORITY_NORMAL,
                'action_url' => '/attendance/check-in'
            ]
        );
    }

    /**
     * Payroll notifications
     */
    public function notifyPayrollGenerated(Collection $users, string $period): void
    {
        $userIds = $users->pluck('id')->toArray();

        $this->createForUsers(
            $userIds,
            Notification::TYPE_PAYROLL_INFO,
            'Payroll Generated',
            "Your payroll for {$period} has been generated and is ready for review",
            [
                'category' => Notification::CATEGORY_PAYROLL,
                'priority' => Notification::PRIORITY_HIGH,
                'data' => ['period' => $period],
                'action_url' => '/payroll'
            ]
        );
    }

    /**
     * Birthday notifications
     */
    public function notifyBirthdays(Collection $birthdayUsers): void
    {
        foreach ($birthdayUsers as $user) {
            // Notify all other users about the birthday
            $otherUsers = User::where('id', '!=', $user->id)->pluck('id')->toArray();

            $this->createForUsers(
                $otherUsers,
                Notification::TYPE_BIRTHDAY,
                'Birthday Today! 🎉',
                "It's {$user->name}'s birthday today! Don't forget to wish them well.",
                [
                    'category' => Notification::CATEGORY_GENERAL,
                    'priority' => Notification::PRIORITY_LOW,
                    'data' => ['birthday_user' => $user->name],
                    'is_important' => false
                ]
            );
        }
    }

    /**
     * Document expiry notifications
     */
    public function notifyDocumentExpiry(User $user, array $documentData): void
    {
        $this->createForUser(
            $user->id,
            Notification::TYPE_DOCUMENT_EXPIRY,
            'Document Expiring Soon',
            "Your {$documentData['type']} will expire on {$documentData['expiry_date']}. Please renew it soon.",
            [
                'category' => Notification::CATEGORY_HR,
                'priority' => Notification::PRIORITY_HIGH,
                'data' => $documentData,
                'action_url' => '/documents'
            ]
        );
    }

    /**
     * General announcement
     */
    public function createAnnouncement(
        string $title,
        string $message,
        $targetUsers = null,
        array $options = []
    ): void {
        $userIds = $targetUsers ?? User::pluck('id')->toArray();

        $this->createForUsers(
            $userIds,
            Notification::TYPE_ANNOUNCEMENT,
            $title,
            $message,
            array_merge([
                'category' => Notification::CATEGORY_ANNOUNCEMENT,
                'priority' => Notification::PRIORITY_NORMAL,
                'is_important' => true
            ], $options)
        );
    }

    /**
     * Clean old notifications
     */
    public function cleanOldNotifications(int $days = 90): int
    {
        return Notification::where('created_at', '<', now()->subDays($days))->delete();
    }
}

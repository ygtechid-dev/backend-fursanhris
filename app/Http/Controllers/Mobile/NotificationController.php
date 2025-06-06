<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    /**
     * Get paginated notifications for authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $perPage = $request->get('per_page', 15);
        $category = $request->get('category');
        $type = $request->get('type');
        $unreadOnly = $request->boolean('unread_only', false);

        $query = Notification::forUser($user->id)
            ->with('sender:id,name,avatar')
            ->orderBy('is_important', 'desc')
            ->orderBy('created_at', 'desc');

        if ($category) {
            $query->byCategory($category);
        }

        if ($type) {
            $query->byType($type);
        }

        if ($unreadOnly) {
            $query->unread();
        }

        $notifications = $query->paginate($perPage);
        return response()->json([
            'success' => true,
            'message' => 'Notifications retrieved successfully',
            'data' => $notifications->items(),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
                'unread_count' => $this->getUnreadCount($user->id)
            ]
        ]);
    }

    /**
     * Get unread notifications count
     */
    public function getUnreadCount($userId = null)
    {
        $userId = $userId ?: Auth::id();
        return Notification::forUser($userId)->unread()->count();
    }

    /**
     * Get unread count endpoint
     */
    public function unreadCount(): JsonResponse
    {
        $count = $this->getUnreadCount();

        return response()->json([
            'success' => true,
            'data' => [
                'unread_count' => $count
            ]
        ]);
    }

    /**
     * Get notification statistics
     */
    public function stats(): JsonResponse
    {
        $user = Auth::user();

        $stats = [
            'total' => Notification::forUser($user->id)->count(),
            'unread' => Notification::forUser($user->id)->unread()->count(),
            'important' => Notification::forUser($user->id)->important()->count(),
            'today' => Notification::forUser($user->id)->whereDate('created_at', today())->count(),
            'this_week' => Notification::forUser($user->id)->whereBetween('created_at', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])->count(),
            'by_category' => Notification::forUser($user->id)
                ->selectRaw('category, count(*) as count')
                ->groupBy('category')
                ->pluck('count', 'category')
                ->toArray()
        ];

        return response()->json([
            'success' => true,
            'message' => 'Notification statistics retrieved successfully',
            'data' => $stats
        ]);
    }

    /**
     * Get single notification
     */
    public function show(int $id): JsonResponse
    {
        $notification = Notification::forUser(Auth::id())
            ->with('sender:id,name,avatar')
            ->find($id);

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Notification retrieved successfully',
            'data' => $notification
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(int $id): JsonResponse
    {
        $notification = Notification::forUser(Auth::id())->find($id);

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
            'data' => [
                'unread_count' => $this->getUnreadCount()
            ]
        ]);
    }

    /**
     * Mark notification as unread
     */
    public function markAsUnread(int $id): JsonResponse
    {
        $notification = Notification::forUser(Auth::id())->find($id);

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }

        $notification->markAsUnread();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as unread',
            'data' => [
                'unread_count' => $this->getUnreadCount()
            ]
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(): JsonResponse
    {
        Notification::forUser(Auth::id())
            ->unread()
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read',
            'data' => [
                'unread_count' => 0
            ]
        ]);
    }

    /**
     * Mark multiple notifications as read
     */
    public function markMultipleAsRead(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'notification_ids' => 'required|array',
            'notification_ids.*' => 'integer|exists:notifications,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        Notification::forUser(Auth::id())
            ->whereIn('id', $request->notification_ids)
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Selected notifications marked as read',
            'data' => [
                'unread_count' => $this->getUnreadCount()
            ]
        ]);
    }

    /**
     * Delete notification
     */
    public function destroy(int $id): JsonResponse
    {
        $notification = Notification::forUser(Auth::id())->find($id);

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted successfully',
            'data' => [
                'unread_count' => $this->getUnreadCount()
            ]
        ]);
    }

    /**
     * Delete multiple notifications
     */
    public function destroyMultiple(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'notification_ids' => 'required|array',
            'notification_ids.*' => 'integer|exists:notifications,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        Notification::forUser(Auth::id())
            ->whereIn('id', $request->notification_ids)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Selected notifications deleted successfully',
            'data' => [
                'unread_count' => $this->getUnreadCount()
            ]
        ]);
    }

    /**
     * Clear all read notifications
     */
    public function clearRead(): JsonResponse
    {
        $deletedCount = Notification::forUser(Auth::id())
            ->read()
            ->delete();

        return response()->json([
            'success' => true,
            'message' => "Deleted {$deletedCount} read notifications",
            'data' => [
                'deleted_count' => $deletedCount,
                'unread_count' => $this->getUnreadCount()
            ]
        ]);
    }

    /**
     * Get notification categories and types
     */
    public function getMetadata(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'categories' => [
                    ['value' => Notification::CATEGORY_GENERAL, 'label' => 'General'],
                    ['value' => Notification::CATEGORY_LEAVE, 'label' => 'Leave'],
                    ['value' => Notification::CATEGORY_ATTENDANCE, 'label' => 'Attendance'],
                    ['value' => Notification::CATEGORY_PAYROLL, 'label' => 'Payroll'],
                    ['value' => Notification::CATEGORY_ANNOUNCEMENT, 'label' => 'Announcement'],
                    ['value' => Notification::CATEGORY_HR, 'label' => 'HR'],
                ],
                'types' => [
                    ['value' => Notification::TYPE_LEAVE_REQUEST, 'label' => 'Leave Request'],
                    ['value' => Notification::TYPE_LEAVE_APPROVED, 'label' => 'Leave Approved'],
                    ['value' => Notification::TYPE_LEAVE_REJECTED, 'label' => 'Leave Rejected'],
                    ['value' => Notification::TYPE_ATTENDANCE_REMINDER, 'label' => 'Attendance Reminder'],
                    ['value' => Notification::TYPE_PAYROLL_INFO, 'label' => 'Payroll Information'],
                    ['value' => Notification::TYPE_ANNOUNCEMENT, 'label' => 'Announcement'],
                    ['value' => Notification::TYPE_BIRTHDAY, 'label' => 'Birthday'],
                    ['value' => Notification::TYPE_OVERTIME_REQUEST, 'label' => 'Overtime Request'],
                    ['value' => Notification::TYPE_DOCUMENT_EXPIRY, 'label' => 'Document Expiry'],
                    ['value' => Notification::TYPE_TRAINING_REMINDER, 'label' => 'Training Reminder'],
                ],
                'priorities' => [
                    ['value' => Notification::PRIORITY_LOW, 'label' => 'Low'],
                    ['value' => Notification::PRIORITY_NORMAL, 'label' => 'Normal'],
                    ['value' => Notification::PRIORITY_HIGH, 'label' => 'High'],
                    ['value' => Notification::PRIORITY_URGENT, 'label' => 'Urgent'],
                ]
            ]
        ]);
    }
}

<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'title',
        'message',
        'data',
        'user_id',
        'sender_id',
        'read_at',
        'is_important',
        'priority',
        'category',
        'action_url',
        'metadata'
    ];

    protected $casts = [
        'data' => 'array',
        'metadata' => 'array',
        'read_at' => 'datetime',
        'is_important' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $appends = [
        'is_read',
        'time_ago',
        'formatted_date'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    // Accessors
    public function getIsReadAttribute(): bool
    {
        return !is_null($this->read_at);
    }

    public function getTimeAgoAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    public function getFormattedDateAttribute(): string
    {
        return $this->created_at->format('d M Y, H:i');
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeImportant($query)
    {
        return $query->where('is_important', true);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', Carbon::now()->subDays($days));
    }

    // Methods
    public function markAsRead(): void
    {
        $this->update(['read_at' => now()]);
    }

    public function markAsUnread(): void
    {
        $this->update(['read_at' => null]);
    }

    public static function createForUser(array $data): self
    {
        return self::create($data);
    }

    public static function createBulkForUsers(array $userIds, array $data): void
    {
        $notifications = [];
        $now = now();

        foreach ($userIds as $userId) {
            $notifications[] = array_merge($data, [
                'user_id' => $userId,
                'created_at' => $now,
                'updated_at' => $now
            ]);
        }

        self::insert($notifications);
    }

    // Constants for notification types
    const TYPE_LEAVE_REQUEST = 'leave_request';
    const TYPE_LEAVE_APPROVED = 'leave_approved';
    const TYPE_LEAVE_REJECTED = 'leave_rejected';
    const TYPE_ATTENDANCE_REMINDER = 'attendance_reminder';
    const TYPE_PAYROLL_INFO = 'payroll_info';
    const TYPE_ANNOUNCEMENT = 'announcement';
    const TYPE_BIRTHDAY = 'birthday';
    const TYPE_DOCUMENT_EXPIRY = 'document_expiry';
    const TYPE_TRAINING_REMINDER = 'training_reminder';
    const TYPE_OVERTIME_REQUEST = 'overtime_request';
    const TYPE_OVERTIME_APPROVED = 'overtime_approved';
    const TYPE_OVERTIME_REJECTED = 'overtime_rejected';
    const TYPE_REIMBURSE_REQUEST = 'reimburse_request';
    const TYPE_REIMBURSE_APPROVED = 'reimburse_approved';
    const TYPE_REIMBURSE_REJECTED = 'reimburse_rejected';
    const TYPE_REIMBURSE_PAID = 'reimburse_paid';

    // Categories
    const CATEGORY_OVERTIME = 'overtime';
    const CATEGORY_FINANCE = 'finance';

    // Constants for categories
    const CATEGORY_GENERAL = 'general';
    const CATEGORY_LEAVE = 'leave';
    const CATEGORY_ATTENDANCE = 'attendance';
    const CATEGORY_PAYROLL = 'payroll';
    const CATEGORY_ANNOUNCEMENT = 'announcement';
    const CATEGORY_HR = 'hr';

    // Constants for priority
    const PRIORITY_LOW = 'low';
    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Termination extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'employee_id',
        'termination_type',  // e.g., voluntary, involuntary, retirement
        'termination_date',
        'description',
        'reason',
        'notice_date',
        'terminated_by',     // ID of the admin who performed the termination
        'is_mobile_access_allowed',  // boolean to allow mobile access
        'status',            // active, inactive, etc.
        'company_id',
        'documents',         // JSON field for any termination documents paths
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'termination_date' => 'datetime',
        'notice_date' => 'datetime',
        'is_mobile_access_allowed' => 'boolean',
        'documents' => 'array',
    ];

    /**
     * Get the user associated with the termination.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the employee associated with the termination.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the admin who performed the termination.
     */
    public function terminatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'terminated_by');
    }

    /**
     * Get the company associated with the termination.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(User::class, 'company_id');
    }
}

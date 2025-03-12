<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reimbursement extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employee_id',
        'request_number',
        'title',
        'description',
        'amount',
        'receipt_path',
        'status', // pending, approved, rejected, paid
        'transaction_date',
        'requested_at',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'paid_by',
        'paid_at',
        'payment_method',
        'notes',
        'category_id',
        'created_by'
    ];

    protected $dates = [
        'requested_at',
        'transaction_date',
        'approved_at',
        'rejected_at',
        'paid_at',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function category()
    {
        return $this->belongsTo(ReimbursementCategory::class, 'category_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejecter()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function payer()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    // public function items()
    // {
    //     return $this->hasMany(ReimbursementItem::class);
    // }
}

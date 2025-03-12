<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReimbursementCategory extends Model
{
    protected $fillable = [
        'name',
        'description',
        'max_amount', // Batas maksimal penggantian per kategori (opsional)
        'is_active'
    ];

    public function reimbursements()
    {
        return $this->hasMany(Reimbursement::class, 'category_id');
    }
}

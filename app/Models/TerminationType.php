<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TerminationType extends Model
{
    protected $fillable = [
        'name',
        'created_by',
    ];

    /**
     * Get the terminations for this termination type.
     */
    public function terminations(): HasMany
    {
        return $this->hasMany(Termination::class, 'termination_type_id');
    }
}

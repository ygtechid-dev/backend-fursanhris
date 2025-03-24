<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RewardType extends Model
{
    use HasFactory;

    protected $table = 'reward_types';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'created_by',
    ];

    /**
     * Get the rewards for the award type.
     */
    public function rewards(): HasMany
    {
        return $this->hasMany(Reward::class);
    }
}

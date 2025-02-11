<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'company_name',
        'email',
        'password',
        'type', // company, employee, dan lain lain
        'avatar',
        'lang',
        'plan',
        'storage_limit',
        // 'referral_code',
        // 'used_referral_code',
        'commission_amount',
        'paid_amount',
        'trial_expire_date',
        'is_trial_done',
        'is_login_enable',
        'created_by',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            // 'password' => 'hashed',
        ];
    }

    public function employee_name()
    {

        $first_name = $this->first_name;
        $last_name = $this->last_name;

        return "$first_name $last_name";
    }

    public static function employeeIdFormat($number)
    {
        $settings = Utility::settings();

        return $settings["employee_prefix"] . sprintf("%05d", $number);
    }

    public function employee()
    {
        return $this->hasOne(Employee::class, 'user_id');
    }

    public function currentLanguage()
    {
        return $this->lang;
    }

    public function creatorId()
    {

        if ($this->type == 'company' || $this->type == 'super admin') {
            return $this->id;
        } else {
            return $this->created_by;
        }
    }
}

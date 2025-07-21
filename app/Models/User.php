<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'subscription',        // KOLOM BARU
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

    /**
     * Check if the user is terminated
     * 
     * @return bool
     */
    public function isTerminated()
    {
        return $this->activeTermination()->exists();
    }

    /**
     * Get the active termination record for this user, if any
     */
    public function activeTermination()
    {
        return $this->terminations()->where('status', 'active')->latest();
    }

    /**
     * Get all termination records for this user
     */
    public function terminations(): HasMany
    {
        return $this->hasMany(Termination::class);
    }

    /**
     * Terminate an employee
     * 
     * @param array $terminationData
     * @return Termination|false
     */
    public function terminate(array $terminationData)
    {
        if ($this->type !== 'employee') {
            return false;
        }

        // Set default values if not provided
        $terminationData['user_id'] = $this->id;
        $terminationData['employee_id'] = $this->employee->id ?? null;
        $terminationData['company_id'] = $this->created_by;
        $terminationData['status'] = 'active';

        // Create the termination record
        $termination = Termination::create($terminationData);

        // Optionally modify the user's login capabilities
        if ($termination && isset($terminationData['is_mobile_access_allowed'])) {
            $this->is_login_enable = $terminationData['is_mobile_access_allowed'];
            $this->save();
        }

        return $termination;
    }

    /**
     * Reinstate a terminated employee
     * 
     * @return bool
     */
    public function reinstate()
    {
        if ($this->type !== 'employee') {
            return false;
        }

        // Find and deactivate all active termination records
        $activeTerminations = $this->terminations()->where('status', 'active')->get();

        foreach ($activeTerminations as $termination) {
            $termination->status = 'inactive';
            $termination->save();
        }

        // Re-enable login
        $this->is_login_enable = true;

        return $this->save();
    }

    /**
     * Check if the user can login based on platform
     * 
     * @param string $platform 'web' or 'mobile'
     * @return bool
     */
    public function canLoginFromPlatform($platform = 'web')
    {
        // If login is disabled completely
        if (!$this->is_login_enable) {
            return false;
        }

        // Check if user is terminated and has platform restrictions
        $activeTermination = $this->activeTermination()->first();
        if ($activeTermination) {
            // If mobile access is allowed but user is trying from web
            if ($activeTermination->is_mobile_access_allowed && $platform === 'web') {
                return false;
            }

            // If terminated with no access at all
            if (!$activeTermination->is_mobile_access_allowed) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get all terminated employees for a company
     * 
     * @param int $companyId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function terminatedEmployees($companyId)
    {
        return static::whereHas('terminations', function ($query) {
            $query->where('status', 'active');
        })->where('created_by', $companyId)
            ->where('type', 'employee')
            ->get();
    }
}
<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * @property int $id
 * @property string $role
 * @property string $status
 * @property string $first_name
 * @property string $last_name
 * @property string $name Virtual full-name accessor, not a real column.
 * @property string $email
 * @property string|null $contact_number
 * @property string|null $username
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['role', 'status', 'first_name', 'last_name', 'email', 'contact_number', 'username', 'password'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    public const ROLE_RESIDENT = 'resident';

    public const ROLE_BFP_PERSONNEL = 'bfp_personnel';

    public const ROLE_BFP_ADMIN = 'bfp_admin';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    /**
     * Include the virtual `name` accessor when the model is serialized
     * (e.g. into Inertia's shared `auth.user` prop).
     */
    protected $appends = ['name'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    /**
     * Virtual `name` attribute so every existing Fortify/profile code path
     * (validation rules, controllers, frontend components) that reads or
     * writes `name` keeps working even though the DB stores first/last
     * name separately.
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn () => trim("{$this->first_name} {$this->last_name}"),
            set: function (string $value) {
                $parts = preg_split('/\s+/', trim($value), 2) ?: [];

                return [
                    'first_name' => $parts[0] ?? '',
                    'last_name' => $parts[1] ?? '',
                ];
            },
        );
    }

    public function isResident(): bool
    {
        return $this->role === self::ROLE_RESIDENT;
    }

    public function isBfpPersonnel(): bool
    {
        return $this->role === self::ROLE_BFP_PERSONNEL;
    }

    public function isBfpAdmin(): bool
    {
        return $this->role === self::ROLE_BFP_ADMIN;
    }

    /**
     * True for either BFP role — convenience check for gating the web portal.
     */
    public function isBfpStaff(): bool
    {
        return $this->isBfpPersonnel() || $this->isBfpAdmin();
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function personnelDetails(): HasOne
    {
        return $this->hasOne(BfpPersonnelDetails::class, 'user_id');
    }

    public function communityReports(): HasMany
    {
        return $this->hasMany(CommunityReport::class, 'user_id');
    }

    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class, 'created_by');
    }

    /**
     * FireSight's own `notification` table records — named distinctly from
     * Notifiable::notifications() (Laravel's built-in notifications system,
     * which this app isn't using but which the trait still provides).
     */
    public function appNotifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    /**
     * Duty shifts assigned to this personnel member.
     */
    public function dutySchedules(): HasMany
    {
        return $this->hasMany(DutySchedule::class, 'user_id');
    }
}

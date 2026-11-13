<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Role;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, LogsActivity, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    const ROLE_ADMIN = 1;
    const ROLE_MANAGER = 2;
    const ROLE_ACCOUNTANT = 3;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
    ];

    public function isAdmin()
    {
        return $this->role_id === self::ROLE_ADMIN;
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Permission names of the user's role, loaded once per request (listing pages check
     * permissions for every row). Each new request reads them again, so changes made under
     * Settings > Roles & Permissions apply on the user's next page load.
     */
    private ?array $permissionNames = null;

    private ?int $permissionNamesRoleId = null;

    public function hasPermission(string $permissionName): bool
    {
        if ($this->permissionNames === null || $this->permissionNamesRoleId !== $this->role_id) {
            $this->permissionNames = array_flip(\DB::table('permissions')
                ->join('permission_role', 'permissions.id', '=', 'permission_role.permission_id')
                ->where('permission_role.role_id', $this->role_id)
                ->pluck('permissions.name')
                ->all());
            $this->permissionNamesRoleId = $this->role_id;
        }

        return isset($this->permissionNames[$permissionName]);
    }


    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->useLogName('user')
            ->logOnly(['name', 'email', 'role'])
            ->dontLogIfAttributesChangedOnly(['password', 'updated_at']);
        // ->setDescriptionForEvent(fn (string $eventName) => "The user is {$eventName}");
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'role_id'           => 'integer',
    ];
}

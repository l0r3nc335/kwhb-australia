<?php

namespace App\Core\v1\User\Models;

use App\Models\Employee;
use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class User extends Model
{

    //Check in roles table if roles.id (5) is for roles.name = 'HR'
    public const HR_ROLE_ID = 5;
    public const HR_SUPPORT_STATUS = ['active', 'offswing'];

    use HasFactory, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['name', 'username', 'password'];

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
    ];

    public function employee()
    {
        return $this->hasOne(Employee::class, 'user_id', 'id');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_role');
    }

    public function hasRole(string|array $role, bool $matchCount = false)
    {
        $roles = is_array($role) ? $role : [$role];

        if ($matchCount) {
            return $this->roles()->whereIn('name', $roles)->count() === count($roles);
        }

        return $this->roles()->whereIn('name', $roles)->count() > 0;
    }
}

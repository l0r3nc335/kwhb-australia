<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Core\v1\Leave\Models\LeaveCredit;
use App\Core\v1\Notification\Models\Notification;
use App\Services\Authorization\AuthorizationService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable {

	use HasApiTokens;
	use HasFactory;
	use Notifiable;

	protected $with = array( 'role.permissions', 'employee');

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int, string>
	 */
	// protected $fillable = ['name', 'username', 'password', 'employee_id'];
	protected $fillable = array( 'name', 'username', 'password', 'role_id' );

	/**
	 * The attributes that should be hidden for serialization.
	 *
	 * @var array<int, string>
	 */
	protected $hidden = array(
		'password',
		'remember_token',
	);

	/**
	 * The attributes that should be cast.
	 *
	 * @var array<string, string>
	 */
	protected $casts = array(
		'email_verified_at' => 'datetime',
	);

    public function notifications()
    {
        return $this->belongsToMany(Notification::class);
    }

	public function credits() {
		return $this->hasMany( LeaveCredit::class );
	}

	public function employee() {
		return $this->hasOne( Employee::class, 'user_id', 'id' );
	}

	public function user_role() {
		return $this->hasOne( UserRole::class, 'user_id', 'id' );
	}

	public function roles() {
		return $this->belongsToMany( Role::class, 'user_role' );
	}

	public function role() {
		return $this->belongsTo( \App\Core\v1\Role\Models\Role::class );
	}

	public static function getUserByUsername( $username ) {
		return self::where( 'username', '=', $username )->first();
	}

	public static function getTokenSingle( $remember_token ) {
		return self::where( 'remember_token', '=', $remember_token )->first();
	}
}

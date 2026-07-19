<?php

namespace App\Core\v1\Role\Models;

use App\Core\v1\Permission\Models\Permission;
use App\Core\v1\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $with = ['permissions'];
    protected $appends = ['user_count', 'sample_users'];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class)->withPivot('scope')->orderBy('id', 'desc');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function getSampleUsersAttribute()
    {
        return $this->users()->with('employee')->limit(5)->get();
    }

    public function getUserCountAttribute()
    {
        return User::query()->where('role_id', $this->id)->count();
    }
}

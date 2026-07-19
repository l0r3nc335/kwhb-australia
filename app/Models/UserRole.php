<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    protected $fillable = [
        'user_id', 'role_id'
    ];
    protected $table = "user_role";
    public $timestamps = false;
    use HasFactory;

    public function role()
    {
        return $this->hasOne(Role::class, 'id', 'role_id');
    }
}

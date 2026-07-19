<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RolePermission extends Model
{
    protected $fillable = [
        'role_id', 'permission_id'
    ];
    protected $table = "role_permission";
    public $timestamps = false;
    use HasFactory;
}

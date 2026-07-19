<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class leave_type_role extends Model
{
    protected $fillable = [
        'leave_type_id', 'role_id', 'level'
    ];
    public $timestamps = false;
    use HasFactory;
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmergencyContact extends Model
{
    protected $fillable = [
        'employee_id', 'name', 'address', 'phone', 'email', 'relationship'
    ];
    use HasFactory;
}

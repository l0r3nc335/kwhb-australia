<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class address extends Model
{
    use HasFactory;
    protected $fillable = [
        'employee_id', 'name', 'label', 'address', 'second_address',
        'state', 'email', 'phone_number', 'is_default', 'is_active',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vaccination extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'validity',
        'custom_expiry',
        'division',
        'required',
        'role',
        'custom_expiry'
    ];
}

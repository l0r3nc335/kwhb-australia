<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MyobSetting extends Model
{
    protected $fillable = [
        'access_token', 'refresh_token', 'expires_in', 'token_type'
    ];
    use HasFactory;
}

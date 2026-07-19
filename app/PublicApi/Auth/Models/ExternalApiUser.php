<?php

namespace App\PublicApi\Auth\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;
use Laravel\Passport\HasApiTokens;

class ExternalApiUser extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'external_api_users'; // match your DB table
    protected $fillable = ['name', 'email', 'password'];
    protected $hidden = ['password'];

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserNotification extends Model
{
    protected $fillable = [
        'log_id', 'user_id'
    ];
    protected $table = "user_notification";
    public $timestamps = false;
    use HasFactory;
}

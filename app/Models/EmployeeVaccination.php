<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeVaccination extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'vaccination',
        'expiry_date',
        'status',
        'file',
        'file_name',
        'file_status',
        'comments',
        'exempt_reason',
        'assignee_user_id',
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function vaccination()
    {
        return $this->hasOne(Vaccination::class, 'name', 'vaccination');
    }

    public function assignee()
    {
        return $this->hasOne(User::class, 'id', 'assignee_user_id');
    }
}

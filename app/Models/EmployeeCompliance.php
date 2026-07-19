<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeCompliance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'compliance',
        'expiry_date',
        'status',
        'file',
        'file_name',
        'file_status',
        'comments',
        'assignee_user_id',
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function complianceModel()
    {
        return $this->hasOne(Compliance::class, 'name', 'compliance');
    }

    public function assignee()
    {
        return $this->hasOne(User::class, 'id', 'assignee_user_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Leave extends Model
{
    protected $fillable = [
        'employee_id', 'leave_type_id', 'date_from', 'date_to', 'total_days',
        'reason', 'date_filed', 'status', 'remarks', 'total_hours', 'approver_id'
    ];

    use HasFactory;

    public function leave_type()
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    public function approver()
    {
        return $this->hasOne(Employee::class, 'id', 'approver_id');
    }
    public function attachments()
    {
        return $this->hasMany(Attachment::class, "source_id", "id")->where("source", "leave");
    }
    public function leave_details()
    {
        return $this->hasMany(LeaveDetail::class, "leave_id", "id");
    }
}

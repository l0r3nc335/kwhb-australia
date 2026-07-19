<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $casts = [
        'dob' =>  'datetime:d/m/Y',
        'date_hired' =>  'datetime:d/m/Y',
    ];

    protected $fillable = [
        'id', 'user_id', 'first_name', 'last_name', 'gender', 'pronouns', 'indigenous',
        'personal_phone', 'personal_email', 'work_phone', 'work_email', 'job_title',
        'status', 'address', 'abn', 'tax_number', 'dob',
        'date_hired', 'location_id', 'division_id', 'employment_id', 'manager_id',
        'scheduled_date', 'scheduled_status'
    ];

    use HasFactory;

    protected $appends = ['full_name'];

    public function getFullNameAttribute()
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
    public function emergency_contact()
    {
        return $this->hasOne(EmergencyContact::class);
    }
    public function address()
    {
        return $this->hasMany(address::class);
    }
    public function medical()
    {
        return $this->hasOne(MedicalHistory::class);
    }
    public function bank_info()
    {
        return $this->hasMany(BankInformation::class)->orderBy('primary', 'Desc');
    }
    public function profile_image()
    {
        return $this->hasOne(Attachment::class, "source_id", "id")->where("name", "profile_image");
    }
    public function cover_image()
    {
        return $this->hasOne(Attachment::class, "source_id", "id")->where("name", "cover_image");
    }
    public function division()
    {
        return $this->belongsTo(Division::class);
    }
    public function employment()
    {
        return $this->belongsTo(Employment::class);
    }
    public function location()
    {
        return $this->belongsTo(Location::class);
    }
    public function manager()
    {
        return $this->belongsTo(Employee::class);
    }
}

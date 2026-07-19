<?php

namespace App\Core\v1\Employee\Models;

use App\Models\Position;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Core\v1\Bank\Models\Bank;
use App\Models\address;
use App\Models\Attachment;
use App\Models\BankInformation;
use App\Models\Division;
use App\Models\EmergencyContact;
use App\Models\Employment;
use App\Models\Location;
use App\Models\MedicalHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $id = 'employees';

    protected $with = ['user' , 'position'];

    const ID = 'id';

    protected $casts = [
        'dob' =>  'datetime:d/m/Y',
        'date_hired' =>  'datetime:d/m/Y',
    ];
    protected $fillable = [
        'id', 'user_id', 'first_name', 'last_name', 'gender', 'pronouns', 'indigenous',
        'personal_phone', 'personal_email', 'work_phone', 'work_email', 'job_title', 'position_id',
        'status', 'address', 'abn', 'tax_number', 'dob',
        'date_hired', 'location_id', 'division_id', 'employment_id', 'manager_id'
    ];

    protected $appends = array('image', 'cover');

    public function medical()
    {
        return $this->hasOne(MedicalHistory::class);
    }

    public function emergency_contact()
    {
        return $this->hasOne(EmergencyContact::class);
    }

    public function employee_position()
    {
        return $this->hasOne(Position::class);
    }

    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id', 'id');
    }    

    public function address()
    {
        return $this->hasMany(address::class);
    }

    public function bank_info()
    {
        return $this->hasMany(BankInformation::class)->orderBy('primary', 'Desc');
    }

    public function banks()
    {
        return $this->hasMany(Bank::class)->orderBy('primary', 'desc');
    }

    public function profile_image()
    {
        return $this->hasOne(Attachment::class, "source_id", "id")->where("name", "profile_image");
    }

    public function cover_image()
    {
        return $this->hasOne(Attachment::class, "source_id", "id")->where("name", "cover_image");
    }

    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->profile_image ? url('storage/profile_image/' . $this->profile_image->file_name) : null
        );
    }

    protected function cover(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->cover_image ?  url('storage/cover_image/' . $this->cover_image->file_name) : null
        );
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
        return $this->belongsTo(\App\Models\Employee::class);
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}

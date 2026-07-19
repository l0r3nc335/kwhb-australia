<?php

namespace App\Core\v1\Contract\Models;

use App\Core\v1\Employee\Models\Employee;
use App\Core\v1\Employment\Models\Employment;
use App\Core\v1\Location\Models\Location;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    protected $guarded = [];

    protected $table = 'contracts';

    protected $fillable = [
        'type',
        'title',
        'content',
        'template',
        'status',
        'employee_signature',
        'ceo_signature',
        'custom_data',
        'model_value',
        'owner_employee_id',
        'owner_user_id',
        'created_by_user_id',
        'assignee_user_id',
        'expiry_date',
        'location_id',
        'employment_type_id',
        'employee_signature_date',
        'ceo_signature_date',
    ];

    protected $casts = [
        'custom_data' => 'array',
        'model_value' => 'array',
        'owner_employee_id' => 'integer',
        'owner_user_id' => 'integer',
        'created_by_user_id' => 'integer',
        'assignee_user_id' => 'integer',
        'expiry_date' => 'date',
        'location_id' => 'integer',
        'employment_type_id' => 'integer',
        'employee_signature_date' => 'date',
        'ceo_signature_date' => 'date',
    ];

    use HasFactory;

    const DRAFT = 1;
    //const PENDING_0 = 2;
    //const PENDING_50 = 3;
    const IN_PROGRESS = 3;
    const COMPLETED = 4;
    const EXPIRED = 5;
    const REJECTED = 6;
    const ARCHIVE = 7;

    public static function statuses(): array
    {
        return [
            self::DRAFT => 'Draft',
            //self::PENDING_0 => 'Pending 0',
            //self::PENDING_50 => 'Pending 50',
            self::IN_PROGRESS => 'In-Progress',
            self::COMPLETED => 'Completed',
            self::EXPIRED => 'Expired',
            self::REJECTED => 'Rejected',
            self::ARCHIVE => 'Archive',
        ];
    }

    public function owner_employee()
    {
        return $this->hasOne(Employee::class, 'id', 'owner_employee_id');
    }

    public function created_by()
    {
        return $this->hasOne(\App\Core\v1\User\Models\User::class, 'id', 'created_by_user_id');
    }

    public function assignee_user()
    {
        return $this->hasOne(\App\Core\v1\User\Models\User::class, 'id', 'assignee_user_id');
    }

    public function location()
    {
        return $this->hasOne(Location::class, 'id', 'location_id');
    }

    public function employment_type()
    {
        return $this->hasOne(Employment::class, 'id', 'employment_type_id');
    }
}

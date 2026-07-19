<?php

namespace App\Core\v1\Employment\Models;

use App\Core\v1\Employee\Models\Employee;
use App\Core\v1\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Core\v1\Employment\Models\EmploymentTypeHistory;
use Carbon\Carbon;


class Employment extends Model
{
    use HasFactory;

    protected $table = 'employment_types';

    protected $appends = ['user_count', 'sample_users'];

    public function employees()
    {
        return $this->hasMany(Employee::class, 'employment_id');
    }

    public function users()
    {
        $userIds = $this->employees()->pluck('user_id')->toArray();

        return User::query()->with('employee')->whereIn('id', $userIds)->get();
    }

    public function getSampleUsersAttribute()
    {
        return $this->users()->take(5);
    }

    public function getUserCountAttribute()
    {
        return count($this->users());
    }

    public function histories()
    {
        return $this->hasMany(EmploymentTypeHistory::class, 'employment_type_id');
    }

    public function getIsActiveAttribute($value)
    {
        if ($this->valid_from) {
            // Parse valid_from in the correct timezone if it's stored as UTC or in a different tz
            $validFrom = Carbon::parse($this->valid_from, 'Australia/Sydney');
            $nowSydney = Carbon::now('Australia/Sydney');
            if ($validFrom->greaterThan($nowSydney)) {
                return 0;
            }
        }
        return $value;
    }

}

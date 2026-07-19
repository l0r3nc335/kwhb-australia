<?php

namespace App\Core\v1\JobTitle\Models;

use App\Core\v1\Employee\Models\Employee;
use App\Core\v1\User\Models\User;
use App\Core\v1\JobTitle\Models\JobTitleHistory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Core\v1\Division\Models\Division;

class JobTitle extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'division_id',
        'classification',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $appends = ['user_count', 'sample_users'];

    // Employees relationship (assuming employees have a job_title_id)
    public function employees()
    {
        return $this->hasMany(Employee::class, 'position_id');
    }

    // Users relationship through employees
    public function users()
    {
        $userIds = $this->employees()->pluck('user_id')->toArray();

        return User::query()->with('employee')->whereIn('id', $userIds)->get();
    }

    // Custom attribute: first 5 users
    public function getSampleUsersAttribute()
    {
        return $this->users()->take(5);
    }

    // Custom attribute: total user count
    public function getUserCountAttribute()
    {
        return count($this->users());
    }

    // ✅ Job Title History Relationship
    public function histories()
    {
        return $this->hasMany(JobTitleHistory::class, 'job_title_id');
    }

    public function division()
    {
        return $this->belongsTo(Division::class, 'division_id');
    }    
}

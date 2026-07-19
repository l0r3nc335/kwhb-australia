<?php

namespace App\Core\v1\Division\Models;

use App\Core\v1\Employee\Models\Employee;
use App\Core\v1\User\Models\User;
use App\Core\v1\Division\Models\DivisionHistory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Core\v1\Location\Models\Location;

class Division extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'is_active', // include if your table has this column
    ];

    protected $appends = ['user_count', 'sample_users'];

    // Employees relationship
    public function employees()
    {
        return $this->hasMany(Employee::class, 'division_id');
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

    // ✅ Division History Relationship
    public function histories()
    {
        return $this->hasMany(DivisionHistory::class, 'division_id');
    }

    public function positions()
    {
        return $this->belongsToMany(Position::class, 'position_divisions', 'division_id', 'position_id')
                    ->withTimestamps();
    }    

    public function locations()
    {
        return $this->belongsToMany(
            Location::class,
            'location_divisions',
            'division_id',
            'location_id'
        );
    }    
}

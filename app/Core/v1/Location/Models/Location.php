<?php

namespace App\Core\v1\Location\Models;

use App\Core\v1\Employee\Models\Employee;
use App\Core\v1\User\Models\User;
use App\Core\v1\Location\Models\LocationHistory;
use App\Core\v1\Division\Models\Division; // <--- Add this
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Carbon\Carbon;


class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'is_active', // include if your table has this column
        'division_id', // <--- Add this to allow mass-assignment
        'valid_from',  // <--- Add if you want to fill this column as well
    ];

    protected $appends = ['user_count', 'sample_users'];

    // Division relationship
    public function division()
    {
        return $this->belongsTo(Division::class, 'division_id');
    }

    public function divisions()
    {
        return $this->belongsToMany(
            Division::class,
            'location_divisions',
            'location_id',
            'division_id'
        );
    }

    // Employees relationship
    public function employees()
    {
        return $this->hasMany(Employee::class, 'location_id');
    }

    protected $casts = [
        'valid_from' => 'datetime',
        'is_active'  => 'boolean',
    ];

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

    // Location History Relationship
    public function histories()
    {
        return $this->hasMany(LocationHistory::class, 'location_id');
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

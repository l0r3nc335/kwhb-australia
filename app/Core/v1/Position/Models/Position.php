<?php

namespace App\Core\v1\Position\Models;

use App\Core\v1\Employee\Models\Employee;
use App\Core\v1\PayClassification\Models\PayClassification;
use App\Core\v1\User\Models\User;
use App\Core\v1\Position\Models\PositionHistory;
use App\Models\Vaccination;
use App\Models\Compliance;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Core\v1\Division\Models\Division;
use Carbon\Carbon;


class Position extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'division_id',
        'classification',
        'pay_classification_id',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $appends = ['user_count', 'sample_users'];

    /**
     * Employees relationship (employees have a position_id)
     */
    public function employees()
    {
        return $this->hasMany(Employee::class, 'position_id');
    }

    public function divisions()
    {
        return $this->belongsToMany(Division::class, 'position_divisions', 'position_id', 'division_id')
                    ->withTimestamps();
    }

    /**
     * Boot method for the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($position) {
            if ($position->start_date && Carbon::parse($position->start_date)->gt(Carbon::today())) {
                $position->is_active = 0;
            }
        });
    }    

    /**
     * Users relationship through employees
     */
    public function users()
    {
        return User::query()
            ->with('employee')
            ->whereIn(
                'id',
                $this->employees()->pluck('user_id')->toArray()
            )
            ->get();
    }

    /**
     * Custom attribute: first 5 users
     */
    public function getSampleUsersAttribute()
    {
        return $this->users()->take(5);
    }

    /**
     * Custom attribute: total user count
     */
    public function getUserCountAttribute()
    {
        return $this->users()->count();
    }

    /**
     * Position History Relationship
     */
    public function histories()
    {
        return $this->hasMany(PositionHistory::class, 'position_id');
    }

    /**
     * Division relationship
     */
    public function division()
    {
        return $this->belongsTo(Division::class, 'division_id');
    }

    /**
     * Vaccinations many-to-many relationship
     */
    public function vaccinations()
    {
        return $this->belongsToMany(Vaccination::class, 'position_vaccination', 'position_id', 'vaccination_id')
                    ->withTimestamps();
    }


    /**
     * Compliances many-to-many relationship
     */
    public function compliances()
    {
        return $this->belongsToMany(
            Compliance::class,
            'position_compliance',
            'position_id',
            'compliance_id'
        )
        ->withTimestamps();
    }    


    public function getIsActiveAttribute($value)
    {
        // Use Sydney timezone for all comparisons
        $now = \Carbon\Carbon::today('Australia/Sydney');

        // If no start_date, just return what's in the DB
        if (!$this->start_date) {
            return $value;
        }

        // Parse start_date as Sydney time
        $start = \Carbon\Carbon::parse($this->start_date, 'Australia/Sydney');

        // If start_date is in the future, not active
        if ($start->gt($now)) {
            return 0;
        }

        // If end_date is set, today must be <= end_date
        if ($this->end_date) {
            $end = \Carbon\Carbon::parse($this->end_date, 'Australia/Sydney');
            if ($now->gt($end)) {
                return 0;
            }
        }

        // If none of the above, return DB value
        return $value;
    }

    public function payClassification()
    {
        return $this->belongsTo(
            \App\Core\v1\PayClassification\Models\PayClassification::class,
            'pay_classification_id'
        );
    }    


}

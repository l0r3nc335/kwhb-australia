<?php

namespace App\Core\v1\PayClassification\Models;

use App\Core\v1\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Carbon\Carbon;


class PayClassification extends Model
{
    use HasFactory;

    protected $table = 'pay_classification';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'classification',
        'description',
        'annual_salary',
        'ot_weekday_sat_first_3',
        'ot_weekday_sat_after_3',
        'ot_sun_ph_all',
        'ph_time_off_ordinary_hours',
        'ordinary_hours',
        'casual_ot_weekday_sat_first_3',
        'casual_ot_weekday_sat_after_3',
        'casual_ot_sun_ph_all',
        'casual_ordinary_hours',
        'status',
    ];

    protected $casts = [
        'annual_salary'                  => 'string',
        'ot_weekday_sat_first_3'         => 'string',
        'ot_weekday_sat_after_3'         => 'string',
        'ot_sun_ph_all'                  => 'string',
        'ph_time_off_ordinary_hours'     => 'string',
        'ordinary_hours'                 => 'string',
        'casual_ot_weekday_sat_first_3'  => 'string',
        'casual_ot_weekday_sat_after_3'  => 'string',
        'casual_ot_sun_ph_all'           => 'string',
        'casual_ordinary_hours'          => 'string',
        'status'                         => 'string',
    ];
    /**
     * Get human-readable status.
     */
    public function getStatusTextAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE   => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
            default               => 'Unknown',
        };
    }

    /**
     * History entries for this pay classification.
     */
    public function histories()
    {
        return $this->hasMany(PayClassificationHistory::class, 'pay_classification_id');
    }

    /**
     * Logs history when creating or updating.
     */
    protected static function booted()
    {
        static::created(function ($pc) {
            $pc->histories()->create([
                'user_id' => auth()->id(),
                'action'  => 'create',
                'activity'=> 'Created pay classification',
            ]);
        });

        // static::updated(function ($pc) {
        //     $pc->histories()->create([
        //         'user_id' => auth()->id(),
        //         'action'  => 'update',
        //         'activity'=> 'Updated pay classification',
        //     ]);
        // });
    }


    public function getIsActiveAttribute($value)
    {
        $nowSydney = Carbon::now('Australia/Sydney');

        // Check if valid_from is set and in the future, deactivate
        if ($this->valid_from && Carbon::parse($this->valid_from, 'Australia/Sydney')->greaterThan($nowSydney)) {
            return 0;
        }

        // If valid_to is set and expired, deactivate
        if ($this->valid_to && Carbon::parse($this->valid_to, 'Australia/Sydney')->lessThan($nowSydney)) {
            return 0;
        }

        // Otherwise, return the DB value
        return $value;
    }    
}

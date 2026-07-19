<?php

namespace App\Core\v1\PayRange\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Core\v1\PayClassification\Models\PayClassification;
use App\Core\v1\PayRange\Models\PayRangeHistory;

class PayRange extends Model
{
    use HasFactory;

    protected $table = 'pay_ranges';

    public const STATUS_ACTIVE   = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_PENDING  = 'pending';
    public const STATUS_EXPIRED  = 'expired';

    protected $fillable = [
        'classification',
        'start_range',
        'end_range',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'start_range'       => 'string',
        'end_range'         => 'string',
        'start_date'        => 'date',
        'end_date'          => 'date',
        'status'            => 'string',
        'classification' => 'string',
    ];

    /**
     * Get human-readable status.
     */
    public function getStatusTextAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE   => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
            self::STATUS_PENDING  => 'Pending',
            self::STATUS_EXPIRED  => 'Expired',
            default               => 'Unknown',
        };
    }

    /**
     * The parent pay classification.
     */
    // public function classification()
    // {
    //     return $this->belongsTo(PayClassification::class, 'classification_id');
    // }

    /**
     * History entries for this pay range.
     */
    public function histories()
    {
        return $this->hasMany(PayRangeHistory::class, 'pay_range_id');
    }

    /**
     * Logs history when creating.
     */
    protected static function booted()
    {
        static::created(function ($range) {
            $range->histories()->create([
                'user_id'  => auth()->id(),
                'action'   => 'create',
                'activity' => 'Created pay range',
            ]);
        });

        // Uncomment to log updates
        // static::updated(function ($range) {
        //     $range->histories()->create([
        //         'user_id'  => auth()->id(),
        //         'action'   => 'update',
        //         'activity' => 'Updated pay range',
        //     ]);
        // });
    }
}

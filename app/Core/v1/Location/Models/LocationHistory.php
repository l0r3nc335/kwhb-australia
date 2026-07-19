<?php

namespace App\Core\v1\Location\Models;

use App\Core\v1\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LocationHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_id',
        'user_id',
        'action',
        'activity',
    ];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

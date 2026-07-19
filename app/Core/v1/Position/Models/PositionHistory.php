<?php

namespace App\Core\v1\Position\Models;

use App\Core\v1\Position\Models\Position;
use App\Core\v1\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PositionHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'position_id',
        'user_id',
        'action',
        'activity',
    ];

    /**
     * The position that this history entry belongs to.
     */
    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id');
    }

    /**
     * The user who performed the action.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
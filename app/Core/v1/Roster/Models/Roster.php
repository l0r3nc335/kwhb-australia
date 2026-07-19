<?php

namespace App\Core\v1\Roster\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Roster extends Model
{
    use HasFactory;

    protected $with = ['users', 'shifts'];

    const SHIFT_TYPE_AGENCY_RAN = 1;
    const SHIFT_TYPE_PERMANENT_RAN = 2;
    const SHIFT_TYPE_FIRST_RESPONDER = 3;
    const SHIFT_TYPE_SECOND_RESPONDER = 4;
    const SHIFT_TYPE_GP = 5;
    const SHIFT_TYPE_PROGRAM_STAFF = 6;
    const SHIFT_TYPE_PHSCC = 7;
    const SHIFT_TYPE_ON_CALL = 8;
    const SHIFT_TYPE_LEAVE = 9;

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function shifts()
    {
        return $this->hasMany(RosterShift::class);
    }

    public function templates()
    {
        return $this->hasMany(RosterTemplate::class);
    }
}

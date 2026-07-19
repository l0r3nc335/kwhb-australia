<?php

namespace App\Core\v1\Allowance\Models;

use App\Core\v1\Employee\Models\Employee;
use App\Core\v1\User\Models\User;
use App\Core\v1\Position\Models\Position;
use App\Core\v1\Allowance\Models\AllowanceHistory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Allowance extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'position_id',
        'positions',
        'value',
        'frequency',
        'start_date',
        'end_date',
        'status',
    ];

    protected $appends = ['user_count', 'sample_users'];

    protected static function booted()
    {
        // static::saving(function ($model) {
        //     $now = Carbon::now();

        //     $start = $model->start_date ? Carbon::parse($model->start_date) : null;
        //     $end = $model->end_date ? Carbon::parse($model->end_date) : null;

        //     if ($start && $start->isFuture()) {
        //         $model->status = 'archive';
        //     } elseif ($end && $end->isPast()) {
        //         $model->status = 'archive';
        //     } else {
        //         $model->status = 'active';
        //     }
        // });
    }

    // 🔗 Relationship: linked position
    // TODO: DEPRECATE THIS AS THE POSITION IS NOW MULTI SELECT IN VUE, BUT STILL USING THE POSITIONS COMMA SEPARATED VAL
    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id');
    }

    // 🔗 Relationship: employees who have this allowance via position
    public function employees()
    {
        return $this->position ? $this->position->employees() : collect();
    }

    // 🔗 Relationship: users via employees
    public function users()
    {
        $userIds = $this->employees()->pluck('user_id')->toArray();

        return User::query()->with('employee')->whereIn('id', $userIds)->get();
    }

    // 🧮 Computed: sample users (first 5)
    public function getSampleUsersAttribute()
    {
        return $this->users()->take(5);
    }

    // 🧮 Computed: total user count
    public function getUserCountAttribute()
    {
        return count($this->users());
    }

    // 📚 Relationship: allowance histories
    public function histories()
    {
        return $this->hasMany(AllowanceHistory::class, 'allowance_id');
    }
}

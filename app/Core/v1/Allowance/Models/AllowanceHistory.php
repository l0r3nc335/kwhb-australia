<?php

namespace App\Core\v1\Allowance\Models;

use App\Core\v1\User\Models\User;
use App\Core\v1\Allowance\Models\Allowance;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AllowanceHistory extends Model
{
    use HasFactory;

    protected $table = 'allowances_histories';

    protected $fillable = [
        'allowance_id',
        'user_id',
        'action',
        'activity',
    ];

    public function allowance()
    {
        return $this->belongsTo(Allowance::class, 'allowance_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

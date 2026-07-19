<?php

namespace App\Core\v1\PayRange\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Core\v1\User\Models\User;
use App\Core\v1\PayRange\Models\PayRange;

class PayRangeHistory extends Model
{
    use HasFactory;

    protected $table = 'pay_range_history';

    protected $fillable = [
        'pay_range_id',
        'user_id',
        'action',
        'activity',
    ];

    public function payRange()
    {
        return $this->belongsTo(PayRange::class, 'pay_range_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

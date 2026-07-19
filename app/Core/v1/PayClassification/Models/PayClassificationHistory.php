<?php

namespace App\Core\v1\PayClassification\Models;

use App\Core\v1\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PayClassificationHistory extends Model
{
    use HasFactory;

    protected $table = 'pay_classification_history';

    protected $fillable = [
        'pay_classification_id',
        'user_id',
        'action',
        'activity',
    ];

    public function payClassification()
    {
        return $this->belongsTo(PayClassification::class, 'pay_classification_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

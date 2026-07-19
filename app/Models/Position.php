<?php

namespace App\Models;

use App\Core\v1\PayClassification\Models\PayClassification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $fillable = [
        'name'
    ];
    use HasFactory;

    public function payClassificationsData()
    {
        return $this->hasone(PayClassification::class, 'id', 'pay_classification_id');
    }

}

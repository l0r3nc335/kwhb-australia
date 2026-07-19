<?php

namespace App\Core\v1\Division\Models;

use App\Core\v1\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DivisionHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'division_id',
        'user_id',
        'action',
        'activity',
    ];

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Core\v1\Employment\Models;

use App\Core\v1\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmploymentTypeHistory extends Model
{
    use HasFactory;

    // EXPLICITLY set the table name
    protected $table = 'employment_types_histories';

    protected $fillable = [
        'employment_type_id',
        'user_id',
        'action',
        'activity',
    ];

    public function employmentType()
    {
        return $this->belongsTo(Employment::class, 'employment_type_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function histories()
    {
        return $this->hasMany(EmploymentTypeHistory::class, 'employment_type_id');
    }    
}

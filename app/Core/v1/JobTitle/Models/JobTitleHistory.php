<?php

namespace App\Core\v1\JobTitle\Models;

use App\Core\v1\User\Models\User;
use App\Core\v1\JobTitle\Models\JobTitle;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JobTitleHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_title_id',
        'user_id',
        'action',
        'activity',
    ];

    public function jobTitle()
    {
        return $this->belongsTo(JobTitle::class, 'job_title_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

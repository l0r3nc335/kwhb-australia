<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employment extends Model
{
    protected $fillable = [
        'name'
    ];
    use HasFactory;

    protected $table = 'employment_types';
}

<?php

namespace App\Core\v1\Template\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Template extends Model
{
    protected $guarded = [];

    protected $table = 'templates';

    use HasFactory, SoftDeletes;

}

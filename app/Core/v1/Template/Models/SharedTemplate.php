<?php

namespace App\Core\v1\Template\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SharedTemplate extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $table = 'shared_templates';
}

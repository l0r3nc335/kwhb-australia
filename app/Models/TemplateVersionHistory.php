<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemplateVersionHistory extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $table = 'template_version_history';
}

<?php

namespace App\Core\v1\Template\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemplateField extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $table = 'template_fields';
}

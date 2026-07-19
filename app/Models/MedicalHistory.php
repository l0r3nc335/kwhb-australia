<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalHistory extends Model
{
    protected $fillable = [
        'employee_id', 'condition', 'allergies', 'diagnosis_date', 'medication'
    ];
    use HasFactory;
}

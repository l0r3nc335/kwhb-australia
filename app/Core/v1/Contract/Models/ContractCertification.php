<?php

namespace App\Core\v1\Contract\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractCertification extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $table = 'contract_certifications';
}

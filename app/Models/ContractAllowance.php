<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractAllowance extends Model
{
    use HasFactory;
    
    protected $guarded = [];

    protected $table = 'contract_allowances';
}

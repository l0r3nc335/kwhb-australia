<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractVersionHistory extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $table = 'contract_version_history';
}

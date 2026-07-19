<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankInformation extends Model
{
    protected $fillable = [
        'employee_id', 'account_name', 'bsb', 'account', 'pay_split', 'pay_split_value',
        'primary', 'reimbursement'
    ];
    protected $table = "bank_informations";
    use HasFactory;
}

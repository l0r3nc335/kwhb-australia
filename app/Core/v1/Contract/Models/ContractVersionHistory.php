<?php

namespace App\Core\v1\Contract\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractVersionHistory extends Model
{
    use HasFactory;

    //protected $guarded = [];

    protected $table = 'contract_version_history';
    
    public $timestamps = false;

    protected $fillable = ['contract_id', 'action', 'activity', 'status', 'user_id'];

    public function user_logs()
    {
        return $this->hasOne(\App\Core\v1\User\Models\User::class, 'id', 'user_id');
    }
}

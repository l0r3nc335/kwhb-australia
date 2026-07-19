<?php

namespace App\Core\v1\Bank\Repositories;

use App\Core\v1\Bank\Models\Bank;
use App\Repositories\ModelRepository;
use Illuminate\Database\Eloquent\Model;

class BankRepository extends ModelRepository
{
    public function model(): Model
    {
        return new Bank;
    }
}

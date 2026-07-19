<?php

namespace App\Core\v1\Bank\Http\Controllers;

use App\Core\v1\Bank\Repositories\BankRepository;
use App\Http\Controllers\Controller;
use App\Repositories\ModelRepository;
use Illuminate\Http\Request;

class BankController extends Controller
{
    public function __construct(protected ModelRepository $repository = new BankRepository())
    {
        //
    }
}

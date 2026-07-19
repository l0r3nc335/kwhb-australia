<?php

namespace App\Core\v1\Year\Http\Controllers;

use App\Core\v1\Year\Repositories\YearRepository;
use App\Http\Controllers\Controller;
use App\Repositories\ModelRepository;

class YearController extends Controller
{
    protected ModelRepository $repository;

    public function __construct(ModelRepository $repository = new YearRepository())
    {
        $this->repository = $repository;
    }

    /**
     * Display all years (desc).
     */
    public function index()
    {
        return response()->json(
            $this->repository->allDesc()
        );
    }

    /**
     * Store a new year: if none exists, add current; else, add latest+1.
     */
    public function store()
    {
        $year = $this->repository->store();

        return response()->json([
            'message' => 'Year created.',
            'data'    => $year,
            'success' => true,
        ]);
    }
}

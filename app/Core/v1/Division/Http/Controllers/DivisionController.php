<?php

namespace App\Core\v1\Division\Http\Controllers;

use App\Core\v1\Division\Repositories\DivisionRepository;
use App\Http\Controllers\Controller;
use App\Repositories\ModelRepository;
use Illuminate\Http\Request;

class DivisionController extends Controller
{
    public function __construct(protected ModelRepository $repository = new DivisionRepository())
    {
        //
    }

    public function index()
    {
        return response()->json(
            $this->repository->orderBy('name', 'asc')->all()
        );
    }

    public function active()
    {
        return response()->json(
            $this->repository
                ->where('is_active', 1)
                ->orderBy('name', 'asc')
                ->allActive()
        );
    }

    public function getPaginatedDivision(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $search = $request->input('search');
        $status = $request->input('is_active');
        $divisionId = $request->input('division_id');
        $sortBy = $request->input('sort_by', 'name');
        $sortDirection = $request->input('sort_direction', 'asc');
    
        $results = $this->repository->filterAndPaginate(
            $perPage,
            $page,
            $search,
            $status,
            $divisionId,
            $sortBy,
            $sortDirection
        );
    
        return response()->json([
            'data' => $results->items(),
            'total' => $results->total(),
            'per_page' => $results->perPage(),
            'current_page' => $results->currentPage(),
        ]);
    }
    

    public function getLineManagers($id)
    {
        return response()->json([
            'message' => '',
            'data' =>  $this->repository->find($id, true)->lineManagers(),
            'tags' => 0,
            'error' => false,
            'errorCodes' => []
        ]);
    }

    public function store(Request $request)
    {
        $name = $request->get('name');
        $this->repository->store($name);

        return response()->json([
            'message' => 'Division created.'
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $isActive = $request->get('is_active');
    
        $division = $this->repository->updateStatus($id, $isActive);
    
        return response()->json([
            'message' => 'Division status updated.',
            'data' => $division
        ]);
    }

    public function getHistory($id)
    {
        $division = $this->repository->getHistory($id);

        return response()->json([
            'message' => 'Fetched history.',
            'data' => $division
        ]);
    }    
    
}

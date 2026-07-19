<?php

namespace App\Core\v1\Location\Http\Controllers;

use App\Core\v1\Location\Repositories\LocationRepository;
use App\Http\Controllers\Controller;
use App\Repositories\ModelRepository;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function __construct(protected ModelRepository $repository = new LocationRepository())
    {
        //
    }

    public function index()
    {
        return response()->json(
            $this->repository->orderBy('name', 'asc')->all()
        );
    }

    public function getPaginatedLocation(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $search = $request->input('search');
        $status = $request->input('is_active');
        $locationId = $request->input('location_id');
        $divisionId = $request->input('division_id');
        $sortBy = $request->input('sort_by', 'name');
        $sortDirection = $request->input('sort_direction', 'asc');
    
        $results = $this->repository->filterAndPaginate(
            $perPage,
            $page,
            $search,
            $status,
            $locationId,
            $divisionId, // <-- PASS HERE
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
    


    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'valid_from'     => 'nullable|date',
            'division_ids'   => 'nullable|array',
            'division_ids.*' => 'integer|exists:divisions,id',
            'is_division_all'  => 'required|boolean'
        ]);

        $record = $this->repository->store($validated);

        return response()->json([
            'message' => 'Location created.',
            'success' => true,
            'data'    => $record,
        ], 201);
    }
    
    public function updateStatus(Request $request, $id)
    {
        $isActive = $request->get('is_active');

        $location = $this->repository->updateStatus($id, $isActive);

        return response()->json([
            'message' => 'Location status updated.',
            'data' => $location
        ]);
    }

    public function getHistory($id)
    {
        $location = $this->repository->getHistory($id);

        return response()->json([
            'message' => 'Fetched history.',
            'data' => $location
        ]);
    }    

}

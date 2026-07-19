<?php

namespace App\Core\v1\Position\Http\Controllers;


use App\Core\v1\Position\Models\Position;
use App\Core\v1\Position\Repositories\PositionRepository;
use App\Http\Controllers\Controller;
use App\Repositories\ModelRepository;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    public function __construct(protected ModelRepository $repository = new PositionRepository())
    {
        // Under the hood, queries now run against the "positions" table
    }

    public function index()
    {
        //dd($this->repository->orderBy('name', 'asc')->all());

        return response()->json(
            $this->repository->orderBy('name', 'asc')->all()
        );
    }

    public function getByNames(Request $request)
    {
        $jobTitleNames = $request->all();
        /*dd($this->repository
            ->whereInMyArray('name', $jobTitleNames)
            ->all());
        */
        return response()->json(
            $this->repository
                ->whereInMyArray('name', $jobTitleNames)
                ->orderBy('name', 'asc')->all()
        );
    }

    public function getActiveJobTitle(Request $request)
    {
        $perPage       = (int) $request->input('per_page', 10);
        $page          = (int) $request->input('page', 1);
        $search        = $request->input('search', null);
        $status        = 1;
        $jobTitleId    = $request->input('position_id', null);
        $divisionId    = $request->input('division_id', null);
        $sortBy        = $request->input('sort_by', 'name');
        $sortDirection = $request->input('sort_direction', 'asc');

        $results = $this->repository->filterAndPaginate(
            perPage:       $perPage,
            page:          $page,
            search:        $search,
            is_active:     $status,
            positionId:    $jobTitleId,
            sortBy:        $sortBy,
            sortDirection: $sortDirection,
            divisionId:    $divisionId
        );

        return response()->json([
            'data'         => $results->items(),
            'total'        => $results->total(),
            'per_page'     => $results->perPage(),
            'current_page' => $results->currentPage(),
            // echo back what was actually applied:
            'sort_by'      => $results->sortBy ?? $sortBy,
            'sort_direction'=> $results->sortDirection ?? $sortDirection,
        ]);
    }

    public function getPaginatedJobTitleForDivision(Request $request)
    {
        $query = Position::query()
            ->where('is_active', 1)
            ->where(function ($q) use ($request) {
                $q->whereHas('divisions', function ($q2) use ($request) {
                    $q2->whereIn('divisions.name', $request->all()); // Make sure the values are division names
                })->orWhereDoesntHave('divisions'); // Use this instead of orWhereNull for belongsToMany
            });

        $results = $query->get();

        return response()->json([
            'data' => $results
        ]);
    }

    public function getPaginatedJobTitle(Request $request)
    {
        $perPage       = (int) $request->input('per_page', 10);
        $page          = (int) $request->input('page', 1);
        $search        = $request->input('search', null);
        $status        = $request->input('is_active', null);
        $jobTitleId    = $request->input('position_id', null);
        $divisionId    = $request->input('division_id', null);  // Single division_id here
        $sortBy        = $request->input('sort_by', 'name');
        $sortDirection = $request->input('sort_direction', 'asc');

        $results = $this->repository->filterAndPaginate(
            perPage:       $perPage,
            page:          $page,
            search:        $search,
            is_active:     $status,
            positionId:    $jobTitleId,
            sortBy:        $sortBy,
            sortDirection: $sortDirection,
            divisionId:    $divisionId  // Single division_id
        );

        return response()->json([
            'data'         => $results->items(),
            'total'        => $results->total(),
            'per_page'     => $results->perPage(),
            'current_page' => $results->currentPage(),
            'sort_by'      => $results->sortBy ?? $sortBy,
            'sort_direction'=> $results->sortDirection ?? $sortDirection,
        ]);
    }



    public function getLineManagers($id)
    {
        return response()->json([
            'message'    => '',
            'data'       => $this->repository->find($id, true)->lineManagers(),
            'tags'       => 0,
            'error'      => false,
            'errorCodes' => []
        ]);
    }

    public function store(Request $request)
    {
        $name            = $request->get('name');
        $division_ids    = $request->get('division_ids', []);  // Default to an empty array if not provided
        $classification  = $request->get('classification');
        $start_date      = $request->get('start_date');
        $end_date        = $request->get('end_date');
        $vaccinationIds  = $request->get('vaccinations', []); // existing
        $complianceIds   = $request->get('compliances', []);  // new
        $is_active       = $request->get('is_active', 1);

        // Optional check to handle when division_ids is empty, no need for validation
        // No error if division_ids is empty, pass it as an empty array
        if (empty($division_ids)) {
            $division_ids = [];  // Ensure it's an empty array if nothing is passed
        }

        $position = $this->repository->store(
            $name,
            $division_ids,  // Pass the array of divisions, which can be empty
            $classification,
            $start_date,
            $end_date,
            $vaccinationIds,
            $complianceIds,    
            $is_active
        );

        if ($position === null) {
            return response()->json([
                'message' => 'Position already exists.',
                'success' => false
            ], 200); // 200 if you want, or 409 for "conflict"
        }        

        return response()->json([
            'message' => 'Position created.',
            'data'    => $position,
            'success' => true
        ]);
    }




    public function updateStatus(Request $request, $id)
    {
        $isActive = $request->get('is_active');

        $jobTitle = $this->repository->updateStatus($id, $isActive);

        return response()->json([
            'message' => 'Job title status updated.',
            'data'    => $jobTitle
        ]);
    }

    public function getHistory($id)
    {
        $history = $this->repository->getHistory($id);

        return response()->json([
            'message' => 'Fetched history.',
            'data'    => $history
        ]);
    }
}

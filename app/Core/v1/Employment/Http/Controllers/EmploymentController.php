<?php

namespace App\Core\v1\Employment\Http\Controllers;

use App\Core\v1\Employment\Repositories\EmploymentRepository;
use App\Core\v1\Employment\Models\Employment;
use App\Http\Controllers\Controller;
use App\Repositories\ModelRepository;
use Illuminate\Http\Request;
use Carbon\Carbon;

class EmploymentController extends Controller
{
    public function __construct(protected ModelRepository $repository = new EmploymentRepository())
    {
        //
    }

    public function index()
    {
        return response()->json(
            $this->repository->orderBy('name', 'asc')->all()
        );
    }

    public function indexActive()
    {
        // Fetch all employments ordered by name (or whatever you want)
        $employments = Employment::orderBy('name', 'asc')->get();

        // Use the model accessor to filter active employments (accessor uses Australia/Sydney timezone)
        $active = $employments->filter(function ($employment) {
            return $employment->is_active == 1;
        })->values();

        return response()->json($active);
    }

    public function getPaginatedEmploymentType(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $search = $request->input('search');
        $status = $request->input('is_active');
        $employmentTypeId = $request->input('employment_type_id');
        $sortBy = $request->input('sort_by', 'name');
        $sortDirection = $request->input('sort_direction', 'asc');

        $results = $this->repository->filterAndPaginate(
            $perPage,
            $page,
            $search,
            $status,
            $employmentTypeId,
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
        $name         = $request->get('name');
        $description  = $request->get('description');
        $validFrom    = $request->get('valid_from');
        $hoursPerWeek = $request->get('hours_per_week');

        $result = $this->repository->store($name, $description, $validFrom, $hoursPerWeek);

        if (empty($result)) {
            return response()->json([
                'message' => 'Employment Type already exists.',
                'success' => false
            ], 200); // Or 409 for conflict
        }

        return response()->json([
            'message' => 'Employment Type Created',
            'success' => true
        ]);
    }



    public function updateStatus(Request $request, $id)
    {
        $isActive = $request->get('is_active');

        $employmentType = $this->repository->updateStatus($id, $isActive);

        return response()->json([
            'message' => 'Employment Type status updated',
            'data' => $employmentType
        ]);
    }

    public function getHistory($id)
    {
        $history = $this->repository->getHistory($id);

        return response()->json([
            'message' => 'Fetched history',
            'data' => $history
        ]);
    }
}

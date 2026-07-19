<?php

namespace App\Core\v1\JobTitle\Http\Controllers;

use App\Core\v1\JobTitle\Repositories\JobTitleRepository;
use App\Http\Controllers\Controller;
use App\Repositories\ModelRepository;
use Illuminate\Http\Request;

class JobTitleController extends Controller
{
    public function __construct(protected ModelRepository $repository = new JobTitleRepository())
    {
        //
    }

    public function index()
    {
        return response()->json(
            $this->repository->orderBy('name', 'asc')->all()
        );
    }

    public function getPaginatedJobTitle(Request $request)
    {

        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $search = $request->input('search');
        $status = $request->input('is_active');
        $jobTitleId = $request->input('job_title_id');
        $sortBy = $request->input('sort_by', 'name');
        $sortDirection = $request->input('sort_direction', 'asc');
        $divisionId = $request->input('division_id');
    
        $results = $this->repository->filterAndPaginate(
            $perPage,
            $page,
            $search,
            $status,
            $jobTitleId,
            $sortBy,
            $sortDirection,
            $divisionId
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
        $division_id = $request->get('division_id');
        $classification = $request->get('classification');
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        $is_active = $request->get('is_active', 1);

        $this->repository->store($name, $division_id, $classification, $start_date, $end_date, $is_active);

        return response()->json([
            'message' => 'Job title created.'
        ]);
    }


    public function updateStatus(Request $request, $id)
    {
        $isActive = $request->get('is_active');
    
        $jobTitle = $this->repository->updateStatus($id, $isActive);
    
        return response()->json([
            'message' => 'Job title status updated.',
            'data' => $jobTitle
        ]);
    }

    public function getHistory($id)
    {
        $history = $this->repository->getHistory($id);

        return response()->json([
            'message' => 'Fetched history.',
            'data' => $history
        ]);
    }    
}

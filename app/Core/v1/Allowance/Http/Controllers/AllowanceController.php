<?php

namespace App\Core\v1\Allowance\Http\Controllers;

use App\Core\v1\Allowance\Repositories\AllowanceRepository;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AllowanceController extends Controller
{
    public function __construct(protected AllowanceRepository $repository = new AllowanceRepository())
    {
        //
    }

    public function index()
    {
        return response()->json(
            $this->repository->orderBy('name', 'asc')->all()
        );
    }

    public function getActiveAllowances()
    {
        return response()->json(
            $this->repository
                ->whereV2('status', 'active')
                ->orderBy('name', 'asc')
                ->all()
        );
    }

    public function getAllowancePackage(Request $request)
    {
        $ids = explode(',', $request->idList);
        $ids = array_map('intval', $ids);

        return response()->json(
            $this->repository
                ->whereIn('id', $ids)
                ->orderBy('name', 'asc')
                ->all()
        );
    }

    public function getPaginatedAllowance(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $search = $request->input('search');
        $positionId = $request->input('position_id');
        $sortBy = $request->input('sort_by', 'name');
        $sortDirection = $request->input('sort_direction', 'asc');
         $status = $request->input('status'); 

        $results = $this->repository->filterAndPaginate(
            $perPage,
            $page,
            $search,
            $positionId,
            $status,
            $sortBy,
            $sortDirection
        );

        $positions = $this->repository->filterAndPaginateReturnPositions(
            $perPage,
            $page,
            $search,
            $positionId,
            $status,
            $sortBy,
            $sortDirection
        )->toArray()['data'];

        $uniqueDataPositions = [];
        foreach ($positions as $position) {
            if (is_string($position)) {
                $uniqueDataPositions = array_merge($uniqueDataPositions, explode(',', $position));
            } elseif (is_array($position)) {
                foreach ($position as $p) {
                    if (is_string($p)) {
                        $uniqueDataPositions = array_merge($uniqueDataPositions, explode(',', $p));
                    }
                }
            }
        }

        return response()->json([
            'data' => $results->items(),
            'total' => $results->total(),
            'per_page' => $results->perPage(),
            'current_page' => $results->currentPage(),
            'unique_positions' => array_unique($uniqueDataPositions)
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $status = $request->get('status');

        // Optionally, validate status value if you have a set of valid ones
        // $request->validate([
        //     'status' => 'required|in:active,archive,otherstatus' // add all valid statuses
        // ]);

        $allowance = $this->repository->updateStatus($id, $status);

        return response()->json([
            'message' => 'Allowance status updated.',
            'data' => $allowance,
            '$status' => $status,
            '$id'=> $id
        ]);
    }    

    public function store(Request $request)
    {
        $startDate = $request->get('start_date');
        $endDate   = $request->get('end_date');

        // Parse to Carbon for date comparison
        $today = \Carbon\Carbon::today();
        $start = $startDate ? \Carbon\Carbon::parse($startDate) : null;

        $status = $start->isAfter($today) ? 'pending' : 'active';

        /*if ($start && $start->isAfter($today) && !$endDate) {
            $status = 'archive';
        }*/

        $record = $this->repository->store(
            $request->get('name'),
            $request->get('description'),
            $request->get('position_id'),
            $request->get('value'),
            $request->get('frequency'),
            $startDate,
            $endDate,
            $status
        );

        return response()->json([
            'message' => 'Allowance created.',
            'data' => $record
        ]);
    }

    public function update(Request $request, $id)
    {
        $record = $this->repository->update($id, $request->all());

        return response()->json([
            'message' => 'Allowance updated.',
            'data' => $record
        ]);
    }

    public function update_statuses()
    {
        $record = $this->repository->update_statuses();

        return response()->json([
            'message' => 'Allowance updated.',
            'data' => $record
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

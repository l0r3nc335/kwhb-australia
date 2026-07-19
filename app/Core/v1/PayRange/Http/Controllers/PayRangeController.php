<?php

namespace App\Core\v1\PayRange\Http\Controllers;

use App\Core\v1\PayRange\Repositories\PayRangeRepository;
use App\Http\Controllers\Controller;
use App\Repositories\ModelRepository;
use Illuminate\Http\Request;

class PayRangeController extends Controller
{
    public function __construct(protected ModelRepository $repository = new PayRangeRepository())
    {
        //
    }

    public function index()
    {
        return response()->json(
            $this->repository
                ->orderBy('id', 'asc')
                ->all()
        );
    }

    public function indexActive()
    {
        $today = now()->format('Y-m-d');
        $results = $this->repository
            ->model()
            ->where(function($query) use ($today) {
                $query->whereNull('start_date')
                    ->orWhere('start_date', '<=', $today);
            })
            ->where(function($query) use ($today) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $today);
            })
            ->where('status', 'active')
            ->orderBy('id', 'asc')
            ->get();

        return response()->json($results);
    }

    public function store(Request $request)
    {
        $data = $request->only([
            'classification',      // <-- Changed
            'start_range',
            'end_range',
            'start_date',
            'end_date',
            'status'
        ]);

        $pr = $this->repository->store(
            $data['classification'],
            $data['start_range'],
            $data['end_range'],
            $data['start_date'] ?? null,
            $data['end_date'] ?? null,
            $data['status'] ?? 'active'
        );

        return response()->json([
            'message' => 'Pay range created.',
            'data'    => $pr,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->only([
            'classification',      // <-- Changed
            'start_range',
            'end_range',
            'start_date',
            'end_date',
            'status',
        ]);

        $pr = $this->repository->updateRange($id, $data);

        if (! $pr) {
            return response()->json(['message' => 'Not Found'], 404);
        }

        return response()->json([
            'message' => 'Pay range updated.',
            'data'    => $pr,
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $status = $request->input('status');
        $startDate = $request->input('start_date', null);
        $endDate = $request->input('end_date', null);

        $data = [
            'status' => $status,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];

        $updated = $this->repository->updateStatus($id, $data);

        if (! $updated) {
            return response()->json(['message' => 'Not Found'], 404);
        }

        return response()->json([
            'message' => 'Status updated.',
            'data'    => ['id' => $id, 'status' => $status],
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

    public function duplicate(Request $request, $id)
    {
        $new = $this->repository->duplicate($id);
        if (!$new) {
            return response()->json(['message' => 'Original range not found.'], 404);
        }

        return response()->json([
            'message' => 'Pay range duplicated.',
            'data' => $new,
        ], 201);
    }

    public function getPaginatedRanges(Request $request)
    {
        $perPage           = $request->input('per_page', 10);
        $page              = $request->input('page', 1);
        $classification    = $request->input('classification'); // <-- Changed
        $search    = $request->input('search'); // <-- Changed
        $status            = $request->input('status');
        $sortBy            = $request->input('sort_by', 'id');
        $sortDirection     = $request->input('sort_direction', 'asc');
        $minRange = $request->input('min_range');
        $maxRange = $request->input('max_range');
        $year = $request->input('year');

        $results = $this->repository->filterAndPaginate(
            $perPage,
            $page,
            $classification,
            $status,
            $sortBy,
            $sortDirection,
            $minRange,
            $maxRange,
            $year,
            $search
        );

        return response()->json([
            'data'         => $results->items(),
            'total'        => $results->total(),
            'per_page'     => $results->perPage(),
            'current_page' => $results->currentPage(),
        ]);
    }

    public function years()
    {
        $years = $this->repository->getAllYears();

        return response()->json([
            'years' => $years,
        ]);
    }

    public function storeCSV(Request $request)
    {
        $csv_data   = $request->input('csv_data', []);
        $status     = $request->input('status', 'active');
        $start_date = $request->input('start_date');
        $end_date   = $request->input('end_date');

        $results = [];

        foreach ($csv_data as $data) {
            // Check if pay range already exists for this classification and range
            $exists = \DB::table('pay_ranges')
                ->where('classification', $data['classification'] ?? '')
                ->where('start_range', $data['start_range'] ?? '')
                ->where('end_range', $data['end_range'] ?? '')
                ->where(function ($query) use ($data) {
                    $startDate = $data['start_date'] ?? null;
                    $endDate   = $data['end_date'] ?? null;
                    // Only block if there's an ACTUAL overlap
                    $query->where(function ($q) use ($startDate, $endDate) {
                        // If both start and end date are present in new row
                        if ($startDate && $endDate) {
                            // Overlap: (existing.start_date IS NULL OR existing.start_date <= $endDate)
                            //      AND (existing.end_date IS NULL OR existing.end_date >= $startDate)
                            $q->where(function ($q2) use ($startDate, $endDate) {
                                $q2->where(function ($q3) use ($startDate, $endDate) {
                                    $q3->where(function ($qq) use ($endDate) {
                                            $qq->whereNull('start_date')
                                                ->orWhere('start_date', '<=', $endDate);
                                        })
                                        ->where(function ($qq) use ($startDate) {
                                            $qq->whereNull('end_date')
                                                ->orWhere('end_date', '>=', $startDate);
                                        });
                                });
                            });
                        } else {
                            // If uploading a row without date limits, we treat it as colliding with any
                            $q->whereRaw('1=1');
                        }
                    });
                })
                ->exists();

            $data['status'] = $status ?: ($data['status'] ?? 'active');
            $data['start_date'] = $start_date ?: ($data['start_date'] ?? null);
            $data['end_date'] = $end_date ?: ($data['end_date'] ?? null);

            $pr = $this->repository->store(
                $data['classification'] ?? '',
                $data['start_range'] ?? '',
                $data['end_range'] ?? '',
                $data['start_date'] ?? null,
                $data['end_date'] ?? null,
                $data['status'] ?? 'active'
            );

            $results[] = $pr;
        }

        return response()->json([
            'message'  => 'Pay ranges created.',
            'data'     => $results,
            'csv_data' => $csv_data
        ], 201);
    }

}

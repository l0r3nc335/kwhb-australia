<?php

namespace App\Core\v1\PayClassification\Http\Controllers;

use App\Core\v1\PayClassification\Repositories\PayClassificationRepository;
use App\Http\Controllers\Controller;
use App\Repositories\ModelRepository;
use Illuminate\Http\Request;

class PayClassificationController extends Controller
{
    public function __construct(protected ModelRepository $repository = new PayClassificationRepository())
    {
        //
    }

    /**
     * List all pay classifications, ordered by classification code.
     */
    public function index()
    {
        return response()->json(
            $this->repository
                 ->orderBy('classification', 'asc')
                 ->all()
        );
    }

    public function indexActive()
    {
        $today = now()->format('Y-m-d');
        $currentYear = now()->year;

        $results = $this->repository
            ->model() // Make sure this returns the Eloquent Model, not a new instance!
            ->where(function($query) use ($today) {
                $query->whereNull('start_date')
                    ->orWhere('start_date', '<=', $today);
            })
            ->where(function($query) use ($today) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $today);
            })
            ->where('year', $currentYear)
            ->orderBy('classification', 'asc')
            ->get();

        return response()->json($results);
    }    

    public function duplicate(Request $request, $id)
    {
        $year = $request->input('year');
        if (!$year) {
            return response()->json(['message' => 'Year is required.'], 400);
        }

        $new = $this->repository->duplicate($id, $year);
        if (!$new) {
            return response()->json(['message' => 'Original classification not found.'], 404);
        }

        return response()->json([
            'message' => 'Classification duplicated.',
            'data' => $new,
        ], 201);
    }    


    public function storeCSV(Request $request)
    {
        $csv_data   = $request->input('csv_data', []);
        $year       = $request->input('year');
        $status     = $request->input('status', 'active');
        $start_date = $request->input('start_date'); // <--- Add this
        $end_date   = $request->input('end_date');   // <--- Add this

        $results = [];

        foreach ($csv_data as $data) {

            $exists = \DB::table('pay_classification')
                ->where('classification', $data['classification'])
                ->where('year', $year)
                ->exists();

            if ($exists) {
                return response()->json([
                    'message' => 'Classification already exists for this year',
                    'success' => false
                ], 201);
            }            

            // Overwrite year, status, start_date, end_date from POST/global if set
            $data['year'] = $year ?: ($data['year'] ?? '');
            $data['status'] = $status ?: ($data['status'] ?? PayClassificationRepository::STATUS_ACTIVE);
            $data['start_date'] = $start_date ?: ($data['start_date'] ?? null); // <--- Add this
            $data['end_date']   = $end_date   ?: ($data['end_date']   ?? null); // <--- Add this

            $pc = $this->repository->store(
                $data['classification'] ?? '',
                $data['description'] ?? '',
                $data['annual_salary'] ?? '',
                $data['ot_weekday_sat_first_3'] ?? '',
                $data['ot_weekday_sat_after_3'] ?? '',
                $data['ot_sun_ph_all'] ?? '',
                $data['ph_time_off_ordinary_hours'] ?? '',
                $data['ordinary_hours'] ?? '',
                $data['casual_ot_weekday_sat_first_3'] ?? null,
                $data['casual_ot_weekday_sat_after_3'] ?? null,
                $data['casual_ot_sun_ph_all'] ?? null,
                $data['casual_ordinary_hours'] ?? null,
                $data['status'] ?? PayClassificationRepository::STATUS_ACTIVE,
                $data['year'] ?? '',
                $data['start_date'] ?? null,
                $data['end_date'] ?? null,
                $data['is_duplicate'] ?? false,
            );

            $results[] = $pc;
        }

        return response()->json([
            'message'  => 'Pay classifications created.',
            'data'     => $results,
            'csv_data' => $csv_data
        ], 201);
    }



    /**
     * Paginate and filter pay classifications.
     */
    public function getPaginatedClassifications(Request $request)
    {
        $perPage       = $request->input('per_page', 10);
        $page          = $request->input('page', 1);
        $search        = $request->input('search');
        $status        = $request->input('status');
        $year        = $request->input('year');
        $classification = $request->input('classification');
        $sortBy        = $request->input('sort_by', 'classification');
        $sortDirection = $request->input('sort_direction', 'asc');

        $results = $this->repository->filterAndPaginate(
            $perPage,
            $page,
            $search,
            $status,
            $classification,
            $sortBy,
            $sortDirection,
            $year
        );

        return response()->json([
            'data'         => $results->items(),
            'total'        => $results->total(),
            'per_page'     => $results->perPage(),
            'current_page' => $results->currentPage(),
        ]);
    }

    /**
     * Retrieve a single pay classification by ID.
     */
    public function show($id)
    {
        $pc = $this->repository->find($id);

        if (! $pc) {
            return response()->json(['message' => 'Not Found'], 404);
        }

        return response()->json($pc);
    }

    /**
     * Create a new pay classification.
     */
    public function store(Request $request)
    {
        $data = $request->only([
            'classification',
            'description',
            'annual_salary',
            'ot_weekday_sat_first_3',
            'ot_weekday_sat_after_3',
            'ot_sun_ph_all',
            'ph_time_off_ordinary_hours',
            'ordinary_hours',
            'casual_ot_weekday_sat_first_3',
            'casual_ot_weekday_sat_after_3',
            'casual_ot_sun_ph_all',
            'casual_ordinary_hours',
            'status',
            'year',
            'start_date', // <-- add
            'end_date',   // <-- add
        ]);
        
        $pc = $this->repository->store(
            $data['classification'],
            $data['description'],
            $data['annual_salary'],
            $data['ot_weekday_sat_first_3'],
            $data['ot_weekday_sat_after_3'],
            $data['ot_sun_ph_all'],
            $data['ph_time_off_ordinary_hours'],
            $data['ordinary_hours'],
            $data['casual_ot_weekday_sat_first_3'] ?? null,
            $data['casual_ot_weekday_sat_after_3'] ?? null,
            $data['casual_ot_sun_ph_all'] ?? null,
            $data['casual_ordinary_hours'] ?? null,
            $data['status'] ?? PayClassificationRepository::STATUS_ACTIVE,
            $data['year'],
            $data['start_date'] ?? null, // <-- add
            $data['end_date'] ?? null    // <-- add
        );

        return response()->json([
            'message' => 'Pay classification created.',
            'data'    => $pc,
        ], 201);
    }


    /**
     * Update an existing pay classification.
     */
    public function update(Request $request, $id)
    {
        $data = $request->only([
            'classification',
            'description',
            'annual_salary',
            'ot_weekday_sat_first_3',
            'ot_weekday_sat_after_3',
            'ot_sun_ph_all',
            'ph_time_off_ordinary_hours',
            'ordinary_hours',
            'status',
        ]);

        $pc = $this->repository->updateClassification($id, $data);

        if (! $pc) {
            return response()->json(['message' => 'Not Found'], 404);
        }

        return response()->json([
            'message' => 'Pay classification updated.',
            'data'    => $pc,
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        // Accept status, start_date, end_date (all flat fields)
        $status = $request->input('status');
        $startDate = $request->input('start_date', null);
        $endDate = $request->input('end_date', null);

        $updated = $this->repository->updateStatus($id, $status, $startDate, $endDate);

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
        $division = $this->repository->getHistory($id);

        return response()->json([
            'message' => 'Fetched history.',
            'data' => $division
        ]);
    }      
}

<?php

namespace App\Core\v1\Position\Repositories;

use App\Core\v1\Position\Models\Position;
use App\Core\v1\Position\Models\PositionHistory;
use App\Repositories\ModelRepository;
use Illuminate\Database\Eloquent\Model;

class PositionRepository extends ModelRepository
{
    /**
     * Specify the Eloquent model class to use.
     *
     * @return Model
     */
    public function model(): Model
    {
        return new Position;
    }

    /**
     * Return all line managers (roles 3 or 4) for a given position.
     */
    public function lineManagers()
    {
        return $this->model
            ->employees()
            ->whereHas('user.role', function ($query) {
                $query->whereIn('id', [3, 4]); // 3 = Line Manager, 4 = General Manager
            })
            ->get();
    }

        /**
         * Filter positions and paginate the results.
         */
    public function filterAndPaginate(
        int    $perPage       = 10,
        int    $page          = 1,
        ?string $search       = null,
        $is_active            = null,
        $positionId           = null,
        string $sortBy        = 'name',
        string $sortDirection = 'asc',
        $divisionId           = null
    ) {
        $query = Position::with(['divisions', 'payClassification']);

        // Filter by division_id (many-to-many)
        if ($divisionId !== null) {
            $query->where(function ($q) use ($divisionId) {
                $q->whereHas('divisions', function ($subQ) use ($divisionId) {
                    $subQ->where('divisions.id', $divisionId);
                })
                ->orWhereDoesntHave('divisions');
            });
        }

        if (!empty($search)) {
            $query->where('name', 'like', "%{$search}%");
        }

        // Handle is_active filter with date logic (accessor mimic)
        if ($is_active !== null) {
            if ($is_active == 0) {
                $query->where(function ($q) {
                    $q->where('is_active', 0)
                    ->orWhere(function ($subQ) {
                        $subQ->whereNotNull('start_date')
                            ->whereDate('start_date', '>', now()->toDateString());
                    })
                    ->orWhere(function ($subQ) {
                        $subQ->whereNotNull('end_date')
                            ->whereDate('end_date', '<', now()->toDateString());
                    });
                });
            } else {
                $query->where('is_active', 1)
                    ->where(function ($q) {
                        $q->whereNull('start_date')
                            ->orWhereDate('start_date', '<=', now()->toDateString());
                    })
                    ->where(function ($q) {
                        $q->whereNull('end_date')
                            ->orWhereDate('end_date', '>=', now()->toDateString());
                    });
            }
        }

        if ($positionId !== null) {
            $query->where('id', $positionId);
        }

        // Only allow sorting on these columns:
        $allowed = ['name', 'classification', 'is_active', 'start_date', 'end_date'];
        $sortBy = strtolower($sortBy);
        if (!in_array($sortBy, $allowed)) {
            $sortBy = 'name';
        }

        $sortDirection = strtolower($sortDirection) === 'desc' ? 'desc' : 'asc';

        // Special case: sort by accessor is_active
        if ($sortBy === 'is_active') {
            $results = $query->get();

            $sorted = $results->sortBy(function ($item) {
                return $item->is_active; // Uses accessor
            }, SORT_REGULAR, $sortDirection === 'desc');

            if ($perPage === -1) {
                $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
                    $sorted->values(),
                    $sorted->count(),
                    $sorted->count(),
                    1
                );
            } else {
                $pageResults = $sorted->forPage($page, $perPage);
                $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
                    $pageResults->values(),
                    $sorted->count(),
                    $perPage,
                    $page
                );
            }
        }
        // Sort by classification (related pay_classification.classification)
        else if ($sortBy === 'classification') {
            $query->leftJoin('pay_classification', 'positions.pay_classification_id', '=', 'pay_classification.id')
                ->orderBy('pay_classification.classification', $sortDirection)
                ->select('positions.*'); // make sure to select base table fields!

            $paginator = ($perPage === -1)
                ? new \Illuminate\Pagination\LengthAwarePaginator(
                    $query->get(),
                    $query->count(),
                    $query->count(),
                    1
                )
                : $query->paginate($perPage, ['*'], 'page', $page);
        }
        // Natural sort for name
        else if ($sortBy === 'name') {
            $results = $query->get();

            $sorted = $results->sortBy(function ($item) {
                return $item->name;
            }, SORT_NATURAL | SORT_FLAG_CASE, $sortDirection === 'desc');

            if ($perPage === -1) {
                $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
                    $sorted->values(),
                    $sorted->count(),
                    $sorted->count(),
                    1
                );
            } else {
                $pageResults = $sorted->forPage($page, $perPage);
                $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
                    $pageResults->values(),
                    $sorted->count(),
                    $perPage,
                    $page
                );
            }
        }
        // All other fields: DB sort
        else {
            $query->orderBy($sortBy, $sortDirection);

            $paginator = ($perPage === -1)
                ? new \Illuminate\Pagination\LengthAwarePaginator(
                    $query->get(),
                    $query->count(),
                    $query->count(),
                    1
                )
                : $query->paginate($perPage, ['*'], 'page', $page);
        }

        $paginator->sortBy        = $sortBy;
        $paginator->sortDirection = $sortDirection;

        return $paginator;
    }





    /**
     * Create a new position, attach vaccinations & compliances, and log history.
     */
    public function store(
        string $name,
        array $division_ids = [],
        string $classification,
        string $start_date,
        string $end_date = null,
        array $vaccinationIds = [],
        array $complianceIds = [],
        $is_active = 1
    ) {
        // Check if a position with the same name already exists
        if ($this->model()->where('name', $name)->exists()) {
            return null;
        }

        $position = $this->model();
        $position->fill([
            'name'           => $name,
            'pay_classification_id' => $classification,
            'start_date'     => $start_date,
            'end_date'       => $end_date,
            'is_active'      => $is_active,
        ]);
        $position->save();

        if (!empty($division_ids)) {
            $position->divisions()->sync($division_ids);
        }

        if (!empty($vaccinationIds)) {
            $position->vaccinations()->sync($vaccinationIds);
        }

        if (!empty($complianceIds)) {
            $position->compliances()->sync($complianceIds);
        }

        $position->histories()->create([
            'user_id'  => auth()->id(),
            'action'   => 'create',
            'activity' => "Position was created.",
        ]);

        return $position;
    }




        

    /**
     * Activate or archive a position and log history.
     */
    public function updateStatus(int $id, bool|int $isActive)
    {
        $position = $this->model()::findOrFail($id);
        $position->is_active = $isActive;
        $position->save();

        $position->histories()->create([
            'user_id'  => auth()->id(),
            'action'   => $isActive ? 'activate' : 'archive',
            'activity' => "Position was changed to " . ($isActive ? 'activated' : 'archived') . ".",
        ]);

        return $position;
    }

    /**
     * Simple update to a position's name.
     */
    public function update(string $name)
    {
        $position = $this->model;
        $position->name = $name;
        $position->save();

        return $position;
    }

    /**
     * Retrieve the history entries for a given position.
     */
    public function getHistory($id)
    {
        return Position::with([
            'histories' => function ($query) {
                $query->with(['user.employee'])->orderByDesc('created_at');
            }
        ])->findOrFail($id)->histories;
    }
}
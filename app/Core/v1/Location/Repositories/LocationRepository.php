<?php

namespace App\Core\v1\Location\Repositories;

use App\Core\v1\Location\Models\Location;
use App\Core\v1\Location\Models\LocationHistory;
use App\Repositories\ModelRepository;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;



class LocationRepository extends ModelRepository
{
    public function model(): Model
    {
        return new Location;
    }

    public function lineManagers()
    {
        return $this->model
            ->employees()
            ->whereHas('user.role', function ($query) {
                // 3 = Line Manager, 4 = General Manager
                $query->whereIn('id', [3, 4]);
            })->get();
    }

    public function filterAndPaginate(
        int $perPage = 10,
        int $page = 1,
        ?string $search = null,
        $is_active = null,
        $locationId = null,
        $divisionId = null,
        string $sortBy = 'name',
        string $sortDirection = 'asc'
    ) {
        $query = Location::query()->with('divisions'); 

        if (!empty($search)) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        if (!is_null($is_active)) {
            if ($is_active == 0) {
                $query->where(function ($q) {
                    $q->where('is_active', 0)
                    ->orWhere(function ($subQ) {
                        $subQ->whereNotNull('valid_from')
                            ->whereDate('valid_from', '>', now()->toDateString());
                    });
                });
            } else {
                $query->where('is_active', 1)
                    ->where(function ($q) {
                        $q->whereNull('valid_from')
                            ->orWhereDate('valid_from', '<=', now()->toDateString());
                    });
            }
        }

        if (!is_null($locationId)) {
            $query->where('id', $locationId);
        }

        if (!is_null($divisionId)) {
            $query->where('division_id', $divisionId);
        }

        // Validate sortBy and sortDirection
        $allowedSortFields = ['name', 'is_active', 'valid_from'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'name';
        }

        $sortDirection = strtolower($sortDirection) === 'desc' ? 'desc' : 'asc';

        // Special case: sort by accessor is_active
        if ($sortBy === 'is_active') {
            $results = $query->get();

            $sorted = $results->sortBy(function ($item) {
                return $item->is_active; // Uses accessor logic
            }, SORT_REGULAR, $sortDirection === 'desc');

            if ($perPage === -1) {
                return new \Illuminate\Pagination\LengthAwarePaginator(
                    $sorted->values(),
                    $sorted->count(),
                    $sorted->count(),
                    1
                );
            } else {
                $pageResults = $sorted->forPage($page, $perPage);
                return new \Illuminate\Pagination\LengthAwarePaginator(
                    $pageResults->values(),
                    $sorted->count(),
                    $perPage,
                    $page
                );
            }
        }

        // All other fields: DB sort
        $query->orderBy($sortBy, $sortDirection);

        if ($perPage === -1) {
            $results = $query->get();
            return new \Illuminate\Pagination\LengthAwarePaginator(
                $results,
                $results->count(),
                $results->count(),
                1
            );
        }

        return $query->paginate($perPage, ['*'], 'page', $page);
    }


    

    public function store(array $data): Location
    {

        // Manual check for existing name (case-insensitive)
        $exists = $this->model()->whereRaw('LOWER(name) = ?', [strtolower($data['name'])])->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'name' => ['Location already exists'],
            ]);
        }
        
        // 1) Create the Location
        $record = $this->model();
        $record->name             = $data['name'];
        $record->valid_from       = $data['valid_from'] ?? null;
        $record->is_division_all  = $data['is_division_all'];   // ← new
        $record->save();

        // 2) Attach divisions via pivot (if any)
        if (! empty($data['division_ids'])) {
            $record->divisions()->sync($data['division_ids']);
        }

        // 3) Log creation
        $record->histories()->create([
            'user_id'  => auth()->id(),
            'action'   => 'create',
            'activity' => "Location '{$record->name}' was created",
        ]);

        return $record;
    }
     

    public function updateStatus(int $id, bool|int $isActive)
    {
        $location = $this->model()::findOrFail($id);
        $originalStatus = $location->is_active;
    
        $location->is_active = $isActive;
        $location->save();
    
        // only log if valid_from is today or in the future
        if (
            $location->valid_from === null
            || ! $location->valid_from->isFuture()
        ) {
            $location->histories()->create([
                'user_id'  => auth()->id(),
                'action'   => $isActive ? 'activate' : 'archive',
                'activity' => "Location '{$location->name}' was " . ($isActive ? 'activated' : 'archived'),
            ]);
        }

        return $location;
    }

    public function update(string $name)
    {
        $record = $this->model;
        $record->name = $name;
        $record->save();

        return $record;
    }

    public function getHistory($id)
    {
        return Location::with([
            'histories' => function ($query) {
                $query->with(['user.employee'])->orderByDesc('created_at');
            }
        ])->findOrFail($id)->histories;
    }
}

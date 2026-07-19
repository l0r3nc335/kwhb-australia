<?php

namespace App\Core\v1\Employment\Repositories;

use App\Core\v1\Employment\Models\Employment;
use App\Core\v1\Employment\Models\EmploymentTypeHistory;
use App\Repositories\ModelRepository;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;


class EmploymentRepository extends ModelRepository
{
    public function model(): Model
    {
        return new Employment;
    }

    // Filter, search, sort, and paginate employment types
public function filterAndPaginate(
    int $perPage = 10,
    int $page = 1,
    ?string $search = null,
    $is_active = null,
    $employmentTypeId = null,
    string $sortBy = 'name',
    string $sortDirection = 'asc'
) {
    $query = Employment::query();

    // Search filter
    if (!empty($search)) {
        $query->where('name', 'like', '%' . $search . '%');
    }

    // Use Australia/Sydney time for filtering
    $nowSydney = \Carbon\Carbon::now('Australia/Sydney')->toDateTimeString();

    if (!is_null($is_active)) {
        if ($is_active == 0) {
            // Inactive: is_active==0 OR valid_from is in the future (Sydney time)
            $query->where(function ($q) use ($nowSydney) {
                $q->where('is_active', 0)
                  ->orWhere(function ($subQ) use ($nowSydney) {
                      $subQ->whereNotNull('valid_from')
                           ->where('valid_from', '>', $nowSydney);
                  });
            });
        } else {
            // Active: is_active==1 AND (valid_from is null OR valid_from <= now in Sydney time)
            $query->where('is_active', 1)
                  ->where(function ($q) use ($nowSydney) {
                      $q->whereNull('valid_from')
                        ->orWhere('valid_from', '<=', $nowSydney);
                  });
        }
    }

    if (!is_null($employmentTypeId)) {
        $query->where('id', $employmentTypeId);
    }

    // Only allow sorting by allowed fields
    $allowedSortFields = ['name', 'is_active', 'hours_per_week'];
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
    } else {
        // Default SQL sorting for other fields
        $query->orderBy($sortBy, $sortDirection);

        if ($perPage === -1) {
            $results = $query->get();
            return new \Illuminate\Pagination\LengthAwarePaginator(
                $results->values(),
                $results->count(),
                $results->count(),
                1
            );
        }
        return $query->paginate($perPage, ['*'], 'page', $page);
    }
}


    // Store employment type with history, description, valid_from, and hours_per_week
    public function store(
        string $name,
        ?string $description = null,
        ?string $validFrom = null,
        ?int $hoursPerWeek = null
    ) {
        // Check if name already exists
        if ($this->model()->where('name', $name)->exists()) {
            return [];
        }

        $record = $this->model();
        $record->name = $name;
        $record->description = $description;
        $record->valid_from = $validFrom;
        $record->hours_per_week = $hoursPerWeek;
        $record->save();

        // Add to history table
        $record->histories()->create([
            'user_id' => auth()->id(),
            'action' => 'create',
            'activity' => "Employment Type '{$record->name}' was created",
        ]);

        return $record;
    }


    public function updateStatus(int $id, bool|int $isActive)
    {
        $employmentType = $this->model()::findOrFail($id);
        $originalStatus = $employmentType->is_active;

        $employmentType->is_active = $isActive;
        $employmentType->save();

        // Log status change to history
        $employmentType->histories()->create([
            'user_id' => auth()->id(),
            'action' => $isActive ? 'activate' : 'archive',
            'activity' => "Employment Type '{$employmentType->name}' was " . ($isActive ? 'activated' : 'archived'),
        ]);

        return $employmentType;
    }

    // Update name/description of employment type
    public function update(int $id, string $name, ?string $description = null)
    {
        $record = $this->model()::findOrFail($id);
        $record->name = $name;
        if ($description !== null) {
            $record->description = $description;
        }
        $record->save();

        // Log update to history
        $record->histories()->create([
            'user_id' => auth()->id(),
            'action' => 'update',
            'activity' => "Employment Type '{$record->name}' was updated",
        ]);

        return $record;
    }

    // Get full history with user/employee relations
    public function getHistory($id)
    {
        return Employment::with([
            'histories' => function ($query) {
                $query->with(['user.employee'])->orderByDesc('created_at');
            }
        ])->findOrFail($id)->histories;
    }
}

<?php

namespace App\Core\v1\JobTitle\Repositories;

use App\Core\v1\JobTitle\Models\JobTitle;
use App\Core\v1\JobTitle\Models\JobTitleHistory;
use App\Repositories\ModelRepository;
use Illuminate\Database\Eloquent\Model;

class JobTitleRepository extends ModelRepository
{
    public function model(): Model
    {
        return new JobTitle;
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
        $jobTitleId = null,
        string $sortBy = 'name',
        string $sortDirection = 'asc',
        $divisionId = null
    ) {
        // Eager load division
        $query = JobTitle::with('division');

        if (!is_null($divisionId)) {
            $query->where('division_id', $divisionId);
        }

        if (!empty($search)) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        if (!is_null($is_active)) {
            $query->where('is_active', $is_active);
        }

        if (!is_null($jobTitleId)) {
            $query->where('id', $jobTitleId);
        }

        // Validate sortBy and sortDirection
        $allowedSortFields = ['name', 'classification', 'is_active', 'start_date'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'name';
        }

        $sortDirection = strtolower($sortDirection) === 'desc' ? 'desc' : 'asc';

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


    public function store(string $name, int $division_id, string $classification, string $start_date, string $end_date, $is_active = 1)
    {
        $record = $this->model();
        $record->name = $name;
        $record->division_id = $division_id;
        $record->classification = $classification;
        $record->start_date = $start_date;
        $record->end_date = $end_date;
        $record->is_active = $is_active;
        $record->save();

        $record->histories()->create([
            'user_id' => auth()->id(),
            'action' => 'create',
            'activity' => "Job Title '{$record->name}' was created.",
        ]);

        return $record;
    }


    public function updateStatus(int $id, bool|int $isActive)
    {
        $jobTitle = $this->model()::findOrFail($id);
        $originalStatus = $jobTitle->is_active;

        $jobTitle->is_active = $isActive;
        $jobTitle->save();

        // Log status change to history
        $jobTitle->histories()->create([
            'user_id' => auth()->id(),
            'action' => $isActive ? 'activate' : 'archive',
            'activity' => "Job Title '{$jobTitle->name}' was " . ($isActive ? 'activated' : 'archived') . '.',
        ]);

        return $jobTitle;
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
        return JobTitle::with([
            'histories' => function ($query) {
                $query->with(['user.employee'])->orderByDesc('created_at');
            }
        ])->findOrFail($id)->histories;
    }
}

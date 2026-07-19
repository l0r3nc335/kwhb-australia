<?php

namespace App\Core\v1\Division\Repositories;

use App\Core\v1\Division\Models\Division;
use App\Core\v1\Division\Models\DivisionHistory;
use App\Repositories\ModelRepository;
use Illuminate\Database\Eloquent\Model;

class DivisionRepository extends ModelRepository
{
    public function model(): Model
    {
        return new Division;
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
        $divisionId = null,
        string $sortBy = 'name',
        string $sortDirection = 'asc'
    ) {
        $query = Division::query();
    
        if (!empty($search)) {
            $query->where('name', 'like', '%' . $search . '%');
        }
    
        if (!is_null($is_active)) {
            $query->where('is_active', $is_active);
        }
    
        if (!is_null($divisionId)) {
            $query->where('id', $divisionId);
        }
    
        // Validate sortBy and sortDirection
        $allowedSortFields = ['name', 'is_active'];
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
    
    

    public function store(string $name)
    {
        $record = $this->model();
        $record->name = $name;
        $record->save();
    
        // Add to history table
        $record->histories()->create([
            'user_id' => auth()->id(), // assumes user is authenticated
            'action' => 'create',
            'activity' => "Division '{$record->name}' was created.",
        ]);
    
        return $record;
    }

    public function updateStatus(int $id, bool|int $isActive)
    {
        $division = $this->model()::findOrFail($id);
        $originalStatus = $division->is_active;
    
        $division->is_active = $isActive;
        $division->save();
    
        // Log status change to history
        $division->histories()->create([
            'user_id' => auth()->id(),
            'action' => $isActive ? 'activate' : 'archive',
            'activity' => "Division '{$division->name}' was " . ($isActive ? 'activated' : 'archived') . '.',
        ]);
    
        return $division;
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
        return Division::with([
            'histories' => function ($query) {
                $query->with(['user.employee'])->orderByDesc('created_at');
            }
        ])->findOrFail($id)->histories;
    }
    
}

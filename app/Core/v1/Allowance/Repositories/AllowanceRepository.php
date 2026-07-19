<?php

namespace App\Core\v1\Allowance\Repositories;

use App\Core\v1\Allowance\Models\Allowance;
use App\Core\v1\Allowance\Models\AllowanceHistory;
use App\Repositories\ModelRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class AllowanceRepository extends ModelRepository
{
    public function model(): Model
    {
        return new Allowance;
    }

    public function filterAndPaginate(
        int $perPage = 10,
        int $page = 1,
        ?string $search = null,
        $positionId = null,
        $status = null,
        string $sortBy = 'name',
        string $sortDirection = 'asc'
    ) {
        $query = Allowance::with('position');

        if (!empty($search)) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        if (!is_null($status) && $status !== '') {
            $query->where('status', $status);
        }        

        if (!is_null($positionId)) {
            $query->where('position_id', $positionId);
        }

        $allowedSortFields = ['name', 'value', 'frequency', 'start_date', 'status'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'name';
        }

        $sortDirection = strtolower($sortDirection) === 'desc' ? 'desc' : 'asc';
        $query->orderBy($sortBy, $sortDirection);

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function filterAndPaginateReturnPositions(
        int $perPage = 10,
        int $page = 1,
        ?string $search = null,
        $positionId = null,
        $status = null,
        string $sortBy = 'name',
        string $sortDirection = 'asc'
    ) {
        $query = Allowance::query() // this was changed (removed with('position'))
        ->select('positions');  // this was changed (select only the positions field)

        $query->whereNotNull('positions');

        if (!empty($search)) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        if (!is_null($status) && $status !== '') {
            $query->where('status', $status);
        }

        if (!is_null($positionId)) {
            $query->where('position_id', $positionId);
        }

        // Allow sorting by existing columns (including positions) even if not selected
        $allowedSortFields = ['name', 'value', 'frequency', 'start_date', 'status', 'positions']; // this was changed
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'name';
        }

        $sortDirection = strtolower($sortDirection) === 'desc' ? 'desc' : 'asc';
        $query->orderBy($sortBy, $sortDirection);

        // Only select 'positions' in the paginated result
        $paginator = $query->paginate($perPage, ['positions'], 'page', $page); // this was changed

        // Make the paginator's "data" contain plain values (strings/arrays) instead of Eloquent models
        // e.g., "data": ["1,2,3", "4,5", ...]
        $paginator->setCollection(
            $paginator->getCollection()->map(function ($row) { // this was changed
                return $row->positions;                        // this was changed
            })
        );

        return $paginator;
    }


    public function store(string $name, ?string $description, array $positions, string $value, string $frequency, ?string $start_date, ?string $end_date, ?string $status)
    {
        $record = $this->model();
        $record->name = $name;
        $record->description = $description;
        $record->position_id = 1; // position is now multi select TODO: make the position_id as nullable or remove it
        $record->positions = implode(',', array_column($positions, 'name'));
        $record->value = $value;
        $record->frequency = $frequency;
        $record->start_date = $start_date;
        $record->end_date = $end_date;
        $record->status = $status;
        $record->save();

        $record->histories()->create([
            'user_id' => auth()->id(),
            'action' => 'create',
            'activity' => "Allowance '{$record->name}' was created.",
        ]);

        return $record;
    }

    public function update(int $id, array $data)
    {
        $record = $this->model()::findOrFail($id);

        $original = $record->getOriginal();

        $record->update($data);

        $changes = collect($record->getChanges())->map(function ($value, $field) use ($original) {
            return "{$field}: '{$original[$field]}' → '{$value}'";
        })->implode(', ');

        if ($changes) {
            $record->histories()->create([
                'user_id' => auth()->id(),
                'action' => 'update',
                'activity' => "Updated fields - {$changes}",
            ]);
        }

        return $record;
    }

    public function update_statuses()
    {
        $record = $this->model()::where('status', 'pending')
            ->whereDate('start_date', '<=', Carbon::today())
            ->update(['status' => 'active']);

        return $record;
    }

    public function updateStatus($id, $status)
    {
        $record = $this->model()::findOrFail($id); // Use findOrFail for consistency

        // Only update if status is different
        if ($record->status !== $status) {
            $record->status = $status;
            $record->save();

            // Log to histories if you have that relationship
            if (method_exists($record, 'histories')) {
                $record->histories()->create([
                    'user_id'  => auth()->id(),
                    'action'   => 'status_update',
                    'activity' => "Allowance status updated to '{$status}'.",
                ]);
            }
        }

        // Return the freshly loaded record (with relations if you want)
        return $record->fresh();
    }


    public function whereIn(string $field, array $values): self
    {
        $this->query->whereIn($field, $values);
        return $this;
    }

    public function getHistory($id)
    {
        return Allowance::with([
            'histories' => function ($query) {
                $query->with(['user.employee'])->orderByDesc('created_at');
            }
        ])->findOrFail($id)->histories;
    }
}

<?php

namespace App\Core\v1\PayRange\Repositories;

use App\Core\v1\PayRange\Models\PayRange;
use App\Core\v1\PayRange\Models\PayRangeHistory;
use App\Repositories\ModelRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Core\v1\PayClassification\Models\PayClassification;

class PayRangeRepository extends ModelRepository
{
    /**
     * Specify the model class.
     *
     * @return Model
     */
    public function model(): Model
    {
        return new PayRange;
    }

    /**
     * Create a new pay range.
     */
    public function store(
        string $classification,
        string $startRange,
        string $endRange,
        ?string $startDate = null,
        ?string $endDate = null,
        string $status = PayRange::STATUS_ACTIVE,
        ?bool $is_duplicate = false
    ): PayRange {
        $pr = $this->model();
        $pr->classification = $classification;
        $pr->start_range = $startRange;
        $pr->end_range = $endRange;
        $pr->start_date = $startDate;
        $pr->end_date = $endDate;

        // Status auto-logic
        $today = \Carbon\Carbon::today();
        $start = $startDate ? \Carbon\Carbon::parse($startDate)->startOfDay() : null;
        $end = $endDate ? \Carbon\Carbon::parse($endDate)->endOfDay() : null;

        if ($start && $end) {
            if ($today->lt($start)) {
                $pr->status = 'pending';
            } elseif ($today->gt($end)) {
                $pr->status = 'expired';
            } else {
                $pr->status = 'active';
            }
        } elseif ($start) {
            if ($today->lt($start)) {
                $pr->status = 'pending';
            } else {
                $pr->status = 'active';
            }
        } elseif ($end) {
            if ($today->gt($end)) {
                $pr->status = 'expired';
            } else {
                $pr->status = 'active';
            }
        } else {
            $pr->status = 'active';
        }

        $pr->save();

        return $pr;
    }


    /**
     * Update an existing pay range by ID.
     */
    public function updateRange(int $id, array $data)
    {
        $pr = PayRange::find($id);
        if (! $pr) {
            return false;
        }

        foreach ([
            'classification',
            'start_range',
            'end_range',
            'start_date',
            'end_date',
            'status',
        ] as $field) {
            if (isset($data[$field])) {
                $pr->{$field} = $data[$field];
            }
        }

        $pr->save();
        return $pr;
    }

    /**
     * Change status of a pay range.
     */
    public function updateStatus(int $id, array $data)
    {
        $pr = PayRange::find($id);
        if (! $pr) {
            return false;
        }

        $oldStatus = $pr->status;

        // Status
        if (isset($data['status'])) {
            $pr->status = $data['status'];
        }

        // Dates
        if (isset($data['start_date'])) {
            $pr->start_date = $data['start_date'];
        }
        if (isset($data['end_date'])) {
            $pr->end_date = $data['end_date'];
        }

        $pr->save();

        // Add history
        $activity = '';
        if (isset($data['status'])) {
            if ($data['status'] === 'inactive') $activity = 'Deactivated';
            if ($oldStatus === 'expired' && $data['status'] === 'active') $activity = 'Reactivated';
            if ($oldStatus === 'inactive' && $data['status'] === 'active') $activity = 'Reactivated';
            if ($oldStatus === 'pending' && $data['status'] === 'active') $activity = 'Activated';
            if ($data['status'] === 'active' && !in_array($oldStatus, ['expired','inactive','pending'])) $activity = 'Activated';
            if ($data['status'] === 'pending') $activity = 'Set to pending';
            if ($data['status'] === 'expired') $activity = 'Set to expired';
        }

        PayRangeHistory::create([
            'pay_range_id' => $pr->id,
            'user_id' => auth()->id(),
            'action' => $activity,
            'activity' => $activity ? "Status was changed to " . $activity : ('Status changed from '.$oldStatus.' to '.$pr->status),
        ]);

        return $pr;
    }

    public function getAllYears(): array
    {
        $years = [];

        // Get min/max year from all start_date and end_date
        $startDates = PayRange::whereNotNull('start_date')->pluck('start_date');
        $endDates   = PayRange::whereNotNull('end_date')->pluck('end_date');

        $dates = $startDates->merge($endDates)->filter();

        foreach ($dates as $date) {
            // Use substr for performance (YYYY-MM-DD)
            $year = substr($date, 0, 4);
            if ($year && is_numeric($year)) {
                $years[] = (int)$year;
            }
        }

        // Remove duplicates and sort descending
        $years = array_unique($years);
        rsort($years);

        // Optionally: if you want associative for select2:
        // return array_map(fn($y) => ['label' => $y, 'value' => $y], $years);

        return array_values($years);
    }    

    /**
     * Apply filters and return paginated results.
     */
    public function filterAndPaginate(
        int $perPage,
        int $page,
        ?string $classification = null,
        ?string $status = null,
        string $sortBy = 'id',
        string $sortDirection = 'asc',
        ?string $minRange = null,
        ?string $maxRange = null,
        ?int $year = null,
        ?string $search
    ): LengthAwarePaginator {
        $query = $this->model()->newQuery();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('classification', 'like', "%{$search}%");
            });
        }        

        if ($classification) {
            $query->where('classification', $classification);
        }
        if ($status) {
            $query->where('status', $status);
        }

        if ($minRange !== null && $minRange !== '') {
            $query->whereRaw('CAST(start_range AS DECIMAL(16,6)) >= ?', [$minRange]);
        }
        if ($maxRange !== null && $maxRange !== '') {
            $query->whereRaw('CAST(end_range AS DECIMAL(16,6)) <= ?', [$maxRange]);
        }

        if ($year) {
            $query->where(function($q) use ($year) {
                $q->whereYear('start_date', $year)
                ->orWhereYear('end_date', $year);
            });
        }

        $query->orderBy($sortBy, $sortDirection);

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function getHistory($id)
    {
        return PayRange::with([
            'histories' => function ($query) {
                $query->with(['user'])->orderByDesc('created_at');
            }
        ])->findOrFail($id)->histories;
    }

    /**
     * Duplicate a pay range.
     */
    public function duplicate(int $id)
    {
        $original = PayRange::find($id);
        if (!$original) {
            return null;
        }

        $new = $original->replicate(['id', 'created_at', 'updated_at']);
        // Optionally, adjust fields on duplicate

        $new->start_date = null;
        $new->end_date = null;
        $new->status = 'pending';

        $new->save();

        return $new;
    }

    // public function classification()
    // {
    //     return $this->belongsTo(PayClassification::class, 'classification_id');
    // }    
}

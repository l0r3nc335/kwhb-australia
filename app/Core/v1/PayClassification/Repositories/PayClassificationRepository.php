<?php

namespace App\Core\v1\PayClassification\Repositories;

use App\Core\v1\PayClassification\Models\PayClassification;
use App\Core\v1\PayClassification\Models\PayClassificationHistory;
use App\Repositories\ModelRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PayClassificationRepository extends ModelRepository
{
    /**
     * Specify the model class.
     *
     * @return Model
     */
    public function model(): Model
    {
        return new PayClassification;
    }

    /**
     * Create a new pay classification.
     */
    public function store(
        string $classification,
        string $description,
        string $annualSalary,
        string $otFirst3,
        string $otAfter3,
        string $otSunPh,
        string $phTimeOff,
        string $ordinaryHours,
        ?string $casualOtFirst3,
        ?string $casualOtAfter3,
        ?string $casualOtSunPh,
        ?string $casualOrdinaryHours,
        string $status = PayClassification::STATUS_ACTIVE,
        string $year,
        ?string $startDate = null,
        ?string $endDate = null,
        ?bool $is_duplicate = false
    ): PayClassification {
        
        $pc = $this->model();
        $pc->classification = $classification;
        $pc->description = $description;
        $pc->annual_salary = $annualSalary;
        $pc->ot_weekday_sat_first_3 = $otFirst3;
        $pc->ot_weekday_sat_after_3 = $otAfter3;
        $pc->ot_sun_ph_all = $otSunPh;
        $pc->ph_time_off_ordinary_hours = $phTimeOff;
        $pc->ordinary_hours = $ordinaryHours;
        $pc->casual_ot_weekday_sat_first_3 = $casualOtFirst3;
        $pc->casual_ot_weekday_sat_after_3 = $casualOtAfter3;
        $pc->casual_ot_sun_ph_all = $casualOtSunPh;
        $pc->casual_ordinary_hours = $casualOrdinaryHours;

        if ($startDate) {
            $dt = \Carbon\Carbon::parse($startDate);
            $pc->year = $dt->format('Y');
        } else {
            $pc->year = $year;
        }

        $pc->start_date = $startDate;
        $pc->end_date = $endDate;

        // Status auto-logic
        $today = \Carbon\Carbon::today();
        $start = $startDate ? \Carbon\Carbon::parse($startDate)->startOfDay() : null;
        $end = $endDate ? \Carbon\Carbon::parse($endDate)->endOfDay() : null;

        if ($start && $end) {
            if ($today->lt($start)) {
                $pc->status = 'pending';
            } elseif ($today->gt($end)) {
                $pc->status = 'expired';
            } else {
                $pc->status = 'active';
            }
        } elseif ($start) {
            if ($today->lt($start)) {
                $pc->status = 'pending';
            } else {
                $pc->status = 'active';
            }
        } elseif ($end) {
            if ($today->gt($end)) {
                $pc->status = 'expired';
            } else {
                $pc->status = 'active';
            }
        } else {
            $pc->status = 'active';
        }

        $pc->save();

        return $pc;
    }



    /**
     * Update an existing pay classification by ID.
     */
    public function updateClassification(int $id, array $data)
    {
        $pc = PayClassification::find($id);
        if (! $pc) {
            return false;
        }

        foreach ([
            'classification',
            'description',
            'annual_salary',
            'ot_weekday_sat_first_3',
            'ot_weekday_sat_after_3',
            'ot_sun_ph_all',
            'ph_time_off_ordinary_hours',
            'ordinary_hours',
            'status',
        ] as $field) {
            if (isset($data[$field])) {
                $pc->{$field} = $data[$field];
            }
        }

        $pc->save();
        return $pc;
    }

    /**
     * Change status of a pay classification.
     */
    public function updateStatus(int $id, array $data)
    {
        $pc = PayClassification::find($id);
        if (! $pc) {
            return false;
        }

        $oldStatus = $pc->status;

        // Status
        if (isset($data['status'])) {
            $pc->status = $data['status'];
        }

        // Dates
        if (isset($data['start_date'])) {
            $pc->start_date = $data['start_date'];
        }
        if (isset($data['expiry_date']) || isset($data['end_date'])) {
            $pc->end_date = $data['expiry_date'] ?? $data['end_date'];
        }

        // Save
        $pc->save();

        // Add history (same as previous message)
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

        PayClassificationHistory::create([
            'pay_classification_id' => $pc->id,
            'user_id' => auth()->id(),
            'action' => $activity,
            'activity' => $activity ? "Status was changed to " . $activity : ('Status changed from '.$oldStatus.' to '.$pc->status),
        ]);

        return $pc;
    }



    /**
     * Find by classification code.
     */
    public function findByCode(string $code)
    {
        return PayClassification::where('classification', $code)->first();
    }

    /**
     * Apply filters and return paginated results.
     *
     * @param  int         $perPage
     * @param  int         $page
     * @param  string|null $search
     * @param  string|null $status
     * @param  string|null $classificationFilter
     * @param  string      $sortBy
     * @param  string      $sortDirection
     * @return LengthAwarePaginator
     */
    public function filterAndPaginate(
        int $perPage,
        int $page,
        ?string $search = null,
        ?string $status = null,
        ?string $classificationFilter = null,
        string $sortBy = 'classification',
        string $sortDirection = 'asc',
        string $year = ''
    ): LengthAwarePaginator {
        $query = $this->model()->newQuery();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('classification', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($year) {
            $query->where('year', $year);
        }        

        if ($classificationFilter) {
            $query->where('classification', $classificationFilter);
        }

        $query->orderBy($sortBy, $sortDirection);

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function getHistory($id)
    {
        return PayClassification::with([
            'histories' => function ($query) {
                $query->with(['user.employee'])->orderByDesc('created_at');
            }
        ])->findOrFail($id)->histories;
    }    


    /**
     * Duplicate a pay classification for a new year.
     */
    public function duplicate(int $id, string $year)
    {
        $original = PayClassification::find($id);
        if (!$original) {
            return null;
        }

        // Replicate without id, timestamps
        $new = $original->replicate(['id', 'created_at', 'updated_at']);
        $new->year = $year;

        // Add " Copy" to the title or code
        $new->classification .= ' Copy'; // or $new->description .= ' Copy';

        // Status and date logic
        $today = \Carbon\Carbon::today();
        $start = $new->start_date ? \Carbon\Carbon::parse($new->start_date)->startOfDay() : null;
        $end = $new->end_date ? \Carbon\Carbon::parse($new->end_date)->endOfDay() : null;
        if ($start && $end) {
            if ($today->lt($start)) {
                $new->status = 'pending';
            } elseif ($today->gt($end)) {
                $new->status = 'expired';
            } else {
                $new->status = 'active';
            }
        } elseif ($start) {
            $new->status = $today->lt($start) ? 'pending' : 'active';
        } elseif ($end) {
            $new->status = $today->gt($end) ? 'expired' : 'active';
        } else {
            $new->status = 'active';
        }

        $new->save();

        return $new;
    }

}

<?php

namespace App\Core\v1\Year\Repositories;

use App\Core\v1\Year\Models\Year;
use App\Repositories\ModelRepository;
use Illuminate\Database\Eloquent\Model;

class YearRepository extends ModelRepository
{
    /**
     * Specify the Eloquent model class to use.
     *
     * @return Model
     */
    public function model(): Model
    {
        return new Year;
    }

    /**
     * Get all years ordered descending.
     */
    public function allDesc()
    {
        return $this->model()->orderBy('year', 'desc')->get();
    }

    /**
     * Store a new year.
     *
     * If table is empty, add current year.
     * If not, add one more than the max year.
     */
    public function store()
    {
        $latestYear = $this->model()->orderBy('year', 'desc')->first();

        if (!$latestYear) {
            $newYear = now()->year;
        } else {
            $newYear = $latestYear->year + 1;
        }

        // Double check: only add if not exists
        $year = $this->model()->firstOrCreate(['year' => $newYear]);

        return $year;
    }
}

<?php

namespace App\Core\v1\Employee\Repositories;

use App\Core\v1\Employee\Models\Employee;
use App\Repositories\ModelRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class EmployeeRepository extends ModelRepository
{
    public function model(): Model
    {
        return new Employee;
    }

    public function statuses(): array
    {
        $statuses = [];

        $data = $this->model()->newQuery()->select([
            DB::raw('distinct status as status')
        ])->get();

        foreach ($data as $d) {
            $statuses[] = $d->status;
        }

        return $statuses;
    }

    public function jobTitles(): array
    {
        $jobTitles = [];

        $data = $this->model()->newQuery()->select([
            DB::raw('distinct job_title as job_title')
        ])->get();

        foreach ($data as $d) {
            $jobTitles[] = $d->job_title;
        }

        return $jobTitles;
    }

    public function reports(): array
    {
        $reports = $this->model()->newQuery()
            ->select(
                [
                    DB::raw("count(id) as total"),
                    DB::raw("sum(case when status='active' then 1 else 0 end) as active"),
                    DB::raw("sum(case when status='offswing' then 1 else 0 end) as offswing"),
                    DB::raw("sum(case when status='pending' then 1 else 0 end) as pending"),
                    DB::raw("sum(case when status='extended leave' then 1 else 0 end) as extended_leave"),
                ]
            )->first();

        return [
            'total' => [
                'value' => $reports->total,
                'percent' => 1
            ],
            'active' => [
                'value' => $reports->active,
                'percent' => round(($reports->active / $reports->total) * 100, 2)
            ],
            'offswing' => [
                'value' => $reports->offswing,
                'percent' => round(($reports->offswing / $reports->total) * 100, 2)
            ],
            'pending' => [
                'value' => $reports->pending,
                'percent' => round(($reports->pending / $reports->total) * 100, 2)
            ],
            'extended_leave' => [
                'value' => $reports->extended_leave,
                'percent' => round(($reports->extended_leave / $reports->total) * 100, 2)
            ],
        ];
    }

    public function approvers(): Collection
    {
        $employee = $this->model;

        return $this->model()
            ->newQuery()
            ->where('division_id', $employee->division_id)
            ->whereHas('user.role', function ($query) {
                $query->whereIn('name', ['Super User', 'Line Manager', 'General Manager', 'HR']);
            })->whereNot('id', $employee->id)
            ->get();
    }

    public function lineManagers(): Collection
    {
        $employee = $this->model;

        return $this->model()
            ->newQuery()
            ->where('division_id', $employee->division_id)
            ->whereHas('user.role', function ($query) {
                // 3 = Line Manager, 4 = General Manager
                $query->whereIn('id', [3, 4]);
            })->get();
    }

    public function updateMedical(array $fields)
    {
        $medical = $this->model->medical()->first();

        if (!empty($medical)) {
            $medical->update($fields);
        } else {
            $this->model->medical()->save($fields);
        }

        return true;
    }

    public function updateEmergencyContact(array $fields)
    {
        $emergency_contact = $this->model->emergency_contact()->first();

        if (!empty($emergency_contact)) {
            $emergency_contact->update($fields);
        } else {
            $this->model->emergency_contact()->save($fields);
        }

        return true;
    }

    public function updateAddress(array $fields)
    {
        $address = $this->model->address()->first();

        if (!empty($address)) {
            $address->update($fields);
        } else {
            $this->model->address()->save($fields);
        }

        return true;
    }
}

<?php

namespace App\Core\v1\Adminsetting\Repositories;

use App\Core\v1\Division\Models\Division;
use App\Core\v1\Employee\Models\Employee;
use App\Core\v1\Employment\Models\Employment;
use App\Core\v1\Location\Models\Location;
use App\Core\v1\Role\Models\Role;
use App\Core\v1\User\Models\User;

class AdminsettingRepository
{
    public function getRolesWithUsers()
    {
        return Role::query()->get();
    }

    public function getDivisions()
    {
        return Division::query()->get();
    }

    public function getLocations()
    {
        return Location::query()->get();
    }

    public function getJobTitles()
    {
        $data = Employee::query()->select('job_title')->distinct()->pluck('job_title')->toArray();
        $titles = [];

        foreach ($data as $k => $v) {
            $title = new \stdClass();
            $title->id = $k + 1;
            $title->name = $v;
            $employees = Employee::query()->where('job_title', $v)->get();
            $title->sample_users = User::query()->with('employee')->whereIn('id', $employees->pluck('user_id')->toArray())->limit(5)->get();
            $title->user_count = count($employees);
            $titles[] = $title;
        }

        return $titles;
    }

    public function getEmployments()
    {
        return Employment::query()->get();
    }
}

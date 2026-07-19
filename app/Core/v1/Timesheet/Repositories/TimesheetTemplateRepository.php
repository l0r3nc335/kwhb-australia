<?php

namespace App\Core\v1\Timesheet\Repositories;

use App\Core\v1\Timesheet\Models\TimesheetTemplate;
use App\Models\User;
use App\Repositories\ModelRepository;
use Illuminate\Database\Eloquent\Model;

class TimesheetTemplateRepository extends ModelRepository
{
    public function model(): Model
    {
        return new TimesheetTemplate;
    }

    public function store(
        int $userId,
        string $name,
        $data
    ): Model
    {
        $template = $this->model();
        $template->user_id = $userId;
        $template->name = $name;
        $template->data = json_encode($data);
        $template->save();

        return $template;
    }

    public function update(
        string $name,
        $data
    ): Model
    {
        $template = $this->model;
        $template->name = $name;
        $template->data = json_encode($data);
        $template->save();

        return $template;
    }
}

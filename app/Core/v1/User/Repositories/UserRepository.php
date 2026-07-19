<?php

namespace App\Core\v1\User\Repositories;

use App\Models\User;
use App\Repositories\ModelRepository;
use Illuminate\Database\Eloquent\Model;

class UserRepository extends ModelRepository
{
    public function model():  \Illuminate\Database\Eloquent\Model
    {
        return new User;
    }

    public function employeeData()
    {
        return $this->model->employee;
    }

    public function getByRoleId($roleId, $fields): \Illuminate\Database\Eloquent\Collection|array
    {
        $query = $this->model->newQuery()
            ->select($fields)
            ->where('role_id', $roleId);
        return $query->get();
    }
}

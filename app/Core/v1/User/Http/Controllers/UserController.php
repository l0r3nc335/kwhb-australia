<?php

namespace App\Core\v1\User\Http\Controllers;

use App\Core\v1\User\Models\User;
use App\Core\v1\User\Repositories\UserRepository;
use App\Http\Controllers\AppController;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
class UserController extends AppController
{
    public function __construct(protected UserRepository $repository)
    {
        parent::__construct();
    }

    public function index(Request $request)
    {
        //$roleId = $request->get('role_id');
        //$data = User::where('role_id', $roleId)->select('id', 'username')->get();
        $data = User::select('id', 'username')->orderBy('username')->get();
        return response()->json($data);
    }

    public static function getUserIdsByDivisions(array $divisions)
    {
        $userIds = User::whereHas('employee.division', function ($query) use ($divisions) {
            $query->whereIn('name', $divisions);
        })->pluck('id');

        return $userIds;
    }

    public function getEmployeeData($id)
    {
        return response()->json(
            $this->repository->find($id, true)->employeeData()
        );
    }
}

<?php

namespace App\Core\v1\Role\Http\Controllers;

use App\Core\v1\Permission\Models\Permission;
use App\Core\v1\Role\Models\Role;
use App\Core\v1\Role\Repositories\RoleRepository;
use App\Http\Controllers\AppController;
use App\Http\Controllers\Controller;
use App\Repositories\ModelRepository;
use Illuminate\Http\Request;

class RoleController extends AppController
{
    public function __construct(protected ModelRepository $repository = new RoleRepository())
    {
        parent::__construct();
    }

    public function all()
    {
        return response()->json(
            $this->repository->orderBy('name', 'asc')->all()
        );
    }

    public function index(Request $request)
    {
        return response()->json(
            $this->repository->orderBy('name', 'asc')->get()
        );
    }

    public function addPermission(Request $request, $id)
    {
        $this->authService->isSuperUser();

        $permissionId = $request->get('permission_id');
        $scope = $request->get('scope');
        $permission = Permission::find($permissionId);
        $roleId = Role::find($id);

        $this->repository->find($roleId, true)->addPermission($permission, $scope);

        return response()->json([
            'message' => 'Role permission updated.'
        ]);
    }

    public function removePermission(Request $request, $id)
    {
        $this->authService->isSuperUser();

        $permissionId = $request->get('permission_id');
        $scope = $request->get('scope');
        $permission = Permission::find($permissionId);
        $roleId = Role::find($id);

        $this->repository->find($roleId, true)->removePermission($permission, $scope);

        return response()->json([
            'message' => 'Role permission updated.'
        ]);
    }
}

<?php

namespace App\Core\v1\Adminsetting\Http\Controllers;

use App\Core\v1\Adminsetting\Repositories\AdminsettingRepository;
use App\Core\v1\Permission\Repositories\PermissionRepository;
use App\Core\v1\Role\Models\Role;
use App\Core\v1\Role\Repositories\RoleRepository;
use App\Http\Controllers\AppController;
use Illuminate\Http\Request;


class AdminSettingController extends AppController
{
    public function __construct(
        protected AdminsettingRepository $repository,
        protected PermissionRepository $permissionRepository,
        protected RoleRepository $roleRepository
    )
    {
        parent::__construct();
    }

    public function updateRolePermissions(Request $request)
    {
        $this->authService->isSuperUser();

        $roleId = $request->get('role_id');
        $featureId = $request->get('feature_id');
        $subFeatureId = $request->get('sub_feature_id');
        $permissions = json_decode($request->get('permissions', json_encode([])));

        $this->roleRepository->updateRolePermissions(Role::find($roleId), $featureId, $subFeatureId, $permissions);

        return response([
            'message' => 'Role permissions updated.'
        ]);
    }

    public function getRolePermissionSettings()
    {
        return response()->json([
            'role_permissions' => $this->roleRepository->all(),
            'actions' => $this->permissionRepository->getActions(),
            'scopes' => $this->permissionRepository->getScopes()
        ]);
    }

    public function getPermissionSettingNavigations()
    {
        return response()->json(
            $this->permissionRepository->permissionSettings()
        );
    }

    public function getRolesWithUsers()
    {
        return response()->json(
            $this->repository->getRolesWithUsers()
        );
    }

    public function getDivisions()
    {
        return response()->json(
            $this->repository->getDivisions()
        );
    }

    public function getLocations()
    {
        return response()->json(
            $this->repository->getLocations()
        );
    }

    public function getJobTitles()
    {
        return response()->json(
            $this->repository->getJobTitles()
        );
    }

    public function getEmployments()
    {
        return response()->json(
            $this->repository->getEmployments()
        );
    }
}

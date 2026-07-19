<?php

namespace App\Core\v1\Role\Repositories;

use App\Core\v1\Permission\Models\Permission;
use App\Core\v1\Role\Models\Role;
use App\Repositories\ModelRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RoleRepository extends ModelRepository
{
    public function model(): Model
    {
        return new Role;
    }

    public function updateRolePermissions(Role $role, int $featureId, int|null $subFeatureId, array $permissions): void
    {
        if (count($permissions) > 0 ) {
            foreach ($permissions as $permission) {
                $p = Permission::query()
                    ->where('feature', $featureId)
                    ->where('sub_feature', $subFeatureId)
                    ->where('action', $permission->action)
                    ->first();

                if (!empty($p)) {
                    DB::table('permission_role')
                        ->where('role_id', $role->id)
                        ->where('permission_id', $p->id)
                        ->delete();

                    if ($permission->scope != 0) {
                        DB::table('permission_role')
                            ->insert([
                                'role_id' => $role->id,
                                'permission_id' => $p->id,
                                'scope' => $permission->scope
                            ]);
                    }
                }
            }
        }
    }

    public function addPermission(Permission $permission, int $scope): void
    {
        DB::table('permission_role')
            ->insert([
                'role_id' => $this->model->id,
                'permission_id' => $permission->id,
                'scope' => $scope
            ]);
    }

    public function removePermission(Permission $permission, int $scope): void
    {
        DB::table('permission_role')
            ->where('permission_id', $permission->id)
            ->where('role_id', $this->model->id)
            ->where('scope', $scope)
            ->delete();
    }
}

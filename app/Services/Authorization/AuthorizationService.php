<?php
namespace App\Services\Authorization;

use App\Core\v1\Permission\Models\Permission;
use App\Core\v1\Role\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class AuthorizationService
{
    public Role $role;
    public Collection $permissions;
    public static Permission|null $currentPermission;
    public User $authUser;

    public function __construct(protected User $user)
    {
        $this->authUser = $user;
        $this->role = $user->role;
        $this->permissions = $this->role->permissions;
    }

    public function isSuperUser(): bool
    {
        if ($this->role->name !== 'Super User') {
            throw new \Exception("Unauthorized. This action is allowed only to Super Users.", 401);
        }

        return true;
    }

    public function allowed(int $feature, int|null $subFeature, int $action)
    {
        if ($this->role->name === 'Super User') {
            return true;
        }

        self::$currentPermission = $this->permissions
            ->where('feature', $feature)
            ->where('sub_feature', $subFeature)
            ->where('action', $action)
            ->first();

        if (empty(self::$currentPermission)) {
            throw new \Exception("Unauthorized. User does not have the right permission required for this action.", 401);
        }

        return true;
    }

    public function scopedQuery(Builder $query, string $tableUserIdField = 'users.user_id'): Builder
    {
        $scopedQuery = $query;
        $authUserId =  $this->user->id;

        if ($this->role->name == 'Super User') {
            return $query;
        }

        switch (self::$currentPermission->pivot->scope) {
            case Permission::SCOPE_SELF:
                $scopedQuery = $query->where("{$tableUserIdField}", $authUserId);
                break;
            case Permission::SCOPE_DIVISION:
                if (!$this->hasJoined($query, 'employees')) {
                    $query = $query->where("{$tableUserIdField}", $authUserId)
                        ->leftJoin('employees', 'employees.user_id', '=', $tableUserIdField);
                }
                $scopedQuery = $query->where('division_id', $this->user->employee->division_id);
                break;
            case Permission::SCOPE_TEAMMATE:
                if (!$this->hasJoined($query, 'employees')) {
                    $query = $query->where("{$tableUserIdField}", $authUserId)
                        ->leftJoin('employees', 'employees.user_id', '=', $tableUserIdField);
                }
                $scopedQuery = $query->where(function($q) {
                    $q->where("employees.manager_id", $this->user->id)->orWhere("employees.line_manager_id", $this->user->id);
                });
                break;
        }

        return $scopedQuery;
    }

    private function hasJoined(Builder $query, $table): bool
    {
        $joins = $query->getQuery()->joins;
        $currentTable = new \stdClass();
        $currentTable->table = $query->getModel()->getTable();

        if (!empty($joins) && is_array($joins)) {
            $joins = [...$joins, $currentTable];
        } else {
            $joins = [$currentTable];
        }

        if($joins == null) {
            return false;
        }

        foreach ($joins as $join) {
            if ($join->table == $table) {
                return true;
            }
        }

        return false;
    }
}

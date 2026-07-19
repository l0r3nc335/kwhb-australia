<?php

namespace App\Core\v1\Activitylog\Repositories;

use App\Core\v1\Activitylog\Models\Activitylog;
use App\Models\User;
use App\Repositories\ModelRepository;
use Illuminate\Database\Eloquent\Model;

class ActivitylogRepository extends ModelRepository
{
    protected string|null $actionName = null;
    protected string|null $description = null;
    protected User|null $user = null;
    protected bool $isSystem = false;

    public function __construct($user, $actionName, $description, $isSystem)
    {
        $this->user = $user;
        $this->actionName = $actionName;
        $this->description = $description;
        $this->isSystem = $isSystem;
    }

    public function model(): Model
    {
        return new Activitylog;
    }

    public function system(): self
    {
        $this->isSystem = true;

        return $this;
    }

    public function user(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function description(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function actionName(string $actionName): self
    {
        $this->actionName = $actionName;

        return $this;
    }

    public function info(
        int $action,
        int $resource,
        array|object|null $oldData,
        array|object|null $newData
    ): void {
        $this->log(
            Activitylog::TYPE_INFO,
            $action,
            $resource,
            $oldData,
            $newData
        );
    }

    public function success(
        int $action,
        int $resource,
        array|object|null $oldData,
        array|object|null $newData
    ): void {
        $this->log(
            Activitylog::TYPE_SUCCESS,
            $action,
            $resource,
            $oldData,
            $newData
        );
    }

    public function warning(
        int $action,
        int $resource,
        array|object|null $oldData,
        array|object|null $newData
    ): void {
        $this->log(
            Activitylog::TYPE_WARNING,
            $action,
            $resource,
            $oldData,
            $newData
        );
    }

    public function error(
        int $action,
        int $resource,
        array|object|null $oldData,
        array|object|null $newData
    ): void {
        $this->log(
            Activitylog::TYPE_WARNING,
            $action,
            $resource,
            $oldData,
            $newData
        );
    }

    protected function log(
        int $type,
        int $action,
        int $resource,
        array|object|null $oldData,
        array|object|null $newData
    ): void {
        $log = $this->model();
        $log->user_id = optional($this->user)->id;
        $log->action = $action;
        $log->type = $type;
        $log->resource = $resource;
        $log->action_name = $this->actionName;
        $log->description = $this->description;
        $log->old_data = $oldData;
        $log->new_data = $newData;
        $log->is_system = $this->isSystem;
        $log->save();
    }
}

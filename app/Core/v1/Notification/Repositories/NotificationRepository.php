<?php

namespace App\Core\v1\Notification\Repositories;

use App\Core\v1\Notification\Models\Notification;
use App\Core\v1\Role\Models\Role;
use App\Models\User;
use App\Repositories\ModelRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class NotificationRepository extends ModelRepository
{
    protected null|User $fromUser;
    protected array $toUsers = [];
    protected null|string $link = null;
    protected int $type;

    public function __construct()
    {
        parent::__construct();

        $this->fromUser = User::find(optional(Auth::user())->id);
        $this->type = Notification::TYPE_STANDARD;
    }

    public function model(): Model
    {
        return new Notification;
    }

    public function toggleRead(): void
    {
        $notification = $this->model;
        $notification->is_read = !$notification->is_read;
        $notification->save();
    }

    public function getAuthUserNotifications()
    {
        $this->query = $this->newQuery()->query->with('user')->whereHas('users', function($q) {
            $q->where('users.id', Auth::user()->id);
        });

        return $this;
    }

    public function getAuthUserUnreadCount(): int
    {
        return $this->newQuery()->query->whereHas('users', function($q) {
            $q->where('users.id', Auth::user()->id);
        })->where('is_read', false)->count();
    }

    public function link(string $link): self
    {
        $this->link = $link;

        return $this;
    }

    public function from(User $user): self
    {
        $this->toUsers = [];
        $this->fromUser = $user;

        return $this;
    }

    public function to(array $toUsers): self
    {
        $this->toUsers = array_merge($this->toUsers, $toUsers);

        return $this;
    }

    public function toSuperUsers(): self
    {
        if (empty($this->fromUser)) {
            throw new \Exception("Required to specify user where the notification is coming from.");
        }

        $superUserRole = Role::query()->where('name', 'Super User')->first();
        $notifyIds = User::query()->where('role_id', $superUserRole->id)->pluck('id')->toArray();

        $this->toUsers = array_merge($this->toUsers, $notifyIds);

        return $this;
    }

    public function toHr(): self
    {
        if (empty($this->fromUser)) {
            throw new \Exception("Required to specify user where the notification is coming from.");
        }

        $hrRole = Role::query()->where('name', 'HR')->first();
        $superUserRole = Role::query()->where('name', 'Super User')->first();

        // If user has no division, notify to super users
        if (empty($divisionId)) {
            $notifyIds = User::query()->where('role_id', $superUserRole->id)->pluck('id')->toArray();
        } else {
            $notifyIds = User::query()
                ->join('employees', 'employees.user_id', '=', 'users.id')
                ->where('users.role_id', $hrRole->id)
                ->where('employees.division_id', $divisionId)
                ->pluck('users.id')
                ->toArray();
        }

        $this->toUsers = array_merge($this->toUsers, $notifyIds);

        return $this;
    }

    public function toManagers(bool $toAllManagers = false, bool $toGeneralManagers = false): self
    {
        if (empty($this->fromUser)) {
            throw new \Exception("Required to specify user where the notification is coming from.");
        }

        $lineManagerRole = Role::query()->where('name', 'Line Manager')->first();
        $generalManager = Role::query()->where('name', 'General Manager')->first();
        $superUserRole = Role::query()->where('name', 'Super User')->first();
        $notifyIds = [];
        $divisionId = $this->fromUser->employee->division_id;
        $managerId = $this->fromUser->employee->line_manager_id;
        $managerId = !empty($managerId) ? $managerId : $this->fromUser->employee->manager_id;

        // If user has no division, notify to super users
        if (empty($divisionId)) {
            $notifyIds = User::query()->where('role_id', $superUserRole->id)->pluck('id')->toArray();
        } else {
            if ($toGeneralManagers) {
                // Try to find line managers first, then general manager if division has no line manager
                $notifyIds = User::query()
                    ->join('employees', 'employees.user_id', '=', 'users.id')
                    ->where('users.role_id', $generalManager->id)
                    ->where('employees.division_id', $divisionId)
                    ->pluck('users.id')
                    ->toArray();
            } else {
                if (empty($managerId)) {
                    // Try to find line managers first, then general manager if division has no line manager
                    $notifyIds =
                        User::query()
                            ->join('employees', 'employees.user_id', '=', 'users.id')
                            ->where('users.role_id', $lineManagerRole->id)
                            ->where('employees.division_id', $divisionId)
                            ->pluck('users.id')
                            ->toArray() ||
                        User::query()
                            ->join('employees', 'employees.user_id', '=', 'users.id')
                            ->where('users.role_id', $generalManager->id)
                            ->where('employees.division_id', $divisionId)
                            ->pluck('users.id')
                            ->toArray();
                } else {
                    $notifyIds[] = $managerId;
                }
            }
        }
        $notifyIds = is_array($notifyIds) ? $notifyIds : [];
        $this->toUsers = array_merge($this->toUsers, $notifyIds);

        return $this;
    }

    public function notify(string $title, string $message)
    {
        if (empty($this->toUsers)) {
            throw new \Exception("Required to specify target user or users for the notification.");
        }

        if (empty($this->fromUser)) {
            throw new \Exception("Required to specify user where the notification is coming from.");
        }

        $notification = $this->model();
        $notification->user_id = $this->fromUser->id;
        $notification->type = $this->type;
        $notification->link = $this->link;
        $notification->title = $title;
        $notification->content = $message;
        $notification->save();

        $notification->users()->sync($this->toUsers);
    }
}

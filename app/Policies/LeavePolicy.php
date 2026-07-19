<?php

namespace App\Policies;

use App\Core\v1\Leave\Models\Leave;
use App\Models\User;
use App\Services\Authorization\AuthorizationService;

class LeavePolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function approveLeave(User $user, int $action)
    {
        if (AuthorizationService::actionAllowed($user, $action)) {
            return true;
        }

        return false;
    }
}

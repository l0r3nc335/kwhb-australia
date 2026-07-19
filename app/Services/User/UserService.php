<?php

namespace App\Services\User;

use App\Core\v1\User\Models\User as UserModelV1;


class UserService
{
    public function getRandomHrUserId()
    {
        return UserModelV1::join('employees', 'users.id', '=', 'employees.user_id')
            ->where('users.role_id', UserModelV1::HR_ROLE_ID)
            ->whereIn('employees.status', UserModelV1::HR_SUPPORT_STATUS)
            ->inRandomOrder()
            ->value('users.id');
    }

    public function getUserIdByUsername(mixed $username) {
        return UserModelV1::where('users.username', $username)->value('id');
    }
}

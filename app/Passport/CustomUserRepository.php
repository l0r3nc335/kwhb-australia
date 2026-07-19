<?php

namespace App\Passport;

use Laravel\Passport\Bridge\User;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Hashing\Hasher;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;

class CustomUserRepository implements UserRepositoryInterface
{
    protected $provider;
    protected $hasher;

    public function __construct(UserProvider $provider, Hasher $hasher)
    {
        $this->provider = $provider;
        $this->hasher = $hasher;
    }

    public function getUserEntityByUserCredentials(
        $username,
        $password,
        $grantType,
        ClientEntityInterface $client
    ) {
        // 👇 Change 'email' to 'username' if your table uses that
        $credentials = ['email' => $username];

        $user = $this->provider->retrieveByCredentials($credentials);

        if (! $user || ! $this->provider->validateCredentials($user, ['password' => $password])) {
            return null;
        }

        return new User($user->getAuthIdentifier());
    }
}

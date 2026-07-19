<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Core\v1\Leave\Models\Leave;
use App\Passport\CustomUserRepository;
use App\Policies\LeavePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\Passport;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Grant\PasswordGrant;
use Laravel\Passport\Bridge\UserRepository;
use Laravel\Passport\Bridge\RefreshTokenRepository;
use DateInterval;

use Illuminate\Contracts\Hashing\Hasher;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Leave::class => LeavePolicy::class
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // directory of the pub private and public keys
        Passport::loadKeysFrom(storage_path('keys'));
        Passport::useTokenModel(\Laravel\Passport\Token::class);

        // Set token expiration
        Passport::tokensExpireIn(now()->addDays(15));
        Passport::refreshTokensExpireIn(now()->addDays(30));

        // Explicitly enable password grant
        $this->enablePasswordGrant();
    }

    protected function enablePasswordGrant()
    {
        $server = app(AuthorizationServer::class);
        $grant = $this->makePasswordGrant();
        $server->enableGrantType($grant, new DateInterval('PT1H'));
    }

    protected function makePasswordGrant()
    {

        $userProvider = Auth::createUserProvider('external_api_users');

        $grant = new \League\OAuth2\Server\Grant\PasswordGrant(
            new CustomUserRepository($userProvider, app(Hasher::class)),
            app(\Laravel\Passport\Bridge\RefreshTokenRepository::class)
        );

        $grant->setRefreshTokenTTL(new \DateInterval('P1M'));

        return $grant;
    }
}

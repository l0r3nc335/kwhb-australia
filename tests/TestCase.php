<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected User $user;
    protected static $databaseMigrated = false;

    protected function setUp(): void
    {
        parent::setUp();

        if (!self::$databaseMigrated) {
            $this->artisan('migrate:fresh');
            $this->artisan('db:seed');

            self::$databaseMigrated = true;
        }

        $this->useStandardUser();
    }

    protected function useStandardUser()
    {
        $user = User::first();

        if (empty($user)) {
            $user = User::create([
                'name' => 'KWHB Standard User',
                'username' => 'kwhb_standard_user',
                'password'  => bcrypt('standard_user'),
                // 'status'  => 'active'
            ]);
        }

        $this->user = $user;

        $this->actingAs($this->user, 'sanctum');
    }
}

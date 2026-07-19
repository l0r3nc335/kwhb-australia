<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('oauth_clients')->insert([
            [
                'user_id' => null,
                'name' => 'Flash Personal Access Client',
                'secret' => 'CRpWwTjveSLcRR2vYjIGxEFSSi2X4q8hMKix0M1Z',
                'provider' => null,
                'redirect' => 'http://localhost',
                'personal_access_client' => 1,
                'password_client' => 0,
                'revoked' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'user_id' => null,
                'name' => 'Flash Password Grant Client',
                'secret' => 'Qb4KDzdxrcmdJSyC6A9gjPEkFyK67rUN07IChnFW',
                'provider' => 'users',
                'redirect' => 'http://localhost',
                'personal_access_client' => 0,
                'password_client' => 1,
                'revoked' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('oauth_clients')->whereIn('name', ['Flash Personal Access Client', 'Flash Password Grant Client'])->delete();
    }
};

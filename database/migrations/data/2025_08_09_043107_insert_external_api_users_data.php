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
        DB::table('external_api_users')->insert([
            [
                'name' => 'MYOB Default user',
                'email' => 'bel@digitallybuzzed.com.au',
                'password' => '$2y$10$WncZnwPMAAm87qhH3ADxR.YsBnkHsvfVxebGzCBwjcsjE3XBgx/tW',
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
        DB::table('external_api_users')->whereIn('name', ['MYOB Default user'])->delete();
    }
};

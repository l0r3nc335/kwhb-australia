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
        DB::table('oauth_personal_access_clients')->insert([
            [
                'client_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'client_id' => 1,
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
        DB::table('oauth_personal_access_clients')->whereIn('client_id', [1])->delete();
    }
};

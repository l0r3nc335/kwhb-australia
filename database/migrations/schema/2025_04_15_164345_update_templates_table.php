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
        DB::table('templates')
            ->whereNull('created_at')
            ->update(['created_at' => now()]);

        DB::table('templates')
            ->whereNull('updated_at')
            ->update(['updated_at' => now()]);

        DB::statement("ALTER TABLE `templates` RENAME COLUMN `name` TO `filename`");

        DB::statement("
            ALTER TABLE `templates`
            MODIFY `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            MODIFY `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE `templates` RENAME COLUMN `filename` TO `name`");
        DB::statement("
            ALTER TABLE `templates`
            MODIFY `created_at` TIMESTAMP NULL,
            MODIFY `updated_at` TIMESTAMP NULL
        ");
    }
};

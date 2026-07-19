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
        DB::statement("ALTER TABLE contracts MODIFY status ENUM(
            'pending_0',
            'pending_50',
            'expired',
            'rejected',
            'completed',
            'draft',
            'archive',
            'cancelled'
        ) NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE contracts MODIFY status ENUM(
            'pending_0',
            'pending_50',
            'expired',
            'rejected',
            'completed',
            'draft',
            'archive'
        ) NOT NULL");
    }
};

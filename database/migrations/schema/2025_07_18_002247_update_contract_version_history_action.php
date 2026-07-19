<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE contract_version_history MODIFY action ENUM(
            'created',
            'updated',
            'deleted',
            'rejected',
            'signed',
            'archived'
        ) NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE contract_version_history MODIFY action ENUM(
            'created',
            'updated',
            'deleted',
            'rejected',
            'signed'
        ) NOT NULL");
    }
};

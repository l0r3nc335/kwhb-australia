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
        Schema::table('contracts', function (Blueprint $table) {
            $table->date('employee_signature_date')->nullable()->after('employee_signature');
            $table->date('contractor_signature_date')->nullable()->after('contractor_signature');
        });

        DB::statement("ALTER TABLE contracts CHANGE COLUMN status status ENUM('pending_0', 'pending_50', 'expired', 'rejected', 'completed', 'draft') DEFAULT 'pending_0'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn('employee_signature_date');
            $table->dropColumn('contractor_signature_date');
        });

        DB::statement("ALTER TABLE contracts CHANGE COLUMN status status ENUM('pending', 'expired', 'rejected', 'completed', 'draft') DEFAULT 'pending'");
    }
};

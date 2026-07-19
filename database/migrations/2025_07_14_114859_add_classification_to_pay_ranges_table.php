<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddClassificationToPayRangesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pay_ranges', function (Blueprint $table) {
            $table->string('classification')->nullable()->after('classification_id');
            // Add `nullable()` if you want to fill it in gradually
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pay_ranges', function (Blueprint $table) {
            $table->dropColumn('classification');
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPayClassificationIdToPositionsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            // If you want to allow positions without a pay classification, use nullable()
            $table->unsignedBigInteger('pay_classification_id')->nullable()->after('division_id');

            // Foreign key constraint
            $table->foreign('pay_classification_id')
                  ->references('id')
                  ->on('pay_classification')
                  ->onDelete('set null'); // or 'cascade', depending on your logic
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            $table->dropForeign(['pay_classification_id']);
            $table->dropColumn('pay_classification_id');
        });
    }
}

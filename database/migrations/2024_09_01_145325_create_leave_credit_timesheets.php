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
        Schema::create('leave_credit_timesheets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('leave_credit_id');
            $table->unsignedBigInteger('timesheet_id');
            $table->float('accrued_credit');
            $table->timestamps();

            $table->foreign('leave_credit_id')->references('id')->on('leave_credits');
            $table->foreign('timesheet_id')->references('id')->on('timesheets')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_credit_timesheets');
    }
};

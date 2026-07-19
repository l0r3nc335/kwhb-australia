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
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->longText('content');
            $table->foreignId('owner_employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('assigned_employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('created_by_employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('position_title');
            $table->enum('status', ['pending', 'expired', 'rejected', 'completed', 'draft'])->default('pending');
            $table->date('expiry_date')->nullable();
            $table->enum('stage', ['draft', 'completed']);
            $table->foreignId('location_id')->constrained('locations')->cascadeOnDelete();
            $table->foreignId('employment_id')->constrained('employments')->cascadeOnDelete();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('salary');
            $table->string('employee_signature')->nullable();
            $table->string('contractor_signature')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};

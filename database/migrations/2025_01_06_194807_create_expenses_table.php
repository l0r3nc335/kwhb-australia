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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->date('expense_date');
            $table->text('reason');
            $table->string('cost_code')->nullable();
            $table->float('amount')->default(0.0);
            $table->text('receipt_image_path')->nullable();
            $table->boolean('confirm_expense_is_work_related')->default(false);
            $table->integer('status')->default(\App\Core\v1\Expense\Models\Expense::STATUS_PENDING);
            $table->text('status_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};

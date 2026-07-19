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
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropForeign('contracts_assigned_employee_id_foreign'); 
            $table->dropColumn('assigned_employee_id'); 
            $table->dropColumn('position_title');
            $table->dropColumn('start_date');
            $table->dropColumn('end_date');
            $table->dropColumn('salary');
            $table->dropColumn('stage');
            $table->dropColumn('contractor_signature');
            $table->dropColumn('contractor_signature_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->foreignId('assigned_employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('position_title');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('salary');
            $table->enum('stage', ['draft', 'completed']);
            $table->string('contractor_signature')->nullable();
            $table->date('contractor_signature_date')->nullable();
        });
    }
};

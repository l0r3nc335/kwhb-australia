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
        Schema::table('leaves', function (Blueprint $table) {
            $table->float('total')->default(7.5)->after('date');
            $table->text('notes')->nullable()->after('description');
            $table->text('attachment')->nullable()->after('notes');
            $table->text('attachment_name')->nullable()->after('attachment');
            $table->boolean('request_exemption')->default(false)->after('attachment_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leaves', function (Blueprint $table) {
            $table->dropColumn('request_exemption');
            $table->dropColumn('attachment_name');
            $table->dropColumn('attachment');
            $table->dropColumn('notes');
            $table->dropColumn('total');
        });
    }
};

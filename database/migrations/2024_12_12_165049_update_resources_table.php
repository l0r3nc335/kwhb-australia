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
        Schema::table('resources', function (Blueprint $table) {
            if (Schema::hasColumn('resources', 'status')) {
                $table->dropColumn('status');
            }
        });

        Schema::table('resources', function (Blueprint $table) {
            if (! Schema::hasColumn('resources', 'privacy')) {
                $table->integer('privacy')->default(\App\Core\v1\Resource\Models\Resource::PRIVACY_PUBLIC);
            }
            if (! Schema::hasColumn('resources', 'mime_type')) {
                $table->string('mime_type')->nullable();
            }
            if (! Schema::hasColumn('resources', 'assigned_to')) {
                $table->unsignedBigInteger('assigned_to')->nullable();
            }
            if (! Schema::hasColumn('resources', 'is_acknowledged')) {
                $table->boolean('is_acknowledged')->default(false);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('resources', function (Blueprint $table) {
            if (Schema::hasColumn('resources', 'is_acknowledged')) {
                $table->dropColumn('is_acknowledged');
            }
            if (Schema::hasColumn('resources', 'assigned_to')) {
                $table->dropColumn('assigned_to');
            }
            if (Schema::hasColumn('resources', 'privacy')) {
                $table->dropColumn('privacy');
            }
            if (Schema::hasColumn('resources', 'mime_type')) {
                $table->dropColumn('mime_type');
            }
            if (! Schema::hasColumn('resources', 'status')) {
                $table->integer('status')->nullable();
            }
        });
    }
};

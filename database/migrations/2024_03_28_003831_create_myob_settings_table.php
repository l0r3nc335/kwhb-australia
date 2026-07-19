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
        Schema::create('myob_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string("access_token");
            $table->string("refresh_token");
            $table->string("expires_in");
            $table->string("token_type");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('myob_settings');
    }
};

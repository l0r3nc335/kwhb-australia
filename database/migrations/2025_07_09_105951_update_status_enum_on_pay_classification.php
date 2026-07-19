<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateStatusEnumOnPayClassification extends Migration
{
    public function up()
    {
        // Use raw SQL to modify ENUM
        DB::statement("ALTER TABLE pay_classification MODIFY COLUMN status ENUM('active', 'pending', 'expired', 'inactive') NOT NULL DEFAULT 'active'");
    }

    public function down()
    {
        // Revert back to original ENUM (adjust if your original is different)
        DB::statement("ALTER TABLE pay_classification MODIFY COLUMN status ENUM('active', 'inactive') NOT NULL DEFAULT 'active'");
    }
}

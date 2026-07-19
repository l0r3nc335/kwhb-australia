<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class YearsTableSeeder extends Seeder
{
    public function run()
    {
        // Check if table is empty
        if (DB::table('years')->count() === 0) {
            DB::table('years')->insert([
                'year' => now()->year,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}

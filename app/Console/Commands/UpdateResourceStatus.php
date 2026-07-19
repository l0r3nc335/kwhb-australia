<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateResourceStatus extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'resources:update-status';

    /**
     * The console command description.
     */
    protected $description = 'Update resource status from "published" to "public"';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $updated = DB::table('resources')
            ->where('status', 'published')
            ->update(['status' => 'public']);

        $this->info("Updated $updated resource(s) from 'published' to 'public'.");
    }
}

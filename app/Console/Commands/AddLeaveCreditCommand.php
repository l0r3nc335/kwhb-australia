<?php

namespace App\Console\Commands;

use App\Core\v1\Leave\Models\Leave;
use App\Core\v1\Leave\Models\LeaveCredit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AddLeaveCreditCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leave:add-credit {username} {--type=} {--credit=20}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $username = $this->argument('username');
        $user = User::where('username', $username)->first();

        if (empty($user)) {
            $this->error('Invalid username');
            return false;
        }

        $type = $this->option('type');
        $credit = $this->option('credit');
        $credit = !empty($credit) ? (float) $credit : 20;

        if (!empty($type)) {
            $this->addCredit($user->id, $type, $credit);
        } else {
            $types = Leave::types();
            foreach ($types as $key => $type) {
                $this->addCredit($user->id, $key, $credit);
            }
        }

        return true;
    }

    private function addCredit(int $userId, int $type, float $credit)
    {
        $lc = LeaveCredit::query()->where('user_id', $userId)->where('type', $type)->first();

        if (!empty($lc)) {
            $lc->credits = $credit;
            $lc->save();
        } else {
            DB::table('leave_credits')->insert([
                'user_id' => $userId,
                'type' => $type,
                'credits' => $credit,
                'date' => Carbon::now()->format('Y-m-d')
            ]);
        }
    }
}

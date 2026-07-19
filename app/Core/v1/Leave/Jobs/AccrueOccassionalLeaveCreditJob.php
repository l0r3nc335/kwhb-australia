<?php

namespace App\Core\v1\Leave\Jobs;

use App\Core\v1\Leave\Models\Leave;
use App\Core\v1\Leave\Services\LeaveCreditAccrualService;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AccrueOccassionalLeaveCreditJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected User $user;

    /**
     * Create a new job instance.
     */
    public function __construct(int $userId)
    {
        $this->user = User::find($userId);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $accrueService = new LeaveCreditAccrualService($this->user);

        $accrueService->accrue(Leave::TYPE_LONG_SERVICE);
        $accrueService->accrue(Leave::TYPE_PERSONAL);
        $accrueService->accrue(Leave::TYPE_INCIDENT_LEAVE);
        $accrueService->accrue(Leave::TYPE_SPECIAL_OR_SORRY);
        $accrueService->accrue(Leave::TYPE_STUDY);
        $accrueService->accrue(Leave::TYPE_COMPASSIONATE);
        $accrueService->accrue(Leave::TYPE_CEREMONIAL);
        $accrueService->accrue(Leave::TYPE_FOIL);
        $accrueService->accrue(Leave::TYPE_PARENTAL);
        $accrueService->accrue(Leave::TYPE_FLEXI_LEAVE);
    }
}

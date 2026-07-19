<?php

namespace App\Jobs;

use App\Core\v1\Notification\Repositories\NotificationRepository;
use App\Models\Compliance;
use App\Models\EmployeeCompliance;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ComplianceExpiryReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected NotificationRepository $notification;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->notification = new NotificationRepository;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $aboutToExpire = Carbon::now()->addDays(5);

        $compliances = EmployeeCompliance::query()->where('expiry_date', '<=', $aboutToExpire->format('Y-m-d'))->get();

        if ($compliances->count() > 0) {
            foreach ($compliances as $compliance) {
                $this->notification
                    ->to([$compliance->user_id])
                    ->notify(
                        "Compliance Expiry",
                        "Your compliance is about to expire."
                    );
            }
        }
    }
}

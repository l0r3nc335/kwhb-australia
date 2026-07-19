<?php

namespace App\Jobs;

use App\Core\v1\Notification\Repositories\NotificationRepository;
use App\Models\EmployeeVaccination;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class VaccinationExpiryReminderJob implements ShouldQueue
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

        $vaccinations = EmployeeVaccination::query()->where('expiry_date', '<=', $aboutToExpire->format('Y-m-d'))->get();

        if ($vaccinations->count() > 0) {
            foreach ($vaccinations as $compliance) {
                $this->notification
                    ->to([$compliance->user_id])
                    ->notify(
                        "Vaccination Expiry",
                        "Your vaccination is about to expire."
                    );
            }
        }
    }
}

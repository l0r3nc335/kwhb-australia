<?php

namespace App\Jobs;

use App\Core\v1\Notification\Repositories\NotificationRepository;
use App\Core\v1\Timesheet\Models\Timesheet;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TimesheetUnsubmittedReminderJob implements ShouldQueue
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
        $timesheets = Timesheet::query()
            ->where('is_submitted', false)
            ->where('is_returned', false)
            ->where('is_approved', false)
            ->get();

        foreach ($timesheets as $timesheet) {
            $this->notification
                ->to([$timesheet->user_id])
                ->notify(
                    "Timesheet Unsubmitted",
                    "Your timesheet is still unsubmitted."
                );
        }
    }
}

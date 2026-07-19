<?php

namespace App\Jobs;

use App\Core\v1\Notification\Repositories\NotificationRepository;
use App\Core\v1\Timesheet\Models\Timesheet;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TimesheetRevisionReminderJob implements ShouldQueue
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
            ->where('is_returned', true)
            ->where('is_approved', false)
            ->get();

        foreach ($timesheets as $timesheet) {
            $this->notification
                ->to([$timesheet->user_id])
                ->notify(
                    "Timesheet Revision Reminder",
                    "Your timesheet revision is due."
                );
        }
    }
}

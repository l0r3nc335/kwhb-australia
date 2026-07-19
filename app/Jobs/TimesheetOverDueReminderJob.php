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

class TimesheetOverDueReminderJob implements ShouldQueue
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
        $dateNow = Carbon::now();
        $firstFortNightDueDate = Carbon::now()->startOfMonth()->addWeeks(2);
        $nextFortNightDueDate = Carbon::now()->endOfMonth();

        if ($dateNow->isAfter($firstFortNightDueDate)) {
            $dueDate = $firstFortNightDueDate;
        } else {
            $dueDate = $nextFortNightDueDate;
        }

        $timesheets = Timesheet::query()
            ->where('is_submitted', true)
            ->where('is_approved', false)
            ->get();

        foreach ($timesheets as $timesheet) {
            $timesheetDate = Carbon::parse($timesheet->date);
            if ($timesheetDate->isAfter($dueDate)) {
                $this->notification
                    ->to([$timesheet->user_id])
                    ->notify(
                        "Timesheet Overdue",
                        "Your timesheet is now overdue."
                    );
            }
        }
    }
}

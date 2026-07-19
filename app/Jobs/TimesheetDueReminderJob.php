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

class TimesheetDueReminderJob implements ShouldQueue
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
            ->where('date', '<=', $dueDate->format('Y-m-d'))
            ->where('is_submitted', false)
            ->where('is_returned', false)
            ->where('is_approved', false)
            ->get();

        foreach ($timesheets as $timesheet) {
            $dueDayDiff = abs(Carbon::parse($timesheet->date)->diffInDays($dueDate) + 1);
            if ($dueDayDiff <= 2) {
                $this->notification
                    ->to([$timesheet->user_id])
                    ->notify(
                        "Timesheet Due",
                        "Your timesheet is due."
                    );
            }
        }
    }
}

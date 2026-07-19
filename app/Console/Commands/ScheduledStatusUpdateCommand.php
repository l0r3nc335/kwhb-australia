<?php

namespace App\Console\Commands;

use App\Core\v1\Notification\Repositories\NotificationRepository;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ScheduledStatusUpdateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'employee:scheduled-status-update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update employee status on scheduled date.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $employees = Employee::query()
            ->whereNotNull('scheduled_date')
            ->whereNotNull('scheduled_status')
            ->where('scheduled_date', '<=', Carbon::now()->format('Y-m-d'))
            ->get();

        if ($employees->count() > 0) {
            foreach ($employees as $employee) {
                $employee->date_hired = $employee->scheduled_date;
                $employee->status = $employee->scheduled_status;
                $employee->scheduled_date = null;
                $employee->scheduled_status = null;
                $employee->save();

                try {
                    $notification = new NotificationRepository;
                    // For managers and hr
                    $notification
                        ->from($employee->user)
                        ->toManagers()
                        ->toHr()
                        ->notify(
                            "Employment Status Change",
                            "<b>{$employee->first_name} {$employee->last_name}</b>'s employment status has been changed."
                        );
                    // for user
                    $notification
                        ->from($employee->user)
                        ->to([$employee->user_id])
                        ->notify(
                            "Employment Status Change",
                            "Your employment status has been changed."
                        );
                } catch (\Exception $e) {
                    //
                }
            }
        } else {
            $this->info('No employees with scheduled status update');
        }
    }
}

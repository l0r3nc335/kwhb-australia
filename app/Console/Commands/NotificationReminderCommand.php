<?php

namespace App\Console\Commands;

use App\Jobs\ComplianceExpiryReminderJob;
use App\Jobs\TimesheetDueReminderJob;
use App\Jobs\TimesheetOverDueReminderJob;
use App\Jobs\TimesheetRevisionReminderJob;
use App\Jobs\TimesheetUnsubmittedReminderJob;
use App\Jobs\VaccinationExpiryReminderJob;
use Illuminate\Console\Command;

class NotificationReminderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:reminders';

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
        try {
            TimesheetDueReminderJob::dispatch();
        } catch (\Exception $exception) {

        }

        try {
            TimesheetOverDueReminderJob::dispatch();
        } catch (\Exception $exception) {

        }

        try {
            TimesheetRevisionReminderJob::dispatch();
        } catch (\Exception $exception) {

        }

        try {
            TimesheetUnsubmittedReminderJob::dispatch();
        } catch (\Exception $exception) {

        }

        try {
            TimesheetUnsubmittedReminderJob::dispatch();
        } catch (\Exception $exception) {

        }

        try {
            VaccinationExpiryReminderJob::dispatch();
        } catch (\Exception $exception) {

        }

        try {
            ComplianceExpiryReminderJob::dispatch();
        } catch (\Exception $exception) {

        }
    }
}

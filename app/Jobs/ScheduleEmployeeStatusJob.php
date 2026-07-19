<?php

namespace App\Jobs;

use App\Models\Employee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ScheduleEmployeeStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $employeeId;
    protected string $targetDate;
    protected string $targetStatus;

    /**
     * Create a new job instance.
     */
    public function __construct(int $employeeId, string $targetDate, string $targetStatus)
    {
        $this->employeeId = $employeeId;
        $this->targetDate = $targetDate;
        $this->targetStatus = $targetStatus;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $employee = Employee::find($this->employeeId);

        if (!empty($employee)) {
            $employee->scheduled_date = null;
            $employee->scheduled_status = null;
            $employee->status = $this->targetStatus;
            $employee->date_hired = $this->targetDate;
            $employee->save();
        }
    }
}

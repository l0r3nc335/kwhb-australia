<?php
namespace App\Core\v1\Leave\Services;

use App\Core\v1\Leave\Models\Leave;
use App\Core\v1\Leave\Models\LeaveCredit;
use App\Core\v1\Leave\Models\LeaveCreditTimesheet;
use App\Core\v1\Timesheet\Models\Timesheet;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class LeaveCreditAccrualService
{
    protected Collection $timesheets;

    const HOURLY_ACCRUE_RATES = [
        Leave::TYPE_ANNUAL => 0.1153847,
        Leave::TYPE_PERSONAL => 0.0384615
    ];

    public function __construct(protected User $user)
    {
    }

    public function timesheets(array $timesheetIds): self
    {
        $this->timesheets = Timesheet::query()->whereIn('id', $timesheetIds)->get();

        return $this;
    }

    public function accrue(int $type): void
    {
        switch ($type) {
            case Leave::TYPE_PERSONAL:
            case Leave::TYPE_ANNUAL:
                $this->accrueRate($type);
                break;
            default:
                $this->accrueOccasion($type);
        }
    }

    public function accrueRate(int $type): void
    {
        if ($this->timesheets->count() > 0 && in_array($type, array_keys(self::HOURLY_ACCRUE_RATES))) {
            foreach ($this->timesheets as $timesheet) {
                $totalHours = $timesheet->total / 8;
                $accruedCredit = $totalHours * self::HOURLY_ACCRUE_RATES[$type];
                $this->saveAccruedCredit($timesheet->id, $type, $accruedCredit);
            }
        }
    }

    public function accrueOccasion(int $type): void
    {
        // WIP
    }

    public function saveAccruedCredit(int $timesheetId, int $leaveType, float $accruedCredits): void
    {
        $leaveCredit = LeaveCredit::query()->where('user_id', $this->user->id)->where('type', $leaveType)->first();

        $accruedRecord = LeaveCreditTimesheet::query()
            ->where('timesheet_id', $timesheetId)
            ->where('leave_credit_id', $leaveCredit->id)
            ->first();

        if (empty($accruedRecord)) {
            $accruedRecord = new LeaveCreditTimesheet;
            $accruedRecord->timesheet_id = $timesheetId;
            $accruedRecord->leave_credit_id = $leaveCredit->id;
            $accruedRecord->save();
        }

        $accruedRecord->accrued_credit = $accruedCredits;
        $accruedRecord->save();
    }
}

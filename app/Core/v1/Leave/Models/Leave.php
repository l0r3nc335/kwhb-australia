<?php

namespace App\Core\v1\Leave\Models;

use App\Core\v1\Employee\Models\Employee;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Leave extends Model
{
    use HasFactory;

    protected $appends = ['leave_type_name', 'approvers', 'status_name'];

    const TYPE_ANNUAL = 1;
    const TYPE_PERSONAL = 2;
    const TYPE_LEAVE_WITHOUT_PAY = 3;
    const TYPE_COMPASSIONATE = 4;
    const TYPE_CEREMONIAL = 5;
    const TYPE_SPECIAL_OR_SORRY = 6;
    const TYPE_FOIL = 7;
    const TYPE_PARENTAL = 8;
    const TYPE_JURY_SERVICE = 9;
    const TYPE_DEFENSE_RESERVE = 10;
    const TYPE_LONG_SERVICE = 11;
    const TYPE_INCIDENT_LEAVE = 12;
    const TYPE_STUDY = 13;
    const TYPE_COMMUNITY_SERVICE = 14;
    const TYPE_FLEXI_LEAVE = 15;

    const STATUS_APPROVED = 1;
    const STATUS_PENDING = 2;
    const STATUS_COMPLETED = 3;
    const STATUS_CANCELLED = 4;
    const STATUS_DECLINED = 5;

    public static function statuses(): array
    {
        return [
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_PENDING => 'Pending Approval',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_DECLINED => 'Declined'
        ];
    }

    public static function types(int $userId = null): array
    {
        $types = [
            self::TYPE_ANNUAL => 'Annual Leave',
            self::TYPE_PERSONAL => 'Personal Leave',
            self::TYPE_LEAVE_WITHOUT_PAY => 'Leave Without Pay (LWOP)',
            self::TYPE_COMPASSIONATE => 'Compassionate Leave',
            self::TYPE_SPECIAL_OR_SORRY => 'Special/Sorry Leave',
            self::TYPE_FOIL => 'FOIL',
            self::TYPE_JURY_SERVICE => 'Jury Service Leave',
            self::TYPE_DEFENSE_RESERVE => 'Defence Reserve Leave',
            self::TYPE_INCIDENT_LEAVE => 'Incident Leave (Sexual, Family, Domestic Violence)',
            self::TYPE_STUDY => 'Study Leave',
            self::TYPE_COMMUNITY_SERVICE => 'Community Service Leave',
            self::TYPE_FLEXI_LEAVE => 'Flexi Leave (TOIL)'
        ];

        $user = User::with('employee')->find($userId);

        if (empty($user)) {
            return $types;
        }

        $userStartDate = $user->employee->date_hired;
        $startDate = ($userStartDate instanceof Carbon) ? $userStartDate : (!empty($userStartDate && is_string($userStartDate)) ? Carbon::parse($userStartDate) : null);

        if (!empty($userStartDate) && abs($startDate->diffInYears(Carbon::now())) >= 7) {
            $types[self::TYPE_LONG_SERVICE] = 'Long Service Leave';
        }

        if (!empty($userStartDate) &&  abs($startDate->diffInYears(Carbon::now())) >= 1) {
            $types[self::TYPE_CEREMONIAL] = 'Ceremonial Leave';
            $types[self::TYPE_PARENTAL] = 'Parental Leave';
        }

        if (!empty($userStartDate) &&  abs($startDate->diffInWeeks(Carbon::now())) >= 46) {
            $types[self::TYPE_FOIL] = 'FOIL';
        }

        return $types;
    }

    public function getApproversAttribute()
    {
        return User::query()->select([
            'leave_batch',
            DB::raw('approver_id as user_id'),
            DB::raw("max(concat(first_name, ' ', last_name)) as name")
        ])
            ->join('leave_approvers', 'leave_approvers.approver_id', '=', 'users.id')
            ->join('employees', 'users.id', '=', 'employees.user_id')
            ->where('leave_approvers.leave_batch', $this->batch)
            ->where('leave_approvers.user_id', $this->user_id)
            ->groupBy([
                'approver_id',
                'leave_batch'
            ])
            ->get();
    }

    public function getLeaveTypeNameAttribute()
    {
        if ($this->type) {
            return self::types()[$this->type];
        }

        return null;
    }

    public function getStatusNameAttribute()
    {
        return self::statuses()[$this->status];
    }

    public function getAttachmentsAttribute()
    {
        return LeaveAttachment::query()
            ->where('user_id', $this->user_id)
            ->where('leave_batch', $this->batch)
            ->get();
    }

    public function employee()
    {
        return $this->hasOne(Employee::class, 'user_id', 'user_id');
    }
}

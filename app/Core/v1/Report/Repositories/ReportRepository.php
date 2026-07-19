<?php

namespace App\Core\v1\Report\Repositories;

use App\Core\v1\Employee\Models\Employee;
use App\Core\v1\Leave\Models\Leave;
use App\Core\v1\Report\Models\Report;
use App\Core\v1\Role\Models\Role;
use App\Core\v1\Timesheet\Models\Timesheet;
use App\Core\v1\Timesheet\Repositories\TimesheetRepository;
use App\Core\v1\User\Models\User;
use App\Repositories\ModelRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ReportRepository extends ModelRepository
{
    protected string $startDate;
    protected string $endDate;
    protected string $dateRangeType;
    protected int $dateRangeIndex = 0;

    public function model(): Model
    {
        return new Report;
    }

    public function setDateStart(string $date = null): self
    {
        if (!empty($date)) {
            $this->startDate = $date;
        }

        return $this;
    }

    public function setDateEnd(string $date = null): self
    {
        if (!empty($date)) {
            $this->endDate = strtolower($date);
        }

        return $this;
    }

    public function setDateRangeType(string $dateRangeType): self
    {
        $this->dateRangeType = strtolower($dateRangeType);

        return $this;
    }

    public function filterByDate(Builder $query, $dateKey = 'created_at'): Builder
    {
        if (!empty($this->startDate)) {
            $query->where($dateKey, '>=', $this->startDate);
        }

        if (!empty($this->endDate)) {
            $query->where($dateKey, '<=', $this->startDate);
        }

        return $query;
    }

    public function generateGraphData(Builder $query): array
    {
        $dates = [];
        $timesheetRepository = new TimesheetRepository;

        switch ($this->dateRangeType) {
            case 'weekly':
                $start = !empty($this->startDate) ? $this->startDate : Carbon::now()->format('Y-m-d');
                $dates = $timesheetRepository->getPeriodDates(Carbon::parse($start));
                break;
            case 'monthly':
                $start = Carbon::now()->startOfMonth();
                $end = Carbon::now()->endOfMonth();
                $diff = $end->diffInDays($start) + 1;
                for ($i = 0; $i < $diff; $i++) {
                    $dates[] = $start->copy()->addDays($i)->format('Y-m-d');
                }
                break;
            case 'quarterly':
                break;
            case 'annually':
                $dates = [1,2,3,4,5,6,7,8,9,10,11,12];
                break;
            default:
                $start = Carbon::parse($this->startDate);
                $end = Carbon::parse($this->endDate);
                $diff = $end->diffInDays($start) + 1;
                for ($i = 0; $i < $diff; $i++) {
                    $dates[] = $start->copy()->addDays($i)->format('Y-m-d');
                }
        }

        return $this->calculateData($query, $dates);
    }

    public function calculateData(Builder $q, array $dates): array
    {
        $data = [];

        switch ($this->dateRangeType) {
            case 'annually':
                foreach ($dates as $date) {
                    $startMonth = Carbon::now()->subYear()->setMonth($date)->startOfMonth()->format('Y-m-d 00:00:00');
                    $endMonth = Carbon::now()->subYear()->setMonth($date)->endOfMonth()->format('Y-m-d 23:59:59');
                    $query = clone $q;
                    $data[Carbon::now()->setMonth($date)->format('F')] = $query->where('created_at', '>=', $startMonth)->where('created_at', '<=', $endMonth)->count();
                }
                break;
            default:
                foreach ($dates as $date) {
                    $dateStart = Carbon::parse($date)->subYear()->startOfDay()->format('Y-m-d H:i:s');
                    $dateEnd = Carbon::parse($date)->subYear()->endOfDay()->format('Y-m-d H:i:s');
                    $query = clone $q;
                    $data[$date] = $query->where('created_at', '>=', $dateStart)->where('created_at', '<=', $dateEnd)->count();
                }
        }

        return $data;
    }

    public function peopleReports(bool $isGraph = false)
    {
        $applicant = Role::query()->where('name', 'Applicant')->first();

        return [
            'active_users' => [
                'value' => $isGraph ? $this->generateGraphData(Employee::query()->where('status', 'active')) : Employee::query()->where('status', 'active')->count(),
                'label' => 'Total Active Users'
            ],
            'users_on_leave' => [
                'value' => 0,
                'label' => 'Total Users on Leave'
            ],
            'terminated_users' => [
                'value' => $isGraph ? $this->generateGraphData(Employee::query()->where('status', 'terminated')) : Employee::query()->where('status', 'terminated')->count(),
                'label' => 'Total Terminated Users'
            ],
            'applicant_users' => [
                'value' => $isGraph ? $this->generateGraphData(User::query()->where('role_id', optional($applicant)->id)) : User::query()->where('role_id', optional($applicant)->id)->count(),
                'label' => 'Total Applicant Users'
            ],
            'offswing_users' => [
                'value' => $isGraph ? $this->generateGraphData(Employee::query()->where('status', 'offswing')) : Employee::query()->where('status', 'offswing')->count(),
                'label' => 'Total Offswing Users'
            ],
            'total_users' => [
                'value' => $isGraph ? $this->generateGraphData(User::query()) : User::query()->count(),
                'label' => 'Total Users'
            ],
        ];
    }

    public function timesheetReports()
    {
        return [
            'submitted' => [
                'value' => Timesheet::query()->where('is_submitted', true)->count(),
                'label' => 'Submitted Timesheets'
            ],
            'unsubmitted' => [
                'value' => Timesheet::query()->where('is_submitted', false)->where('is_returned', false)->count(),
                'label' => 'Unsubmitted Timesheets'
            ],
            'returned' => [
                'value' => Timesheet::query()->where('is_returned', true)->count(),
                'label' => 'Returned Timesheets'
            ],
            'hours_worked' => [
                'value' => Timesheet::query()->where('is_approved', true)->sum('total'),
                'label' => 'Hours Worked'
            ],
            'leave_vs_worked' => [
                'value' => 0,
                'label' => 'Leave vs Worked'
            ],
        ];
    }

    public function leaveReports()
    {
        return [
            'total_hours' => [
                'value' => 0,
                'label' => 'Total Leave Hours'
            ],
            'annual' => [
                'value' => 0,
                'label' => 'Total Annual Leave'
            ],
            'personal' => [
                'value' => 0,
                'label' => 'Total Personal Leave'
            ],
            'lwop' => [
                'value' => 0,
                'label' => 'Total Leave without Pay'
            ],
            'compassionate' => [
                'value' => 0,
                'label' => 'Total Compassionate Leave'
            ],
            'ceremonial' => [
                'value' => 0,
                'label' => 'Total Ceremonial Leave'
            ],
            'special_sorry' => [
                'value' => 0,
                'label' => 'Total Special/Sorry Leave'
            ],
            'foil' => [
                'value' => 0,
                'label' => 'Total FOIL'
            ],
            'parental' => [
                'value' => 0,
                'label' => 'Total Parental Leave'
            ],
            'community_service' => [
                'value' => 0,
                'label' => 'Total Community Service Leave'
            ],
            'incident' => [
                'value' => 0,
                'label' => 'Total Sexual, Family and Domestic violence Leave'
            ],
            'jury_service' => [
                'value' => 0,
                'label' => 'Total Jury Service Leave'
            ],
            'defense_resource' => [
                'value' => 0,
                'label' => 'Total Defense Reserve Leave'
            ],
            'long_service' => [
                'value' => 0,
                'label' => 'Total Long Service Leave'
            ],
            'flexi' => [
                'value' => 0,
                'label' => 'Total Flexi Leave (TOIL)'
            ],
        ];
    }

    public function letterOfOfferReports()
    {
        return [
            'sent' => [
                'value' => 0,
                'label' => 'Total Letters Sent'
            ],
            'rejected' => [
                'value' => 0,
                'label' => 'Total Letters Rejected'
            ],
            'signed' => [
                'value' => 0,
                'label' => 'Total Letters Signed'
            ],
        ];
    }

    public function contractReports()
    {
        return [
            'sent' => [
                'value' => 0,
                'label' => 'Total Letters Sent'
            ],
            'rejected' => [
                'value' => 0,
                'label' => 'Total Letters Rejected'
            ],
            'signed' => [
                'value' => 0,
                'label' => 'Total Letters Signed'
            ],
        ];
    }

    public function vaccinationReports()
    {
        return [
            'current' => [
                'value' => 0,
                'label' => 'Total Current Vaccination Users'
            ],
            'expired' => [
                'value' => 0,
                'label' => 'Total Expired Vaccination Users'
            ],
            'pending_review' => [
                'value' => 0,
                'label' => 'Total Pending Review Vaccination Users'
            ],
            'applied' => [
                'value' => 0,
                'label' => 'Total Applied Vaccination Users'
            ],
            'exempt' => [
                'value' => 0,
                'label' => 'Total Exempt Vaccination Users'
            ],
            'types_and_total' => [
                'value' => 0,
                'label' => 'Vaccination Types & Total Users per type'
            ],
        ];
    }

    public function complianceReports()
    {
        return [
            'current' => [
                'value' => 0,
                'label' => 'Total Current Compliance Users'
            ],
            'expired' => [
                'value' => 0,
                'label' => 'Total Expired Compliance Users'
            ],
            'pending' => [
                'value' => 0,
                'label' => 'Total Pending Compliance Users'
            ],
            'pending_review' => [
                'value' => 0,
                'label' => 'Total Pending Review Compliance Users'
            ],
        ];
    }
}

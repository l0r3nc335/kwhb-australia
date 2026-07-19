<?php

namespace App\Core\v1\Timesheet\Repositories;

use App\Core\v1\Leave\Models\Leave;
use App\Core\v1\Permission\Models\Permission;
use App\Core\v1\Timesheet\Models\Timesheet;
use App\Models\User;
use App\Repositories\ModelRepository;
use App\Services\Authorization\AuthorizationService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TimesheetRepository extends ModelRepository
{
    public function model(): Model
    {
        return new Timesheet;
    }

    public function getDateCategories(string $date, int $userId): array
    {
        $categories = Timesheet::categories();

        $leaves = Leave::query()->where('user_id', $userId)->where('date', $date)->where('status', Leave::STATUS_APPROVED)->get();
        $activeLeaveCategories = [];

        foreach ($leaves as $leave) {
            $leaveCategory = Timesheet::mapLeaveTimesheetCategories()[$leave->type] ?? null;
            if (!empty($leaveCategory)) {
                if (is_array($leaveCategory)) {
                    foreach ($leaveCategory as $lc) {
                        $activeLeaveCategories[] = $lc;
                    }
                } else {
                    $activeLeaveCategories[] = $leaveCategory;
                }
            }
        }

        $activeLeaveCategories = array_unique($activeLeaveCategories);

        foreach ($activeLeaveCategories as $activeLeaveCategory) {
            $categories[$activeLeaveCategory] = Timesheet::leaveCategories()[$activeLeaveCategory];
        }

        $objectCategories = [];

        foreach ($categories as $id => $name) {
            $c = new \stdClass();
            $c->id = $id;
            $c->name = $name;
            $objectCategories[] = $c;
        }

        return $objectCategories;
    }

    public function getPeriodDates(Carbon $startDate): array
    {
        $dates = [];
        $i = 0;
        $dayName = Carbon::parse($startDate->format('Y-m-d'))->format('l');
        $weekStartDate = Carbon::parse($startDate->format('Y-m-d'))->startOf('week')->addDays(2)->format('Y-m-d');

        if (in_array($dayName, ['Monday', 'Tuesday'])) {
            $weekStartDate = Carbon::parse($startDate->format('Y-m-d'))->startOf('week')->subWeek()->addDays(2)->format('Y-m-d');
        }

        while ($i < 7) {
            $dates[] = Carbon::parse($weekStartDate)->addDays($i)->format('Y-m-d');
            $i++;
        }

        return $dates;
    }

    public function deleteCategoryEntries(int $userId, int $category, string $startDate): void
    {
        $dates = $this->getPeriodDates(Carbon::parse($startDate));

        $this->model()->newQuery()->whereIn('date', $dates)
            ->where('user_id', $userId)
            ->where('category', $category)
            ->delete();
    }

    public function timesheets(Carbon $startDate, User $user): array
    {
        $dates = $this->getPeriodDates($startDate);

        return $this->model()
            ->newQuery()
            ->select([
                'timesheets.*',
                DB::raw("concat(employees.first_name, ' ', employees.last_name) as full_name")
            ])
            ->join('employees', 'employees.user_id', '=', 'timesheets.user_id')
            ->where('timesheets.user_id', $user->id)
            ->whereIn('date', $dates)
            ->orderBy('date')
            ->get()
            ->toArray();
    }

    public function submitWeekPeriod($userId, Carbon $startDate): bool
    {
        $dates = $this->getPeriodDates($startDate);

        $entries = $this->model()
            ->newQuery()
            ->where('user_id', $userId)
            ->whereIn('date', $dates)
            ->get();

        if ($entries->count() > 0) {
            foreach ($entries as $timesheet) {
                $this->model = $timesheet;
                $this->submit();
            }

            return true;
        }

        return false;
    }

    public function applyTimesheets(array $periodDates, array $timesheets, $userId)
    {
        $this->model()->newQuery()->whereIn('date', $periodDates)->delete();
        $namedDates = [];

        foreach ($periodDates as $pDate) {
            $namedDates[Carbon::parse($pDate)->dayName] = $pDate;
        }

        foreach ($timesheets as $timesheet) {
            $targetDate = $namedDates[Carbon::parse($timesheet['date'])->dayName];

            $this->store(
                $userId, 
                is_array($timesheet['category'])? $timesheet['category']['id'] : $timesheet['category'], 
                $targetDate, 
                $timesheet['start'], 
                $timesheet['end'], 
                $timesheet['break'], 
                $timesheet['comment']
            );
        }
    }

    public function store(
        int $userid,
        int $category,
        string $date,
        string $start = null,
        string $end = null,
        int $break = 0,
        string $comment = null
    ): Model {
        $timesheet = $this->model();
        $timesheet->user_id = $userid;
        $timesheet->category = $category;
        $timesheet->date = $date;
        $timesheet->start = $start;
        $timesheet->end = $end;
        $timesheet->break = $break;
        $timesheet->comment = $comment;
        $timesheet->save();

        $timesheet->total = $this->computeTimesheetTotalMinutes($timesheet);
        $timesheet->save();

        return $timesheet;
    }

    public function update(
        int $category,
        string|null $start,
        string|null $end,
        int $break = 0,
        string $comment = null
    ): Model {
        $timesheet = $this->model;
        $timesheet->category = $category;
        $timesheet->break = $break;
        $timesheet->comment = $comment;

        if (!empty($start)) {
            $timesheet->start = $start;
        }
        if (!empty($end)) {
            $timesheet->end = $end;
        }

        $timesheet->save();
        $timesheet->total = $this->computeTimesheetTotalMinutes($timesheet);
        $timesheet->save();

        return $timesheet;
    }

    public function categories(): array
    {
        $categories = [];

        foreach (Timesheet::categories() as $key => $category) {
            $cat = new \stdClass();
            $cat->id = $key;
            $cat->name = $category;
            $categories[] = $cat;
        }

        foreach (Timesheet::leaveCategories() as $key => $category) {
            $cat = new \stdClass();
            $cat->id = $key;
            $cat->name = $category;
            $categories[] = $cat;
        }

        return $categories;
    }

    public function submit(): void
    {
        $timesheet = $this->model;
        $timesheet->is_returned = false;
        $timesheet->is_submitted = true;
        $timesheet->save();
    }

    public function approve(): void
    {
        $timesheet = $this->model;
        $timesheet->is_returned = false;
        $timesheet->return_comment = null;
        $timesheet->is_approved = true;
        $timesheet->approved_by = Auth::user()->id;
        $timesheet->save();
    }

    public function fetchTimesheetsPerUser(bool $submitted = false, bool $approved = false)
    {
        $query = $this->model()
            ->newQuery()
            ->select([
                DB::raw('max(employees.location_id) as location_id'),
                DB::raw('max(employees.division_id) as division_id'),
                DB::raw('max(employees.manager_id) as manager_id'),
                DB::raw('max(timesheets.date) as date'),
                'employees.user_id',
                DB::raw('max(job_title) as job_title'),
                DB::raw('max(return_comment) as return_comment'),
                DB::raw('(select group_concat(role_id) from user_role where user_role.user_id = timesheets.user_id) as roles'),
                DB::raw("(select concat(employees.first_name, ' ', employees.last_name) from employees where employees.user_id = timesheets.user_id) as name"),
                DB::raw("(case when (dayname(`date`) = 'Monday' or dayname(`date`) = 'Tuesday') then subdate(`date`, weekday(`date`) + 5) else subdate(`date`, weekday(`date`) - 2) end) as week_start"),
                DB::raw("(case when (dayname(`date`) = 'Monday' or dayname(`date`) = 'Tuesday') then subdate(`date`, weekday(`date`) - 1) else subdate(`date`, weekday(`date`) - 8) end) as week_end"),
                DB::raw('sum(total) as total'),
                DB::raw('max(is_submitted) as submitted'),
                DB::raw('max(is_approved) as approved'),
                DB::raw('max(is_returned) as returned'),
                DB::raw("(concat('[', group_concat(json_object('id', timesheets.id, 'category', category, 'start', start, 'end', end, 'break', break, 'date', date, 'total', total, 'comment', comment, 'is_returned', is_returned)), ']')) as entries")
            ])
            ->join('users', 'users.id', '=', 'timesheets.user_id')
            ->join('employees', 'employees.user_id', '=', 'timesheets.user_id')
            ->whereNotNull('timesheets.user_id');

        $query = $this->authService->scopedQuery($query, 'timesheets.user_id');

        if ($submitted) {
            $query = $query->where('is_submitted', $submitted);
        } else {
            $query = $query->where(function ($q) {
                $q->where('is_submitted', false)->orWhere('is_returned', true);
            });
        }

        return $query->where('is_approved', $approved)
            ->groupBy([ 'timesheets.user_id', 'week_start', 'week_end'])
            ->orderBy('week_start', 'desc')
            ->get();
    }

    public function returnEntry(string $remarks): void
    {
        $timesheet = $this->model;
        $timesheet->is_returned = true;
        $timesheet->return_comment = $remarks;
        $timesheet->save();
    }

    public function approveWeekPeriod(int|null $userId, null|Carbon $date): void
    {
        $query = $this->model()->newQuery();

        if (!empty($userId)) {
            $query = $query->where('timesheets.user_id', $userId);
        }

        if (!empty($date)) {
            $periodDates = $this->getPeriodDates($date);
            $query = $query->whereIn('timesheets.date', $periodDates);
        }

        $query = $this->authService->scopedQuery($query, 'timesheets.user_id');
        $query->update([
            'is_submitted' => true,
            'is_returned' => false,
            'return_comment' => null,
            'is_approved' => true,
            'approved_by' => Auth::user()->id
        ]);
    }

    public function returnWeekPeriod(int $userId, Carbon $date, string $remarks): void
    {
        $periodDates = $this->getPeriodDates($date);

        $this->model()->newQuery()
            ->where('user_id', $userId)
            ->whereIn('date', $periodDates)
            ->update([
                'is_returned' => true,
                'return_comment' => $remarks
            ]);
    }

    public function computeTimesheetTotalMinutes(Model $timesheet): int
    {
        if (!empty($timesheet->start) && !empty($timesheet->end)) {
            if ($timesheet->end < $timesheet->start) {
                $startDate = Carbon::parse($timesheet->date . ' ' . $timesheet->start . ':00');
                $endDate = Carbon::parse($timesheet->date . ' ' . $timesheet->end . ':00')->addDay();
            } else {
                $startDate = Carbon::parse($timesheet->date . ' ' . $timesheet->start . ':00');
                $endDate = Carbon::parse($timesheet->date . ' ' . $timesheet->end . ':00');
            }


            return $endDate->diffInMinutes($startDate) - (integer) $timesheet->break;
        }

        return $timesheet->break;
    }

    public function deleteTimesheets(string $date, int $userId): void
    {
        $date = Carbon::parse($date);
        $dates = $this->getPeriodDates($date);

        $this->model()->newQuery()->whereIn('date', $dates)->where('user_id', $userId)->delete();

    }
}

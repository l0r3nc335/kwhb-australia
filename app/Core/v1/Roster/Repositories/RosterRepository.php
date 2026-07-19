<?php

namespace App\Core\v1\Roster\Repositories;

use App\Core\v1\Roster\Models\Roster;
use App\Core\v1\Roster\Models\RosterShift;
use App\Core\v1\Roster\Models\RosterTemplate;
use App\Repositories\ModelRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

class RosterRepository extends ModelRepository
{
    public function model(): Model
    {
        return new Roster;
    }

    public function addShift(
        int $rosterId,
        int $userId,
        int $type,
        string $recurrence,
        string $startTime,
        string $finishTime,
        string $startDate,
        string $endDate,
        bool $isRepeat = false,
        bool $isNextDayFinish = false,
        array $occursOn = []
    )
    {
        $shift = new RosterShift;
        $shift->roster_id = $rosterId;
        $shift->user_id = $userId;
        $shift->type = $type;
        $shift->recurrence = $recurrence;
        $shift->start = $startTime;
        $shift->end = $finishTime;
        $shift->start_date = $startDate;
        $shift->end_date = $endDate;
        $shift->is_repeat = $isRepeat;
        $shift->is_next_day_finish = $isNextDayFinish;
        $shift->occurs_on = json_encode($occursOn);
        $shift->save();

        return $shift;
    }

    public function update(
        int $type,
        string $recurrence,
        string $startTime,
        string $finishTime,
        string $startDate,
        string $endDate,
        bool $isRepeat = false,
        bool $isNextDayFinish = false,
        array $occursOn = []
    )
    {
        $shift = $this->model;
        $shift->type = $type;
        $shift->recurrence = $recurrence;
        $shift->start = $startTime;
        $shift->end = $finishTime;
        $shift->start_date = $startDate;
        $shift->end_date = $endDate;
        $shift->is_repeat = $isRepeat;
        $shift->is_next_day_finish = $isNextDayFinish;
        $shift->occurs_on = json_encode($occursOn);
        $shift->save();

        return $shift;
    }

    public function addRoster(int $locationId): Model
    {
        $roster = $this->model();
        $roster->location_id = $locationId;
        $roster->save();

        return $roster;
    }

    public function shifts(Roster $roster)
    {
        return $roster->shifts()->get();
    }

    public function shiftTypes(): array
    {
        return [
            [
                'id' => Roster::SHIFT_TYPE_AGENCY_RAN,
                'name' => 'Agency RAN'
            ],
            [
                'id' => Roster::SHIFT_TYPE_PERMANENT_RAN,
                'name' => 'Permanent RAN'
            ],
            [
                'id' => Roster::SHIFT_TYPE_FIRST_RESPONDER,
                'name' => 'First Responder'
            ],
            [
                'id' => Roster::SHIFT_TYPE_SECOND_RESPONDER,
                'name' => 'Second Responder'
            ],
            [
                'id' => Roster::SHIFT_TYPE_GP,
                'name' => 'GP'
            ],
            [
                'id' => Roster::SHIFT_TYPE_PROGRAM_STAFF,
                'name' => 'Program Staff'
            ],
            [
                'id' => Roster::SHIFT_TYPE_PHSCC,
                'name' => 'PHSCC'
            ],
            [
                'id' => Roster::SHIFT_TYPE_ON_CALL,
                'name' => 'On Call'
            ],
            [
                'id' => Roster::SHIFT_TYPE_LEAVE,
                'name' => 'Leave'
            ]
        ];
    }

    public function removeStaff(int $userId): void
    {
        RosterShift::query()->where('roster_id', $this->model->id)->where('user_id', $userId)->delete();
        $this->model->users()->detach([$userId]);
    }

    public function addStaff(int $userId): void
    {
        $this->model->users()->syncWithoutDetaching([$userId]);
    }

    public function addTemplate(string $name, object|array $shiftsData): void
    {
        $template = new RosterTemplate;
        $template->name = $name;
        $template->user_id = auth()->user()->id;
        $template->roster_id = $this->model->id;
        $template->shifts = json_encode($shiftsData);
        $template->save();
    }

    public function deleteTemplate(int $templateId): void
    {
        RosterTemplate::query()->where('id', $templateId)->delete();
    }

    public function queryShifts(string $startDate, string $endDate)
    {
        $dateStartCarbon = Carbon::parse($startDate);
        $dateEndCarbon = Carbon::parse($endDate);
        $dateStartCopy = $dateStartCarbon->copy();
        $dayRange = $dateStartCarbon->diffInDays($dateEndCarbon);
        $dateRange = [];

        for($i=0;$i<=$dayRange;$i++) {
            $dateRange[] = Carbon::parse($dateStartCopy->format('Y-m-d'))->addDays($i)->format('Y-m-d');
        }

        $this->query = $this->query->with('shifts', function($q) use ($dateRange) {
            $q->where(function($q2) use ($dateRange) {
                // Date range condition
                if (count($dateRange) === 1) {
                    $rawQ = "find_in_set(dayname('{$dateRange[0]}'), replace(replace(replace(occurs_on, '\"', ''), '[', '') , ']', ''))";
                    $q2->whereRaw($rawQ);
                }
                foreach ($dateRange as $index => $date) {
                    $where = $index == 0 ? 'where' : 'orWhere';
                    $q2->$where(function($q3) use ($date) {
                        $q3->where('start_date', '<=', $date)
                            ->Where('end_date', '>=', $date);
                        $rawQ = "find_in_set(dayname('{$date}'), replace(replace(replace(occurs_on, '\"', ''), '[', '') , ']', ''))";
                        $q3->whereRaw($rawQ);
                    });
                }
            });
            // is repeat condition
            $q->orWhere(function($q2) use ($dateRange) {
                $q2->where('is_repeat', true)
                    ->where('start_date', '<=', $dateRange[0]);
                if (count($dateRange) === 1) {
                    $rawQ = "find_in_set(dayname('{$dateRange[0]}'), replace(replace(replace(occurs_on, '\"', ''), '[', '') , ']', ''))";
                    $q2->whereRaw($rawQ);
                }
            });
        });

        return $this;
    }
}

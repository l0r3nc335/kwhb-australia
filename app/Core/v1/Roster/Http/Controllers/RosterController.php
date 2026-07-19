<?php

namespace App\Core\v1\Roster\Http\Controllers;

use App\Core\v1\Roster\Models\Roster;
use App\Core\v1\Roster\Models\RosterShift;
use App\Core\v1\Roster\Repositories\RosterRepository;
use App\Http\Controllers\AppController;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RosterController extends AppController
{
    public function __construct(protected RosterRepository $repository)
    {
        parent::__construct();
    }

    public function index(Request $request)
    {
        $locationId = $request->get('location_id');
        $dateStart = $request->get('start_date', Carbon::now()->startOfYear()->format('Y-m-d'));
        $dateEnd = $request->get('end_date', Carbon::now()->endOfYear()->format('Y-m-d'));

        $rosterQuery = $this->repository
            ->newQuery()
            ->filter(['location_id' => $locationId], true, true);

        $query = $rosterQuery->queryShifts($dateStart, $dateEnd);

        return response()->json(
            $query->all()
        );
    }

    public function getShifts($id)
    {
        $roster = Roster::find($id);

        return response()->json(
            $this->repository->shifts($roster)
        );
    }

    public function getShiftTypes()
    {
        return response()->json(
            $this->repository->shiftTypes()
        );
    }

    public function addShift(Request $request)
    {
        $locationId = $request->get('location_id');
        $userId = $request->get('user_id');
        $type = $request->get('type');
        $recurrence = $request->get('recurrence');
        $start = $request->get('start_time');
        $finish = $request->get('finish_time');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $isRepeat = $request->boolean('is_repeat');
        $isNextDayFinish = $request->boolean('is_next_day_finish');
        $occursOn = $request->get('occurs_on');

        $roster = $this->repository->newQuery()->query->where('location_id', $locationId)->first();

        if (!empty($roster)) {
            $this->repository->addShift(
                $roster->id,
                $userId,
                $type,
                $recurrence,
                $start,
                $finish,
                $startDate,
                $endDate,
                $isRepeat,
                $isNextDayFinish,
                $occursOn
            );

            return response()->json([
                'message' => 'Shift added'
            ]);
        }

        return response()->json([
            'message' => 'Invalid Roster'
        ], 400);
    }

    public function getLocationEmployees($locationId)
    {

    }

    public function updateShift(Request $request, $id)
    {
        $shift = RosterShift::find($id);
        $type = $request->get('type');
        $recurrence = $request->get('recurrence');
        $start = $request->get('start_time');
        $finish = $request->get('finish_time');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $isRepeat = $request->boolean('is_repeat');
        $isNextDayFinish = $request->boolean('is_next_day_finish');
        $occursOn = $request->get('occurs_on');

        if (!empty($shift)) {
            $this->repository
                ->use($shift)
                ->update(
                    $type,
                    $recurrence,
                    $start,
                    $finish,
                    $startDate,
                    $endDate,
                    $isRepeat,
                    $isNextDayFinish,
                    $occursOn
                );

            return response()->json([
                'message' => 'Shift updated'
            ]);
        }

        return response()->json([
            'message' => 'Invalid shift.'
        ], 401);
    }

    public function removeRosterEmployee(Request $request)
    {
        $locationId = $request->get('location_id');
        $userId = $request->get('user_id');

        $roster = $this->repository
            ->newQuery()
            ->filter(['location_id' => $locationId], true, true)
            ->query
            ->first();

        if (empty($roster)) {
            return response()->json([
                'message' => 'Roster has no staffs yet.'
            ], 400);
        }

        $this->repository->use($roster)->removeStaff($userId);

        return response()->json([
            'message' => 'Staff removed from the roster'
        ]);
    }

    public function addRosterEmployee(Request $request)
    {
        $locationId = $request->get('location_id');
        $userId = $request->get('user_id');

        $roster = $this->repository
            ->newQuery()
            ->filter(['location_id' => $locationId], true, true)
            ->query
            ->first();

        if (empty($roster)) {
            $roster = $this->repository->addRoster($locationId);
        }

        $this->repository->use($roster)->addStaff($userId);

        return response()->json([
            'message' => 'Staff added to roster'
        ]);
    }

    public function getTemplates(Request $request)
    {
        $locationId = $request->get('location_id');
        $roster = $this->repository->newQuery()->query->where('location_id', $locationId)->first();

        return response()->json(
            optional($roster)->templates
        );
    }

    public function addTemplate(Request $request)
    {
        $locationId = $request->get('location_id');
        $shifts = $request->get('shifts');
        $name = $request->get('name');

        $roster = $this->repository->newQuery()->query->where('location_id', $locationId)->first();

        if (!empty($roster)) {
            $this->repository->use($roster)->addTemplate($name, $shifts);

            return response()->json([
                'message' => 'Template Added'
            ]);
        }

        return response()->json([
            'message' => 'Invalid roster.'
        ], 401);
    }

    public function deleteTemplate(Request $request)
    {
        $templateId = $request->get('template_id');
        $this->repository->deleteTemplate($templateId);

        return response()->json([
            'message' => 'Template deleted.'
        ], 201);
    }
}

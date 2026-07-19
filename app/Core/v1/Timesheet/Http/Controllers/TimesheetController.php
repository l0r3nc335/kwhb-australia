<?php

namespace App\Core\v1\Timesheet\Http\Controllers;

use App\Core\v1\Notification\Repositories\NotificationRepository;
use App\Core\v1\Permission\Models\Permission;
use App\Core\v1\Timesheet\Http\Requests\StoreRequest;
use App\Core\v1\Timesheet\Http\Requests\UpdateRequest;
use App\Core\v1\Timesheet\Repositories\TimesheetRepository;
use App\Core\v1\Timesheet\Repositories\TimesheetTemplateRepository;
use App\Http\Controllers\AppController;
use App\Models\User;
use App\Repositories\ModelRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TimesheetController extends AppController
{
    public function __construct(
        protected ModelRepository $repository = new TimesheetRepository,
        protected ModelRepository $templateRepository = new TimesheetTemplateRepository)
    {
        parent::__construct();
    }

    public function getDateCategories(Request $request)
    {
        $this->authService->allowed(
            Permission::FEATURE_OPERATIONAL,
            Permission::SUB_FEATURES[Permission::FEATURE_OPERATIONAL]['timesheets'],
            Permission::ACTION_VIEW
        );

        $date = $request->get('date');
        $userId = $request->get('user_id');

        return response()->json(
            $this->repository->getDateCategories($date, $userId)
        );
    }

    public function getPeriodDates(Request $request)
    {
        $this->authService->allowed(
            Permission::FEATURE_OPERATIONAL,
            Permission::SUB_FEATURES[Permission::FEATURE_OPERATIONAL]['timesheets'],
            Permission::ACTION_VIEW
        );

        $startDate = $request->get('start_date');
        $period = $request->get('period', 0);
        $startDate = !empty($startDate) ? Carbon::parse($startDate) : now();
        $startDate->addDays($period * 7);

        return response()->json(
            $this->repository->getPeriodDates($startDate)
        );
    }

    public function deleteEntry($id)
    {
        $this->authService->allowed(
            Permission::FEATURE_OPERATIONAL,
            Permission::SUB_FEATURES[Permission::FEATURE_OPERATIONAL]['timesheets'],
            Permission::ACTION_DELETE
        );

        $this->repository->find($id, true)->query->forceDelete();

        return response()->json([], 201);
    }

    public function deleteCategoryEntries(Request $request)
    {
        $this->authService->allowed(
            Permission::FEATURE_OPERATIONAL,
            Permission::SUB_FEATURES[Permission::FEATURE_OPERATIONAL]['timesheets'],
            Permission::ACTION_DELETE
        );

        $startDate = $request->get('start_date');
        $userId = $request->get('user_id');
        $category = $request->get('category');

        $this->repository->deleteCategoryEntries($userId, $category, $startDate);

        return response()->json([], 201);
    }

    public function index(Request $request)
    {
        $this->authService->allowed(
            Permission::FEATURE_OPERATIONAL,
            Permission::SUB_FEATURES[Permission::FEATURE_OPERATIONAL]['timesheets'],
            Permission::ACTION_VIEW
        );

        $userId = $request->get('user_id', Auth::user()->id);
        $user = User::find($userId);
        $periodStart = $this->repository->getPeriodDates(Carbon::now())[0];
        $startDate = $request->get('start_date');
        $startDate = !empty($startDate) ? Carbon::parse($startDate) : Carbon::parse($periodStart);
        $timesheets = $this->repository->useAuthService($this->authService)->timesheets($startDate, $user);

        return response()->json(
            $timesheets
        );
    }

    public function indexTemplate(Request $request)
    {
        $this->authService->allowed(
            Permission::FEATURE_OPERATIONAL,
            Permission::SUB_FEATURES[Permission::FEATURE_OPERATIONAL]['timesheet_templates'],
            Permission::ACTION_VIEW
        );

        return response()->json(
            $this->templateRepository->all()
        );
    }

    public function getSubmittedTimesheets(Request $request)
    {
        $this->authService->allowed(
            Permission::FEATURE_OPERATIONAL,
            Permission::SUB_FEATURES[Permission::FEATURE_OPERATIONAL]['timesheets'],
            Permission::ACTION_VIEW
        );

        return response()->json(
            $this->repository->useAuthService($this->authService)->fetchTimesheetsPerUser(true)
        );
    }

    public function getUnSubmittedTimesheets(Request $request)
    {
        $this->authService->allowed(
            Permission::FEATURE_OPERATIONAL,
            Permission::SUB_FEATURES[Permission::FEATURE_OPERATIONAL]['timesheets'],
            Permission::ACTION_VIEW
        );

        return response()->json(
            $this->repository->useAuthService($this->authService)->fetchTimesheetsPerUser()
        );
    }

    public function getApprovedTimesheets()
    {
        $this->authService->allowed(
            Permission::FEATURE_OPERATIONAL,
            Permission::SUB_FEATURES[Permission::FEATURE_OPERATIONAL]['timesheets'],
            Permission::ACTION_VIEW
        );

        return response()->json(
            $this->repository->useAuthService($this->authService)->fetchTimesheetsPerUser(true, true)
        );
    }

    public function returnWeekPeriod(Request $request)
    {
        $this->authService->allowed(
            Permission::FEATURE_OPERATIONAL,
            Permission::SUB_FEATURES[Permission::FEATURE_OPERATIONAL]['timesheets'],
            Permission::ACTION_UPDATE
        );

        $date = Carbon::parse($request->get('start_date', Carbon::now()->format('Y-m-d')));
        $userId = $request->get('user_id');
        $remarks = $request->get('remarks');

        try {
            $this->repository->returnWeekPeriod($userId, $date, $remarks);
            $status = 200;
            $message = 'Timesheet week period returned!';
        } catch (\Exception $exception) {
            $message = $exception->getMessage();
            $status = $exception->getCode();
        }

        return response()->json([
            'message' => $message
        ], $status);
    }

    public function approveWeekPeriod(Request $request)
    {
         $this->authService->allowed(
            Permission::FEATURE_OPERATIONAL,
            Permission::SUB_FEATURES[Permission::FEATURE_OPERATIONAL]['timesheets'],
            Permission::ACTION_APPROVE
        );

        $date = $request->get('start_date') ? Carbon::parse($request->get('start_date')) : null;
        $userId = $request->get('user_id');

        try {
            $this->repository->useAuthService($this->authService)->approveWeekPeriod($userId, $date);
            $status = 200;
            $message = 'Timesheet week period approved!';

            $fromUser = User::find(Auth::user()->id);
            $this->notification
                ->from($fromUser)
                ->link("/timesheets?tab=approved&user_id={$userId}&date={$date->format('Y-m-d')}")
                ->to([$userId])
                ->notify(
                    "Timesheets",
                    "<b>{$fromUser->employee->first_name} {$fromUser->employee->first_name}</b> has approved your timesheet."
                );

        } catch (\Exception $exception) {
            $message = $exception->getMessage();
            $status =  500;
        }

        return response()->json([
            'message' => $message
        ], $status);
    }

    public function return(Request $request, $id)
    {
        $this->authService->allowed(
            Permission::FEATURE_OPERATIONAL,
            Permission::SUB_FEATURES[Permission::FEATURE_OPERATIONAL]['timesheets'],
            Permission::ACTION_UPDATE
        );

        $timesheet = $this->repository->find($id);

        if (empty($template)) {
            return response()->json([
                'message' => 'Timesheet entry not found.'
            ], 404);
        }

        $remarks = $request->get('remarks');

        $this->repository->use($timesheet)->returnEntry($remarks);

        return response()->json([
            'message' => 'Timesheet entry returned!'
        ]);
    }

    public function store(StoreRequest $request)
    {
        $this->authService->allowed(
            Permission::FEATURE_OPERATIONAL,
            Permission::SUB_FEATURES[Permission::FEATURE_OPERATIONAL]['timesheets'],
            Permission::ACTION_CREATE
        );

        $user_id = $request->get('user_id', Auth::user()->id);
        $category = $request->get('category');
        $date = $request->get('date');
        $start = $request->get('start');
        $end = $request->get('end');
        $break = $request->get('break', 60);
        $comment = $request->get('comment');

        $timesheet = $this->repository->store(
            $user_id,
            $category,
            $date,
            $start,
            $end,
            $break,
            $comment
        );

        return response()->json([
            'timesheet' => $timesheet
        ]);
    }

    public function storeTemplate(Request $request)
    {
        $this->authService->allowed(
            Permission::FEATURE_OPERATIONAL,
            Permission::SUB_FEATURES[Permission::FEATURE_OPERATIONAL]['timesheet_templates'],
            Permission::ACTION_CREATE
        );

        $name = $request->get('name');
        $userId = $request->get('user_id', Auth::user()->id);
        $data = $request->get('data');

        $template = $this->templateRepository->store($userId, $name, $data);

        return response()->json([
            'template' => $template
        ]);
    }

    public function deleteTemplate(Request $request, $id)
    {
        $this->authService->allowed(
            Permission::FEATURE_OPERATIONAL,
            Permission::SUB_FEATURES[Permission::FEATURE_OPERATIONAL]['timesheet_templates'],
            Permission::ACTION_DELETE
        );

        $template = $this->templateRepository->find($id);
        $this->templateRepository->use($template)->delete();

        return response()->json([], 201);
    }

    public function updateTemplate(Request $request, $id)
    {
        $this->authService->allowed(
            Permission::FEATURE_OPERATIONAL,
            Permission::SUB_FEATURES[Permission::FEATURE_OPERATIONAL]['timesheet_templates'],
            Permission::ACTION_UPDATE
        );

        $template = $this->templateRepository->find($id);

        if (empty($template)) {
            return response()->json([
                'message' => 'Template not found.'
            ], 404);
        }

        $name = $request->get('name');
        $data = $request->get('data');

        $template = $this->templateRepository->use($template)->update($name, $data);

         return response()->json([
             'template' => $template
         ]);
    }

    public function update(UpdateRequest $request, $id)
    {
        $this->authService->allowed(
            Permission::FEATURE_OPERATIONAL,
            Permission::SUB_FEATURES[Permission::FEATURE_OPERATIONAL]['timesheets'],
            Permission::ACTION_UPDATE
        );

        $timesheet = $this->repository->find($id);

        if (empty($timesheet)) {
            return response()->json([
                'message' => 'Timesheet not found!'
            ], 404);
        }

        $category = $request->get('category');
        $start = $request->get('start');
        $end = $request->get('end');
        $break = $request->get('break');
        $comment = $request->get('comment');

        $timesheet = $this->repository
            ->use($timesheet)
            ->update(
                $category,
                $start,
                $end,
                $break,
                $comment
            );

        return response()->json([
            'timesheet' => $timesheet->refresh()
        ]);
    }

    public function submit($id)
    {
        $this->authService->allowed(
            Permission::FEATURE_OPERATIONAL,
            Permission::SUB_FEATURES[Permission::FEATURE_OPERATIONAL]['timesheets'],
            Permission::ACTION_SUBMIT
        );

        $timesheet = $this->repository->find($id);

        if (empty($timesheet)) {
            return response()->json([
                'message' => 'Timesheet not found!'
            ], 404);
        }

        $this->repository->use($timesheet)->submit();

        return response()->json([
            'message' => 'Timesheet approved!'
        ]);
    }

    public function submitPeriod(Request $request)
    {
        $this->authService->allowed(
            Permission::FEATURE_OPERATIONAL,
            Permission::SUB_FEATURES[Permission::FEATURE_OPERATIONAL]['timesheets_submit_tab'],
            Permission::ACTION_SUBMIT
        );

        $startDate = $request->get('start_date');
        $startDate = $startDate ? Carbon::parse($startDate) : Carbon::now()->startOf('week')->addDay();
        $userId = Auth::user()->id;

        $success = $this->repository->submitWeekPeriod($userId, $startDate);

        if (!$success) {
            return response()->json([
                'message' => 'No week period timesheet entries found.'
            ], 401);
        }

        $user = User::find($userId);

        $this->notification
            ->from($user)
            ->toManagers()
            ->toSuperUsers()
            ->link("/timesheets?tab=pending&user_id={$user->id}&date={$startDate}")
            ->notify(
                "Timesheets",
                "<b>{$user->employee->first_name} {$user->employee->last_name}</b> submitted a timesheet!"
            );

        return response()->json([
            'message' => 'Week Period Submitted.'
        ]);
    }

    public function approve($id)
    {
        $this->authService->allowed(
            Permission::FEATURE_OPERATIONAL,
            Permission::SUB_FEATURES[Permission::FEATURE_OPERATIONAL]['timesheets'],
            Permission::ACTION_APPROVE
        );

        $timesheet = $this->repository->find($id);

        if (empty($timesheet)) {
            return response()->json([
                'message' => 'Timesheet not found!'
            ], 404);
        }

        $this->repository->use($timesheet)->approve();

        return response()->json([
            'message' => 'Timesheet approved!'
        ]);
    }

    public function applyTemplate(Request $request)
    {
        $this->authService->allowed(
            Permission::FEATURE_OPERATIONAL,
            Permission::SUB_FEATURES[Permission::FEATURE_OPERATIONAL]['timesheets'],
            Permission::ACTION_UPDATE
        );
        $periodStartDate = Carbon::parse($request->get('start_date'))->tz('Australia/Sydney');
        $timesheets = $request->get('timesheets', []);
        $userId = $request->get('user_id') ?? auth()->user()->id;

        $periodDates = $this->repository->getPeriodDates($periodStartDate);
        $this->repository->applyTimesheets($periodDates, $timesheets, $userId);

        return response()->json([
            'message' => 'Timesheet entries applied.'
        ]);
    }

    public function categories()
    {
        $this->authService->allowed(
            Permission::FEATURE_OPERATIONAL,
            Permission::SUB_FEATURES[Permission::FEATURE_OPERATIONAL]['timesheets'],
            Permission::ACTION_VIEW
        );

        return response()->json(
            $this->repository->categories()
        );
    }

    public function delete(Request $request)
    {
        $this->authService->allowed(
            Permission::FEATURE_OPERATIONAL,
            Permission::SUB_FEATURES[Permission::FEATURE_OPERATIONAL]['timesheets'],
            Permission::ACTION_DELETE
        );

        $date = $request->get('date');
        $userId = $request->get('user_id') ?? auth()->user()->id;

        $this->repository->deleteTimesheets($date, $userId);

        return response()->json([], 201);
    }
}

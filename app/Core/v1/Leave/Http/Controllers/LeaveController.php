<?php

namespace App\Core\v1\Leave\Http\Controllers;

use App\Core\v1\Leave\Http\Requests\StoreRequest;
use App\Core\v1\Leave\Http\Requests\UpdateRequest;
use App\Core\v1\Leave\Models\Leave;
use App\Core\v1\Leave\Repositories\LeaveRepository;
use App\Core\v1\Permission\Models\Permission;
use App\Http\Controllers\AppController;
use App\Models\User;
use App\Repositories\ModelRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LeaveController extends AppController
{
    public function __construct(protected ModelRepository $repository = new LeaveRepository)
    {
        parent::__construct();
    }

    public function getLeaveDetails(Request $request)
    {
        $this->authService->allowed(
            Permission::FEATURE_PEOPLE,
            Permission::SUB_FEATURES[Permission::FEATURE_PEOPLE]['leave_management'],
            Permission::ACTION_VIEW
        );

        $userId = $request->get('user_id');
        $batch = $request->get('batch');

        return response()->json(
            $this->repository->query->where('user_id', $userId)->where('batch', $batch)->get()
        );
    }

    public function index(Request $request)
    {
        $this->authService->allowed(
            Permission::FEATURE_PEOPLE,
            Permission::SUB_FEATURES[Permission::FEATURE_PEOPLE]['leave_management_leave_request'],
            Permission::ACTION_VIEW
        );

        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 5);
        $queryName = $request->get('query');
        $type = $request->get('type');
        $division = $request->get('division');
        $location = $request->get('location');
        $status = $request->get('status');
        $userId = $request->get('user_id');
        $showAll = $request->boolean('show_all');
        $historyMode = $request->boolean('history_mode');
        $batch = $request->get('batch');
        $dateStart = $request->get('date_start');
        $dateEnd = $request->get('date_end');

        $query =  $this->repository
            ->useAuthService($this->authService)
            ->fetchFormattedLeaves();

        if ($historyMode) {
            $query = $query->filter([
                'leaves.status' => [ Leave::STATUS_APPROVED, Leave::STATUS_DECLINED]
            ], true, true, 'or');
        }

        if (!empty($queryName)) {
            $query = $query->filter([
                'employees.first_name' => $queryName,
                'employees.last_name' => $queryName
            ], true, true, 'or');
        }

        if (!empty($type)) {
            $query = $query->filter([
                'type' => $type,
            ]);
        }

        if (!empty($division)) {
            $query = $query->filter([
                'employees.division_id' => $division,
            ]);
        }

        if (!empty($location)) {
            $query = $query->filter([
                'employees.location_id' => $location,
            ]);
        }

        if (!empty($status)) {
            $query = $query->filter([
                'leaves.status' => $status,
            ], true, true);
        }

        if (!empty($userId))  {
            $query = $query->filter([
                'leaves.user_id' => $userId
            ]);
        }

        if (!empty($batch)) {
            $query = $query->filter([
                'leaves.batch' => $batch
            ]);
        }

        // Add date range filtering for overlapping leaves
        if (!empty($dateStart) && !empty($dateEnd)) {
            $query->query->where(function($q) use ($dateStart, $dateEnd) {
                $q->where(function($inner) use ($dateStart, $dateEnd) {
                    // Leaves that have at least one day within the date range
                    $inner->where('date', '>=', $dateStart)
                        ->where('date', '<=', $dateEnd);
                })->orWhere(function($inner) use ($dateStart, $dateEnd) {
                    // Leaves that span the date range (start before and end after)
                    $inner->where('date', '<=', $dateStart)
                        ->where('date', '>=', $dateEnd);
                });
            });
        }

        $total = $query->count();

        if ($showAll) {
            $leaves = $query->query->get();
        } else {
            $leaves = $query->paginate($perPage)
                ->page($page)
                ->orderBy('updated_at', 'desc')->get();
        }

        return response()->json(
        [
            'total' => $total,
            'leaves' => $leaves
        ]
        );
    }

    public function getCalendarData(Request $request)
    {
        $this->authService->allowed(
            Permission::FEATURE_PEOPLE,
            Permission::SUB_FEATURES[Permission::FEATURE_PEOPLE]['leave_management'],
            Permission::ACTION_VIEW
        );

        $queryName = $request->get('query');
        $type = $request->get('type');
        $division = $request->get('division');
        $location = $request->get('location');
        $status = $request->get('status');
        $month = $request->get('month');
        $statuses = [];

        if (!empty($status)) {
            $statuses = [$status];
        }

        $query =  $this->repository
            ->useAuthService($this->authService)
            ->fetchFormattedLeaves($statuses);

        if (!empty($month)) {
            $monthStart = Carbon::now()->setMonth($month)->startOfMonth()->format('Y-m-d');
            $monthEnd = Carbon::now()->setMonth($month)->endOfMonth()->format('Y-m-d');
            $query = $query->filterDate('date', $monthStart, '>=');
            $query = $query->filterDate('date', $monthEnd, '<=');
        }

        if (!empty($queryName)) {
            $query = $query->filter([
                'employees.first_name' => $queryName,
                'employees.last_name' => $queryName
            ], true, true, 'or');
        }

        if (!empty($type)) {
            $query = $query->filter([
                'type' => $type,
            ]);
        }

        if (!empty($division)) {
            $query = $query->filter([
                'employees.division_id' => $division,
            ], true, true);
        }

        if (!empty($location)) {
            $query = $query->filter([
                'employees.location_id' => $location,
            ], true, true);
        }

        if (!empty($status)) {
            $query = $query->filter([
                'leaves.status' => $status,
            ], true, true);
        }

        $results =  $query->orderBy('users.id', 'desc')->query->get();
        $calendarData = $this->repository->generateCalendarData($results);

        return response()->json($calendarData);
    }

    public function getCredits(Request $request)
    {
        $this->authService->allowed(
            Permission::FEATURE_PEOPLE,
            Permission::SUB_FEATURES[Permission::FEATURE_PEOPLE]['leave_management'],
            Permission::ACTION_VIEW
        );

        $userId = $request->get('user_id');
        $user = User::find($userId);

        return response()->json($this->repository->fetchLeaveCredits($user));
    }

    public function store(StoreRequest $request)
    {
        $permission = $request->get('request_permission', 'leave_management');

        $this->authService->allowed(
            Permission::FEATURE_PEOPLE,
            Permission::SUB_FEATURES[Permission::FEATURE_PEOPLE][$permission],
            Permission::ACTION_CREATE
        );

        $userId = $request->get('user_id');
        $approvers = json_decode($request->get('approvers'));
        $type = $request->get('type');
        $start = $request->get('start');
        $end = $request->get('end');
        $reason = $request->get('reason');
        $exemption = $request->boolean('exemption');
        $details = json_decode($request->get('details'));
        $attachment = $request->hasFile('attachment') ? $request->file('attachment') : null;
        $attachmentPath = null;
        $attachmentName = null;

        if ($attachment) {
            $attachmentPath = $attachment->store('leave_attachments');
            $attachmentName = $attachment->getClientOriginalName();
        }

        $this->repository->store(
            $userId,
            $type,
            $start,
            $end,
            [$approvers],
            $reason,
            null,
            $details,
            $attachmentPath,
            $attachmentName,
            $exemption
        );

        return response()->json();
    }

    public function approve(Request $request)
    {
        $this->authService->allowed(
            Permission::FEATURE_PEOPLE,
            Permission::SUB_FEATURES[Permission::FEATURE_PEOPLE]['leave_management_leave_request'],
            Permission::ACTION_APPROVE
        );

        $userId = $request->get('user_id');
        $batch = $request->get('batch');
        $type = $request->get('type');

        $this->repository->useAuthService($this->authService)->approve($userId, $batch, $type);

        return response()->json();
    }

    public function decline(Request $request)
    {
        $this->authService->allowed(
            Permission::FEATURE_PEOPLE,
            Permission::SUB_FEATURES[Permission::FEATURE_PEOPLE]['leave_management'],
            Permission::ACTION_UPDATE
        );

        $userId = $request->get('user_id');
        $batch = $request->get('batch');

        $this->repository->decline($userId, $batch);

        return response()->json();
    }

    public function cancel(Request $request)
    {
        $this->authService->allowed(
            Permission::FEATURE_PEOPLE,
            Permission::SUB_FEATURES[Permission::FEATURE_PEOPLE]['leave_management'],
            Permission::ACTION_UPDATE
        );

        $batch = $request->get('batch');
        $userId = $request->get('user_id');

        $this->repository->cancel($userId, $batch);

        return response()->json();
    }

    public function update(UpdateRequest $request)
    {
        $this->authService->allowed(
            Permission::FEATURE_PEOPLE,
            Permission::SUB_FEATURES[Permission::FEATURE_PEOPLE]['leave_management'],
            Permission::ACTION_UPDATE
        );

        $userId = $request->get('user_id');
        $batch = $request->get('batch');
        $approvers = [json_decode(json_encode($request->get('approvers')))];
        $type = $request->get('type');
        $start = $request->get('start');
        $end = $request->get('end');
        $reason = $request->get('reason');
        $details = json_decode($request->get('details'));

        $this->repository->update(
            $batch,
            $userId,
            $type,
            $start,
            $end,
            $approvers,
            $reason,
            $details
        );

        return response()->json();
    }

    public function getStatuses()
    {
        $this->authService->allowed(
            Permission::FEATURE_PEOPLE,
            Permission::SUB_FEATURES[Permission::FEATURE_PEOPLE]['leave_management'],
            Permission::ACTION_VIEW
        );

        $statuses = [];

        foreach(Leave::statuses() as $id => $label) {
            $t = new \stdClass();
            $t->id = $id;
            $t->name = $label;
            $statuses[] = $t;
        }

        return response()->json($statuses);
    }

    public function delete(Request $request)
    {
        $userId = $request->get('user_id');
        $batch = $request->get('batch');

        $this->repository->newQuery()->query->where('user_id', $userId)->where('batch', $batch)->forceDelete();

        return response()->json([], 201);
    }

    public function getTypes(Request $request)
    {
        $this->authService->allowed(
            Permission::FEATURE_PEOPLE,
            Permission::SUB_FEATURES[Permission::FEATURE_PEOPLE]['leave_management'],
            Permission::ACTION_VIEW
        );

        $userId = $request->get('user_id', auth()->user()->id);
        $types = [];

        foreach(Leave::types($userId) as $id => $label) {
            $t = new \stdClass();
            $t->id = $id;
            $t->name = $label;
            $types[] = $t;
        }

        return response()->json($types);
    }
}

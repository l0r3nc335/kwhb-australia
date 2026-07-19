<?php

namespace App\Core\v1\Leave\Repositories;

use App\Core\v1\Leave\Models\Leave;
use App\Core\v1\Leave\Models\LeaveCredit;
use App\Core\v1\Notification\Repositories\NotificationRepository;
use App\Models\User;
use App\Repositories\ModelRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class LeaveRepository extends ModelRepository
{
    protected $notification;

    public function __construct()
    {
        parent::__construct();

        $this->notification = new NotificationRepository;
    }

    public function model(): Model
    {
        return new Leave;
    }

    public function fetchLeaveCredits(User $user): array
    {
        $credits = [];

        $types = Leave::types();

        foreach ($types as $id => $name) {
            $c = new \stdClass();
            $c->id = $id;
            $c->name = $name;
            $c->credits = optional($user->credits()->where('type', $id)->first())->credits ?? 0;
            $credits[] = $c;
        }

        return $credits;
    }

    public function generateCalendarData(Collection $leaves): array
    {
        $userLeaves = [];
        $data = [];

        foreach ($leaves as $leave) {
            if (isset($userLeaves[$leave->user_id])) {
                $userLeaves[$leave->user_id][] = $leave;
            } else {
                $userLeaves[$leave->user_id] = [$leave];
            }
        }

        foreach ($userLeaves as $userLeave) {
            $leave = new \stdClass;
            $leave->name = $userLeave[0]->name;
            $leave->status = $userLeave[0]->status_name;
            $leave->type = $userLeave[0]->leave_type_name;
            $leave->leaves = $userLeave;
            $data[] = $leave;
        }

        return $data;
    }

    public function fetchFormattedLeaves(array $status = [])
    {
        $this->query = $this->model()
            ->newQuery()
            ->with(['employee'])
            ->select([
                'batch',
                'description',
                DB::raw('users.id as user_id'),
                DB::raw('sum(total) as total'),
                DB::raw('max(type) as type'),
                DB::raw('max(attachment) as attachment'),
                DB::raw('max(attachment_name) as attachment_name'),
                DB::raw('max(leaves.status) as status'),
                DB::raw('min(date) as start_date'),
                DB::raw('max(date) as end_date'),
                DB::raw('max(request_exemption) as request_exemption'),
                DB::raw('max(is_endorsed) as is_endorsed'),
                DB::raw("max(concat(employees.first_name, ' ', employees.last_name)) as name"),
                DB::raw('count(date) as day_count'),
                DB::raw('max(leaves.updated_at) as updated_at'),
            ])
            ->join('users', 'users.id', '=', 'leaves.user_id')
            ->join('employees', 'users.id', '=', 'employees.user_id')
            ->groupBy(['users.id', 'batch', 'description']);

        if (count($status) > 0) {
            $this->query = $this->query->whereIn('leaves.status', $status);
        }

        $this->query = $this->authService->scopedQuery($this->query, 'leaves.user_id');

        return $this;
    }

    public function store(
        int $userId,
        int $type,
        string $startDate,
        string $endDate,
        array $approvers,
        string $description = null,
        int $batch = null,
        array $details = [],
        string $attachment = null,
        string $attachmentName = null,
        bool $exemption = false,
        bool $isUpdate = false
    ): void
    {
        $user = User::find($userId);
        $dates = $this->getDates($startDate, $endDate);
        $batch = $batch ?? $this->getBatch($user);
        $leaveDetails = collect($details);
        $notifyUsers = [];

        foreach ($dates as $date) {
            $detail = $leaveDetails->where('date', $date)->first();
            $leave = $this->model();
            $leave->user_id = $userId;
            $leave->type = $type;
            $leave->date = $date;
            $leave->batch = $batch;
            $leave->status = Leave::STATUS_PENDING;
            $leave->description = $description;
            $leave->notes = optional($detail)->notes;
            $leave->total = optional($detail)->total;
            $leave->attachment = $attachment;
            $leave->attachment_name = $attachmentName;
            $leave->request_exemption = $exemption;
            $leave->save();

            foreach ($approvers as $approver) {
                $approverId = is_object($approver) ? $approver->user_id : ((is_array($approver) ? $approver[0]->user_id : $approver));
                $notifyUsers[] = $approverId;
                DB::table('leave_approvers')->insert([
                    'user_id' => $userId,
                    'approver_id' => $approverId,
                    'leave_batch' => $leave->batch
                ]);
            }

            if (!$isUpdate) {
                // For managers and assigned user
                $this->notification
                    ->from($user)
                    ->to($notifyUsers)
                    ->toManagers()
                    ->notify(
                        'Leave Request Submitted',
                        "<b>{$user->employee->first_name} {$user->employee->last_name}</b> has submitted a leave request for your approval."
                    );
                // For user
                $this->notification
                    ->from($user)
                    ->to([$user->id])
                    ->notify(
                        'Leave Request Submitted',
                        "You have submitted a leave request."
                    );
            } else {
                // For managers and assigned user
                $this->notification
                    ->from($user)
                    ->to($notifyUsers)
                    ->toManagers()
                    ->notify(
                        'Leave Request Updated',
                        "<b>{$user->employee->first_name} {$user->employee->last_name}</b>'s leave request has been updated."
                    );
                // For user
                $this->notification
                    ->from($user)
                    ->to([$user->id])
                    ->notify(
                        'Leave Request Updated',
                        "Your leave request has been updated."
                    );
            }
        }
    }

    public function update(
        int $batch,
        int $userId,
        int $type,
        string $startDate,
        string $endDate,
        array $approvers,
        string $description = null,
        array $details = []
    ): void
    {
       $this->deleteLeaveBatch($userId, $batch);

       $this->store($userId, $type, $startDate, $endDate, $approvers, $description, $batch, $details, null, null, false, true);
    }

    public function deleteLeaveBatch(int $userId, int $batch): void
    {
        DB::table('leave_approvers')->where('user_id', $userId)->where('leave_batch', $batch)->delete();
        $this->newQuery()->query->where('user_id', $userId)->where('batch', $batch)->delete();
    }

    public function approve(int $userId, int $batch, int $type): void
    {
        $authUser = User::find(auth()->user()->id);

        DB::table('leave_approvers')
            ->where('approver_id', $authUser->id)
            ->where('user_id', $userId)
            ->where('leave_batch', $batch)
            ->update([
                'is_approved' => true
            ]);

        $query = $this->newQuery(true);

        if (in_array($this->authService->role->name, ['CEO', 'General Manager', 'Super User'])) {
            $query->query->where('batch', $batch)->where('user_id', $userId)->update([
                'status' => Leave::STATUS_APPROVED
            ]);
        } else {
            $query->query->where('batch', $batch)->where('user_id', $userId)->update([
                'is_endorsed' => true
            ]);
        }

        $subCred = $this->newQuery()->query->select([DB::raw('sum(total) as total')])->where('batch', $batch)->where('user_id', $userId)->first();
        $spentCredits = optional($subCred)->total ?? 0;
        $credit = LeaveCredit::query()->where('user_id', $userId)->where('type', $type)->first();

        if ($credit && $credit->credits > $spentCredits) {
            $credit->credits = $credit->credits - $spentCredits;
            $credit->save();
        }

        $this->notification
            ->from($authUser)
            ->to([$userId])
            ->notify(
                'Leave Approval',
                'Your leave request has been approved.'
            );
    }

    public function decline(int $userId, int $batch)
    {
        $authUser = User::find(auth()->user()->id);

        $this->model()->newQuery()->where('user_id', $userId)->where('batch', $batch)->update([
            'status' => Leave::STATUS_DECLINED
        ]);

        DB::table('leave_approvers')
            ->where('approver_id', $authUser->id)
            ->where('user_id', $userId)
            ->where('leave_batch', $batch)
            ->update([
                'is_declined' => true
            ]);

        $this->notification
            ->from($authUser)
            ->to([$userId])
            ->notify(
                'Leave Declined',
                'Your leave request has been declined.'
            );
    }

    public function cancel(int $userId, int $batch): void
    {
        $authUser = User::find(auth()->user()->id);

        $this->newQuery()->query
            ->where('user_id', $userId)
            ->where('batch', $batch)
            ->update([
                'status' => Leave::STATUS_CANCELLED
            ]);

        $this->notification
            ->from($authUser)
            ->to([$userId])
            ->notify(
                'Leave Cancelled',
                'Your leave request has been cancelled.'
            );
    }

    public function getDates(string $startDate, string $endDate)
    {
        $dates = [];
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $daysDiff = $start->diffInDays($end) + 1;

        if ($daysDiff > 0) {
            for($i = 0; $i < $daysDiff; $i++) {
                $dates[] = Carbon::parse($start->format('Y-m-d'))->addDays($i)->format('Y-m-d');
            }
        } else {
            $dates[] = $start->format('Y-m-d');
        }

        return $dates;
    }

    private function getBatch(User $user)
    {
        $batch =  Leave::query()
            ->select(
               'batch'
            )
            ->where('user_id', $user->id)
            ->orderBy('batch', 'desc')
            ->first();

        if (!empty($batch)) {
            return $batch->batch + 1;
        }

        return 1;
    }
}

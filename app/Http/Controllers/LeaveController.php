<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Leave;
use App\Models\LeaveDetail;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use App\Helpers\DataResponse;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Auth;
use stdClass;

class LeaveController extends Controller
{
    public function index()
    {
        return response()->json($this->getLeave());
    }

    public function create()
    {
        //
    }

    public function getLeave()
    {
        $tbl = Leave::with(['leave_type', 'employee', 'attachments', 'leave_details', 'approver'])->get();
        return $tbl;
    }

    public function store(Request $request)
    {

        $dataResponse = new DataResponse();
        $user_cred = Auth::user();
        try {
            DB::beginTransaction();
            $date_from = Carbon::createFromFormat('d/m/Y', $request->date_from)->format('Y-m-d');
            $date_to = Carbon::createFromFormat('d/m/Y', $request->date_to)->format('Y-m-d');
            $requestData = $request->all();
            $requestData['date_from'] = $date_from;
            $requestData['date_to'] = $date_to;
            $requestData['date_filed'] = Carbon::now();
            $data = Leave::create($requestData);

            //store attachments
            $files = $request->attachments;
            if ($files)
                for ($i = 0; $i < count($files); $i++) {
                    $attachment = new stdClass;
                    $attachment->attachment = $files[$i];
                    $attachment->path = "leave_attachments";
                    $attachment->name = "leave_attachments";
                    $attachment->source = "leave";
                    $attachment->source_id = $data->id;
                    $AttachmentController = "App\Http\Controllers\AttachmentController";
                    app($AttachmentController)->saveAttachment($attachment);
                }

            //store leave details
            $leave_details = $request->leave_details;
            foreach ($leave_details as $detail) {
                $leave_date = Carbon::createFromFormat('d/m/Y', $detail['date'])->format('Y-m-d');

                $leave_detail = new LeaveDetail;
                $leave_detail->leave_id = $data->id;
                $leave_detail->leave_date = $leave_date;
                $leave_detail->day_name = $detail['day'];
                $leave_detail->hour = $detail['hours'];
                $leave_detail->save();
            }

            DB::table('logs')->insert([
                'user_id' => $user_cred->id,
                'function_name' => 'store',
                'log_type' => 'Message',
                'action' => 'Create Leave',
                'ip_address' => Helper::instance()->getIp(),
                'data' => $data,
            ]);
            //
            // $dataResponse->data = $this->getLeave();
            $dataResponse->data = "data";
            $dataResponse->message = 'Success';
            DB::commit();
            return response()->json($dataResponse, 200);
        } catch (Exception $ex) {

            DB::rollBack();
            DB::table('logs')->insert([
                'user_id' => $user_cred->id,
                'function_name' => 'store',
                'log_type' => 'Error',
                'action' => 'Create Leave',
                'ip_address' => Helper::instance()->getIp(),
                'data' => $ex->getMessage(),
            ]);
            $dataResponse->ErrorResponse($ex);
            return response()->json($dataResponse, 500);
            //return response()->json(['error' => $ex->getMessage()], 500);
        }
    }


    public function show($employee_id)
    {
        $dataResponse = new DataResponse();

        try {
            $employee = Employee::find($employee_id);
            $tbl = Leave::with(['leave_type', 'employee', 'attachments', 'leave_details', 'approver'])->where("user_id", $employee->user_id)->get();
            $dataResponse->data = $tbl;
            $dataResponse->message = 'Success';
            return response()->json($dataResponse, 200);
        } catch (Exception $ex) {
            $dataResponse->ErrorResponse($ex);
            return response()->json($dataResponse, 500);
        }
    }

    public function edit(Leave $Leave)
    {
        //
    }

    public function update(Request $request, $id)
    {
        $user_cred = Auth::user();
        try {
            $cmd  = Leave::findOrFail($id);
            $logFrom = $cmd->replicate();
            $input = $request->all();

            $cmd->fill($input)->save();
            $logTo = $cmd;
            DB::table('logs')->insert([
                'user_id' => $user_cred->id,
                'function_name' => 'update',
                'log_type' => 'Message',
                'action' => 'Update Leave',
                'ip_address' => Helper::instance()->getIp(),
                'data' => "From: " . $logFrom . "\r\nTo: " . $logTo,
            ]);
            return $this->getLeave();
        } catch (Exception $ex) {
            DB::table('logs')->insert([
                'user_id' => $user_cred->id,
                'function_name' => 'update',
                'log_type' => 'Error',
                'action' => 'Update Leave',
                'ip_address' => Helper::instance()->getIp(),
                'data' => $ex->getMessage(),
            ]);
            return response()->json(['error' => $ex->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $user_cred = Auth::user();
        try {
            $tbl = Leave::findOrFail($id);
            Leave::destroy($id);


            DB::table('logs')->insert([
                'user_id' => $user_cred->id,
                'function_name' => 'destroy',
                'log_type' => 'Message',
                'action' => 'Delete Leave',
                'ip_address' => Helper::instance()->getIp(),
                'data' => $tbl,
            ]);
            return $this->getLeave();
        } catch (Exception $ex) {
            DB::table('logs')->insert([
                'user_id' => $user_cred->id,
                'function_name' => 'destroy',
                'log_type' => 'Error',
                'action' => 'Delete Leave',
                'ip_address' => Helper::instance()->getIp(),
                'data' => $ex->getMessage(),
            ]);
            return response()->json(['error' => 'There was a problem deleting this Leave.'], 500);
        }
    }

    public function fetchLeaveByApprover($employee_id)
    {
        $dataResponse = new DataResponse();
        $user_cred = Auth::user();

        try {
            $tbl = Leave::with(['leave_type', 'employee', 'attachments', 'leave_details', 'approver'])
                ->where("approver_id", $employee_id)
                ->where("status", "Pending")
                ->get();
            $dataResponse->data = $tbl;
            $dataResponse->message = 'Success';
            return response()->json($dataResponse, 200);
        } catch (Exception $ex) {
            $dataResponse->ErrorResponse($ex);
            return response()->json($dataResponse, 500);
        }
    }

    public function update_row(Request $request)
    {
        $user_cred = Auth::user();
        $selectedRole = $request->get('selectedRole');
        $dataResponse = new DataResponse();
        try {
            $tbl = Leave::where("id", $request->id);
            $oldData = $tbl->select($request->row)->get();
            $update = $tbl->update([$request->row => $request->data]);
            $newData = $tbl->select($request->row)->get();

            DB::table('logs')->insert([
                //'user_id' => 1,
                'user_id' => $user_cred->id,
                'function_name' => 'update_row',
                'log_type' => 'Message',
                'action' => 'Update Leave ' . $request->row,
                'ip_address' => Helper::instance()->getIp(),
                'data' => "Update Leave id " . $request->id . "\nRow: " . $request->row .
                    "\nFrom: " . $oldData . "\nTo: " . $newData,
                'created_at' => Carbon::now(),
            ]);

            $dataResponse->data = $tbl->get();
            $dataResponse->message = 'Success';
            return response()->json($dataResponse, 200);
        } catch (\Exception $ex) {
            DB::table('logs')->insert([
                //'user_id' => 1,
                'user_id' => $user_cred->id,
                'function_name' => 'update_row',
                'log_type' => 'Error',
                'action' => 'Update Leave ' . $request->row,
                'ip_address' => Helper::instance()->getIp(),
                'data' => $ex->getMessage(),
                'created_at' => Carbon::now(),
            ]);
            $dataResponse->ErrorResponse($ex);
            return response()->json($dataResponse, 200);
        }
    }

    public function multipleFilter(Request $request)
    {
        $dataResponse = new DataResponse();
        $user_cred = Auth::user();
        try {

            $filter = (object) $request->filter;
            $data = (object) $request->data;
            $tbl = Leave::with(['leave_type', 'employee', 'attachments', 'leave_details', 'approver']);

            if ($filter->leave_type)
                if ($data->leave_type_id != "all")
                    $tbl->where("leave_type_id", $data->leave_type_id);
            if ($filter->division)
                if ($data->division_id != "all")
                    $tbl->whereHas("employee", function ($query) use ($data) {
                        $query->where("division_id", $data->division_id);
                    });
            if ($filter->location)
                if ($data->location_id != "all")
                    $tbl->whereHas("employee", function ($query) use ($data) {
                        $query->where("division_id", $data->location_id);
                    });
            if ($filter->status)
                if ($data->status != "all")
                    $tbl->where("status", $data->status);

            if ($filter->filter_date) {
                $filter_date = (object) $data->filter_date;
                $from = new Carbon($filter_date->from);
                $to = new Carbon($filter_date->to);
                $tbl->whereBetween("date_from", [$from->toDateString(), $to->toDateString()]);
            }
            if ($filter->search_name)
                $tbl->whereHas("employee", function ($query) use ($data) {

                    $query->where(function ($query1) use ($data) {
                        $query1->where('first_name', 'like', '%' . $data->search_name . '%')
                            ->orWhere('last_name', 'like', '%' . $data->search_name . '%');
                    });
                });


            $dataResponse->data = $tbl->get();
            $dataResponse->message = 'Success';
            return response()->json($dataResponse, 200);
        } catch (Exception $ex) {
            $dataResponse->ErrorResponse($ex);
            return response()->json($dataResponse, 500);
        }
    }
}

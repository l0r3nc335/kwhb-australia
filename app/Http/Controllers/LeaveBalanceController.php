<?php

namespace App\Http\Controllers;

use App\Models\LeaveBalance;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use App\Helpers\DataResponse;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LeaveBalanceController extends Controller
{

    private $user;
    public function __construct()
    {
        $this->user = Auth::user();
    }
    public function index()
    {
        $tbl = LeaveBalance::all();
        return response()->json($tbl);
    }

    public function create()
    {
        //
    }


    public function store(Request $request)
    {
        try {
            $data = LeaveBalance::create($request->all());

            DB::table('logs')->insert([
                'user_id' => $this->user->id,
                'function_name' => 'store',
                'log_type' => 'Message',
                'action' => 'Create LeaveBalance',
                'ip_address' => Helper::instance()->getIp(),
                'data' => $data,
            ]);
            return $this->index();
        } catch (Exception $ex) {
            DB::table('logs')->insert([
                'user_id' => $this->user->id,
                'function_name' => 'store',
                'log_type' => 'Error',
                'action' => 'Create LeaveBalance',
                'ip_address' => Helper::instance()->getIp(),
                'data' => $ex->getMessage(),
            ]);
            return response()->json(['error' => $ex->getMessage()], 500);
        }
    }


    public function show(LeaveBalance $LeaveBalance)
    {
        //
    }

    public function edit(LeaveBalance $LeaveBalance)
    {
        //
    }

    public function update(Request $request, $id)
    {
        try {
            $cmd  = LeaveBalance::findOrFail($id);
            $logFrom = $cmd->replicate();
            $input = $request->all();

            $cmd->fill($input)->save();
            $logTo = $cmd;
            DB::table('logs')->insert([
                'user_id' => $this->user->id,
                'function_name' => 'update',
                'log_type' => 'Message',
                'action' => 'Update LeaveBalance',
                'ip_address' => Helper::instance()->getIp(),
                'data' => "From: " . $logFrom . "\r\nTo: " . $logTo,
            ]);
            return $this->index();
        } catch (Exception $ex) {
            DB::table('logs')->insert([
                'user_id' => $this->user->id,
                'function_name' => 'update',
                'log_type' => 'Error',
                'action' => 'Update LeaveBalance',
                'ip_address' => Helper::instance()->getIp(),
                'data' => $ex->getMessage(),
            ]);
            return response()->json(['error' => $ex->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $tbl = LeaveBalance::findOrFail($id);
            LeaveBalance::destroy($id);


            DB::table('logs')->insert([
                'user_id' => $this->user->id,
                'function_name' => 'destroy',
                'log_type' => 'Message',
                'action' => 'Delete LeaveBalance',
                'ip_address' => Helper::instance()->getIp(),
                'data' => $tbl,
            ]);
            return $this->index();
        } catch (Exception $ex) {
            DB::table('logs')->insert([
                'user_id' => $this->user->id,
                'function_name' => 'destroy',
                'log_type' => 'Error',
                'action' => 'Delete LeaveBalance',
                'ip_address' => Helper::instance()->getIp(),
                'data' => $ex->getMessage(),
            ]);
            return response()->json(['error' => 'There was a problem deleting this LeaveBalance.'], 500);
        }
    }

    public function fetchLeaveBalance(Request $request)
    {

        $dataResponse = new DataResponse();

        $data = LeaveBalance::where('employee_id', $request->employee_id)
            ->where('leave_type_id', $request->leave_type_id)
            //->where('enroll_year', Carbon())
            ->first();

        $dataResponse->data = $data;
        $dataResponse->message = 'Success';
        return response()->json($dataResponse, 200);
    }
}

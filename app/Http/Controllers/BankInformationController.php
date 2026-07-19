<?php

namespace App\Http\Controllers;

use App\Core\v1\Activitylog\Repositories\ActivitylogRepository;
use App\Core\v1\Notification\Repositories\NotificationRepository;
use App\Models\BankInformation;
use App\Models\User;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use App\Helpers\DataResponse;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BankInformationController extends Controller
{
    protected NotificationRepository $notification;

    public function __construct()
    {
        $this->notification = new NotificationRepository;
    }

    public function index()
    {
        $tbl = BankInformation::all();
        return response()->json($tbl);
    }

    public function create()
    {
        //
    }


    public function store(Request $request)
    {
        $user_id = Auth::user()->id;
        $dataResponse = new DataResponse();

        DB::beginTransaction();
        try {
            $temp = $request->primary;
            $emp_id = $request->employee_id;
            $reimbursement = $request->reimbursement;
            if ($temp)
                BankInformation::where('employee_id', $emp_id)->update(["primary" => false]);
            if ($reimbursement)
                BankInformation::where('employee_id', $emp_id)->update(["reimbursement" => false]);

            $request['primary'] = $request->primary ?? false;
            $request['reimbursement'] = $request->reimbursement ?? false;
            $data = BankInformation::create($request->all());

            DB::table('logs')->insert([
                'user_id' => $user_id,
                'function_name' => 'store',
                'log_type' => 'Message',
                'action' => 'Create BankInformation',
                'ip_address' => Helper::instance()->getIp(),
                'data' => $data,
            ]);
            //
            DB::commit();
            $dataResponse->data = $this->show($emp_id);
            $dataResponse->message = 'Success';

            //activity logs
            $user = User::whereHas('employee', function ($query) use ($emp_id) {
                $query->where('id', $emp_id);
            })->first();
            $log = new ActivitylogRepository($user, 'Bank Details Added', 'Bank details were added to the employee profile', 1);
            $log->info(1, 1, null, null);

            return response()->json($dataResponse, 200);
        } catch (Exception $ex) {
            DB::rollBack();
            DB::table('logs')->insert([
                'user_id' => $user_id,
                'function_name' => 'store',
                'log_type' => 'Error',
                'action' => 'Create BankInformation',
                'ip_address' => Helper::instance()->getIp(),
                'data' => $ex->getMessage(),
            ]);
            $dataResponse->ErrorResponse($ex);
            return response()->json($dataResponse, 500);
            //return response()->json(['error' => $ex->getMessage()], 500);
        }
    }


    public function show($emp_id)
    {
        $tbl = BankInformation::where('employee_id', $emp_id)->orderBy('primary', 'Desc')->get();
        return $tbl;
    }

    // public function edit(Employee $Employee)
    // {
    //     //
    // }

    public function update(Request $request, $id)
    {
        $user_id = Auth::user()->id;
        $dataResponse = new DataResponse();

        DB::beginTransaction();
        try {

            $temp = $request->primary;
            $emp_id = $request->employee_id;
            $reimbursement = $request->reimbursement;
            if ($temp)
                BankInformation::where('employee_id', $emp_id)->update(["primary" => false]);
            if ($reimbursement)
                BankInformation::where('employee_id', $emp_id)->update(["reimbursement" => false]);

            $cmd  = BankInformation::findOrFail($id);
            $logFrom = $cmd->replicate();
            $input = $request->all();

            $cmd->fill($input)->save();


            BankInformation::query()
                ->where('employee_id', $emp_id)
                ->update([
                    'pay_split' => $cmd->pay_split
                ]);

            $logTo = $cmd;
            DB::table('logs')->insert([
                'user_id' => $user_id,
                'function_name' => 'update',
                'log_type' => 'Message',
                'action' => 'Update BankInformation',
                'ip_address' => Helper::instance()->getIp(),
                'data' => "From: " . $logFrom . "\r\nTo: " . $logTo,
            ]);

            DB::commit();
            $dataResponse->data = $this->show($emp_id);;
            $dataResponse->message = 'Success';

            //activity logs
            $user = User::whereHas('employee', function ($query) use ($emp_id) {
                $query->where('id', $emp_id);
            })->first();
            $log = new ActivitylogRepository($user, 'Bank Details Changed', 'Bank details were changed', 1);
            $log->info(1, 1, null, null);

            // Manager, HR
            $this->notification
                ->from($user)
                ->toHr()
                ->toManagers()
                ->notify(
                    "Bank Details Change Confirmation",
                    "<b>{$user->employee->first_name} {$user->employee->first_name}</b>'s bank details have been updated."
                );
            // User
            $this->notification
                ->from($user)
                ->to([$user->id])
                ->notify(
                    "Bank Details Change Confirmation",
                    "Your bank details have been updated."
                );

            return response()->json($dataResponse, 200);
            //return $this->index();
        } catch (Exception $ex) {
            DB::rollBack();
            DB::table('logs')->insert([
                'user_id' => $user_id,
                'function_name' => 'update',
                'log_type' => 'Error',
                'action' => 'Update BankInformation',
                'ip_address' => Helper::instance()->getIp(),
                'data' => $ex->getMessage(),
            ]);
            $dataResponse->ErrorResponse($ex);
            return response()->json($dataResponse, 500);
            //return response()->json(['error' => $ex->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $user_id = Auth::user()->id;
        $dataResponse = new DataResponse();

        DB::beginTransaction();
        try {
            $tbl = BankInformation::findOrFail($id);
            $emp_id = $tbl->employee_id;

            DB::table('logs')->insert([
                'user_id' => $user_id,
                'function_name' => 'destroy',
                'log_type' => 'Message',
                'action' => 'Delete BankInformation',
                'ip_address' => Helper::instance()->getIp(),
                'data' => $tbl,
            ]);

            BankInformation::destroy($id);

            DB::commit();
            $dataResponse->data = $this->show($emp_id);
            $dataResponse->message = 'Success';

            //activity logs
            $user = User::whereHas('employee', function ($query) use ($emp_id) {
                $query->where('id', $emp_id);
            })->first();
            $log = new ActivitylogRepository($user, 'Bank Details Changed', 'Bank details were removed', 1);
            $log->info(1, 1, null, null);

            return response()->json($dataResponse, 200);
        } catch (Exception $ex) {
            DB::rollBack();
            DB::table('logs')->insert([
                'user_id' => $user_id,
                'function_name' => 'destroy',
                'log_type' => 'Error',
                'action' => 'Delete BankInformation',
                'ip_address' => Helper::instance()->getIp(),
                'data' => $ex->getMessage(),
            ]);
            $dataResponse->ErrorResponse($ex);
            return response()->json($dataResponse, 500);
        }
    }
}

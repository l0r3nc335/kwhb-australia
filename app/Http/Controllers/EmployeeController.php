<?php

namespace App\Http\Controllers;

use App\Core\v1\Activitylog\Repositories\ActivitylogRepository;
use App\Core\v1\Employee\Models\Employee;
use App\Core\v1\Notification\Repositories\NotificationRepository;
use App\Jobs\ScheduleEmployeeStatusJob;
use App\Models\User;
use App\Models\Role;
use App\Models\UserRole;
use App\Models\EmergencyContact;
use App\Models\MedicalHistory;
use App\Models\address;
use App\Events\NotificationCreated;
use App\Models\Attachment;
use App\Models\EmployeeStatusHistory;
use App\Services\Myob\MyobEmployeeSyncService;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use App\Helpers\DataResponse;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use stdClass;
use App\Myob\Services\Employee\EmployeeServices;

class EmployeeController extends Controller
{

    protected $authUser;
    protected NotificationRepository $notification;

    public function __construct()
    {
        $this->authUser = Auth::user();
        $this->notification = new NotificationRepository;
    }
    public function index()
    {
        return response()->json($this->getEmployees());
    }

    public function getEmployees()
    {
        $tbl = Employee::with([
            'user.user_role.role',
            'division',
            'employment',
            'bank_info',
            'address',
            'location',
            'emergency_contact',
            'medical',
            'profile_image',
            'cover_image',
            'manager'
        ])->paginate(5);
        return $tbl;
    }
    public function create()
    {
        //
    }


    public function fetch_employees(Request $request)
    {
        $itemsPerPage = $request->itemsPerPage;
        $search = $request->search;
        $tbl = Employee::with([
            'user.user_role.role',
            'division',
            'employment',
            'bank_info',
            'address',
            'location',
            'emergency_contact',
            'medical',
            'profile_image',
            'cover_image',
            'manager'
        ]);
        if ($search != null || $search != "") {
            $tbl->where(function ($query1) use ($search) {
                $query1->where('first_name', 'like', '%' . $search . '%')
                    ->orWhere('last_name', 'like', '%' . $search . '%');
            });
        }
        return response()->json($tbl->paginate($itemsPerPage));
    }


    //TODO: add the myob integrations here
    public function store(Request $request)
    {

        $dataResponse = new DataResponse();
        $user_cred = Auth::user();
        try {
            DB::beginTransaction();

            //$dateHired = Carbon::createFromFormat('d/m/Y', $request->date_hired)->format('Y-m-d');
            //$requestData['date_hired'] = $dateHired;
            $requestData = $request->all();

            //Store User
            $user = new User;
            $user->role_id = $request->role_id;
            $user->name = $request->first_name . " " . $request->last_name;
            $user->username =  substr(strtolower($request->first_name), 0, 1) . strtolower($request->last_name);
            $user->password = bcrypt('password');

            // $user->employee_id = $data->id;
            $user->save();

            //Store Employee
            $requestData['date_hired'] = \Carbon\Carbon::parse($requestData['date_hired']);
            $requestData['user_id'] = $user->id;
            $data = Employee::create($requestData);

            //Store Role
            $user_role = new UserRole;
            $user_role->user_id = $user->id;
            $user_role->role_id = $request->role_id;
            $user_role->save();

            //store profile pic
            if ($request->hasFile('profile')) {
                $attachment = new stdClass;
                $attachment->attachment = $request->profile;
                $attachment->path = "profile_image";
                $attachment->name = "profile_image";
                $attachment->source = "employee";
                $attachment->source_id = $data->id;
                $AttachmentController = "App\Http\Controllers\AttachmentController";
                app($AttachmentController)->saveAttachment($attachment);
            }
            DB::table('logs')->insert([
                'user_id' => $user_cred->id,
                'function_name' => 'store',
                'log_type' => 'Message',
                'action' => 'Create Employee',
                'ip_address' => Helper::instance()->getIp(),
                'data' => $data,
                'created_at' => Carbon::now(),
            ]);

            //activity logs
            $log = new ActivitylogRepository($user, 'User Created', $request->first_name . ' ' . $request->last_name . ' account was created', 1);
            $log->info(1, 1, null, null);

            $dataResponse->data = $this->show($data->id);
            $dataResponse->message = 'Success';
            DB::commit();
            return response()->json($dataResponse, 200);
        } catch (Exception $ex) {
            DB::rollBack();
            DB::table('logs')->insert([
                'user_id' =>  $user_cred->id,
                'function_name' => 'store',
                'log_type' => 'Error',
                'action' => 'Create Employee',
                'ip_address' => Helper::instance()->getIp(),
                'data' => $ex->getMessage(),
                'created_at' => Carbon::now(),
            ]);
            $dataResponse->ErrorResponse($ex);
            return response()->json($dataResponse, 200);

            //return response()->json(['error' => $ex->getMessage()], 500);
        }
    }


    public function show($emp_id)
    {
        $tbl = Employee::with([
            'user.user_role.role',
            'division',
            'employment',
            'bank_info',
            'address',
            'location',
            'emergency_contact',
            'medical',
            'profile_image',
            'cover_image',
            'manager'
        ])->findOrFail($emp_id);
        return $tbl;
    }

    public function edit(Employee $Employee)
    {
        //
    }

    public function update(Request $request, $id)
    {

        $dataResponse = new DataResponse();
        $user_cred = Auth::user();
        try {
            $cmd  = Employee::findOrFail($id);
            $logFrom = $cmd->replicate();
            $input = $request->all();

            $input['date_hired'] = \Carbon\Carbon::parse($input['date_hired']);
            $cmd->fill($input)->save();

            if ($request->role_id) {
                $user = $cmd->user;
                $user->role_id = $request->role_id;
                $user->save();
            }

            $logTo = $cmd;
            DB::table('logs')->insert([
                'user_id' => $user_cred->id,
                'function_name' => 'update',
                'log_type' => 'Message',
                'action' => 'Update Employee',
                'ip_address' => Helper::instance()->getIp(),
                'data' => "From: " . $logFrom . "\r\nTo: " . $logTo,
                'created_at' => Carbon::now(),
            ]);
            $dataResponse->data = $this->show($id);
            $dataResponse->message = 'Success';

            //activity logs
            $user = User::whereHas('employee', function ($query) use ($request) {
                $query->where('id', $request->employee_id);
            })->first();

            $log = new ActivitylogRepository($user, 'Employee Details', 'Updated Employee Details', 1);
            $log->info(1, 1, null, null);

            return response()->json($dataResponse, 200);
        } catch (Exception $ex) {
            DB::table('logs')->insert([
                'user_id' => $user_cred->id,
                'function_name' => 'update',
                'log_type' => 'Error',
                'action' => 'Update Employee',
                'ip_address' => Helper::instance()->getIp(),
                'data' => $ex->getMessage(),
                'created_at' => Carbon::now(),
            ]);
            $dataResponse->ErrorResponse($ex);
            return response()->json($dataResponse, 200);
        }
    }

    public function destroy($id)
    {
        $user_cred = Auth::user();
        try {
            $tbl = Employee::findOrFail($id);
            Employee::destroy($id);


            DB::table('logs')->insert([
                'user_id' => $user_cred->id,
                'function_name' => 'destroy',
                'log_type' => 'Message',
                'action' => 'Delete Employee',
                'ip_address' => Helper::instance()->getIp(),
                'data' => $tbl,
                'created_at' => Carbon::now(),
            ]);
            return $this->index();
        } catch (Exception $ex) {
            DB::table('logs')->insert([
                'user_id' => $user_cred->id,
                'function_name' => 'destroy',
                'log_type' => 'Error',
                'action' => 'Delete Employee',
                'ip_address' => Helper::instance()->getIp(),
                'data' => $ex->getMessage(),
                'created_at' => Carbon::now(),
            ]);
            return response()->json(['error' => 'There was a problem deleting this Employee.'], 500);
        }
    }

    public function update_row(Request $request)
    {
        $user_cred = Auth::user();
        $dataResponse = new DataResponse();

        try {
            DB::beginTransaction();
            $oldData = "";
            $newData = "";
            $myobemp = "";
            $emp = Employee::with(['employment', 'user'])->where("id", $request->id)->first();
            if ($request->row == 'status') {

                $dateSelected = Carbon::now();
                $selectedRole = $request->get('selectedRole');
                if (isset($request->dateSelected)) {
                    $dateSelected = Carbon::parse($request->dateSelected, 'Australia/Sydney');
                    $dateNow = Carbon::now('Australia/Sydney');

                    if ($dateSelected->isAfter($dateNow)) {
                        $emp->scheduled_status = $request->data;
                        $emp->scheduled_date = $dateSelected->format('Y-m-d');
                        $emp->save();

                        $delaySeconds = abs($dateSelected->diffInSeconds($dateNow));
                        ScheduleEmployeeStatusJob::dispatch($emp->id, $emp->scheduled_date, $emp->scheduled_status)
                            ->delay($delaySeconds);
                    } else {
                        $emp->status = $request->data;
                        $emp->date_hired = $dateSelected->format('Y-m-d');
                        $emp->scheduled_status = null;
                        $emp->scheduled_date = null;
                        $emp->save();

                        $user = User::find($emp->user_id);

                        if (!empty($selectedRole)) {
                            $user->role_id = $selectedRole;
                            $user->save();
                        }

                        $this->notification
                            ->from($user)
                            ->toManagers()
                            ->toHr()
                            ->notify(
                                "Employment Status Change",
                                "<b>{$emp->first_name} {$emp->last_name}</b>'s employment status has been changed."
                            );
                    }

                    //activity logs
                    $user = User::whereHas('employee', function ($query) use ($request) {
                        $query->where('id', $request->id);
                    })->first();
                    $data = is_array($request->data) ? $request->data[0] : $request->data;
                    $log = new ActivitylogRepository($user, 'Employment Status Changed', 'Employment status was changed to ' . ucwords($data) . ' from ' . Carbon::parse($dateSelected)->format('d/m/Y'), 1);
                    $log->info(1, 1, null, null);

                    // Sync with Myob Employee
                    try {
                        Artisan::call("myob:upsert employee {$user->id}");
                    } catch (\Exception) {
                        //
                    }
                }

                if (!empty($request->selectedRole)) {
                    UserRole::where('user_id', $emp->user->id)->delete();
                    UserRole::create([
                        'user_id' => $emp->user->id,
                        'role_id' => $request->selectedRole
                    ]);
                }

                EmployeeStatusHistory::insert([
                    'status' => $data,
                    'employee_id' => $request->id,
                    'user_id' => $user_cred->id,
                    'date_change' => Carbon::parse($dateSelected),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
            }

            if ($request->row == 'password') {
                $this->reset_password($request->id, $request->data);

                //activity logs
                $user = User::whereHas('employee', function ($query) use ($request) {
                    $query->where('id', $request->id);
                })->with('employee')->first();
                $log = new ActivitylogRepository($user, 'Password Reset', $user->employee->first_name . ' ' . $user->employee->last_name . ' reset their password', 1);
                $log->info(1, 1, null, null);

                $this->notification
                    ->from($user)
                    ->to([$user->id])
                    ->notify(
                        "Password Update Confirmation",
                        "Your password has been updated."
                    );
            } else if ($request->row == 'employee') {
                $data = (object) $request->data;
                $tbl = Employee::where("id", $request->id);
                $oldData = $tbl->get();
                Employee::where('id', $request->id)->update([
                    'first_name' => $data->first_name,
                    'last_name' => $data->last_name,
                    'division_id' => $data->division_id,
                    'manager_id' => $data->manager_id,
                    'job_title' => $data->job_title,
                    'position_id'   => $data->position_id,
                    'updated_at' => Carbon::now()
                ]);

                $user = User::whereHas('employee', function ($query) use ($request) {
                    $query->where('id', $request->id);
                })->first();

                if (!empty($data->role_id)) {
                    User::where('id', $user->id)->update(['role_id' => $data->role_id]);
                }

                $log = new ActivitylogRepository($user, 'Employee Details', 'Employee details were changed', 1);
                $log->info(1, 1, null, null);

                // Sync with Myob Employee
                try {
                    Artisan::call("myob:upsert employee {$user->id}");
                } catch (\Exception) {
                    //
                }

                $newData = $tbl->get();

                // Notifications
                if (empty($data->manager_id)) {
                    $user = User::find($oldData[0]->user_id);
                    $this->notification
                        ->from($user)
                        ->toManagers(false, true)
                        ->toHr()
                        ->notify(
                            "Employee Unassigned",
                            "<b>{$user->employee->first_name} {$user->employee->last_name}</b> is not assigned a Line Manager."
                        );
                }
            } else if ($request->row == 'contact') {
                $data = (object) $request->data;
                $tbl = Employee::where("id", $request->id);
                $oldData = $tbl->get();
                Employee::where('id', $request->id)->update([
                    'work_phone' => $data->work_phone,
                    'work_email' => $data->work_email,
                    'location_id' => $data->location_id,
                    'updated_at' => Carbon::now()
                ]);
                //activity logs
                $user = User::whereHas('employee', function ($query) use ($request) {
                    $query->where('id', $request->id);
                })->first();
                $log = new ActivitylogRepository($user, 'Contact Details', 'Contact details were changed', 1);
                $log->info(1, 1, null, null);

                // Sync with Myob Employee
                try {
                    Artisan::call("myob:upsert employee {$user->id}");
                } catch (\Exception) {
                    //
                }

                $newData = $tbl->get();

                // Manager, HR
                $this->notification
                    ->from($user)
                    ->toHr()
                    ->toManagers()
                    ->notify(
                        "Contact Details Change Confirmation",
                        "<b>{$user->employee->first_name} {$user->employee->first_name}</b>'s contact details have changed."
                    );
                // User
                $this->notification
                    ->from($user)
                    ->to([$user->id])
                    ->notify(
                        "Contact Details Change Confirmation",
                        "Your contact details have changed."
                    );
            } else if ($request->row == 'personal_details') {
                $data = (object) $request->data;
                $tbl = Employee::where("id", $request->id);
                $oldData = $tbl->get();

                // --- SAFELY PARSE DOB ---
                $dobValue = null;
                try {
                    if (!empty($data->dob2) && strtolower($data->dob2) !== 'invalid date') {
                        $dobValue = \Carbon\Carbon::parse($data->dob2);
                    }
                } catch (\Exception $e) {
                    $dobValue = null;
                }

                Employee::where('id', $request->id)->update([
                    'first_name' => $data->first_name,
                    'last_name' => $data->last_name,
                    'gender' => $data->gender,
                    'pronouns' => $data->pronouns,
                    'indigenous' => $data->indigenous,
                    'dob' => $dobValue,
                    'address' => $data->address,
                    'personal_phone' => $data->personal_phone,
                    'personal_email' => $data->personal_email,
                    'updated_at' => Carbon::now()
                ]);
                if ($data->address2 != null)
                    address::updateOrCreate(
                        ["employee_id" => $request->id],
                        [
                            "name" => "Employee details address",
                            "label" => "home",
                            "address" => $data->address2,
                            "is_default" => true,
                            "is_active" => true,
                        ]
                    );
                //activity logs
                $user = User::whereHas('employee', function ($query) use ($request) {
                    $query->where('id', $request->id);
                })->first();
                $log = new ActivitylogRepository($user, 'Personal Details', 'Personal details were changed', 1);
                $log->info(1, 1, null, null);

                // Sync with Myob Employee
                try {
                    Artisan::call("myob:upsert employee {$user->id}");
                } catch (\Exception) {
                    //
                }

                $newData = $tbl->get();

                // Manager, HR
                $this->notification
                    ->from($user)
                    ->toHr()
                    ->toManagers()
                    ->notify(
                        "Personal Details Update Confirmation",
                        "<b>{$user->employee->first_name} {$user->employee->first_name}</b>'s personal information has been updated."
                    );
                // User
                $this->notification
                    ->from($user)
                    ->to([$user->id])
                    ->notify(
                        "Personal Details Update Confirmation",
                        "Your personal information has been updated."
                    );
            }

            DB::table('logs')->insert([
                'user_id' => $user_cred->id,
                'function_name' => 'update_row',
                'log_type' => 'Message',
                'action' => 'Update Employee ' . $request->row,
                'ip_address' => Helper::instance()->getIp(),
                'data' => "update Employee id " . $request->id . "\nRow: " . $request->row .
                    "\nFrom: " . $oldData . "\nTo: " . $newData . "\nmyobemp:" . strval($myobemp),
                'created_at' => Carbon::now(),
            ]);

            $dataResponse->data = $this->show($request->id);
            $dataResponse->message = 'Success';
            event(new NotificationCreated("test broadcasting"));
            DB::commit();
            return response()->json($dataResponse, 200);
        } catch (\Exception $ex) {
            DB::rollBack();
            DB::table('logs')->insert([
                'user_id' => $user_cred->id,
                'function_name' => 'update_row',
                'log_type' => 'Error',
                'action' => 'Update Employee ' . $request->row,
                'ip_address' => Helper::instance()->getIp(),
                'data' => $ex->getMessage(),
                'created_at' => Carbon::now(),
            ]);
            $dataResponse->ErrorResponse($ex);
            return response()->json($dataResponse, 200);
        }
    }

public function update_user(Request $request)
{
    $user_cred = Auth::user();
    $dataResponse = new DataResponse();

    try {
        DB::beginTransaction();
        $oldData = "";
        $newData = "";
        $myobemp = "";

        $emp = Employee::with(['employment', 'user'])->where("id", $request->id)->first();
        if (!$emp) {
            throw new \Exception("Employee not found with ID: " . $request->id);
        }

        if ($request->row == 'status') {
            $dateSelected = Carbon::now();
            $selectedRole = $request->get('selectedRole');
            $selectedRoleId = null;
            // Extract selectedRoleId from possible array/object/int
            if (is_array($selectedRole) && isset($selectedRole['id'])) {
                $selectedRoleId = $selectedRole['id'];
            } else if (is_object($selectedRole) && isset($selectedRole->id)) {
                $selectedRoleId = $selectedRole->id;
            } else if (is_numeric($selectedRole)) {
                $selectedRoleId = $selectedRole;
            }

            if (isset($request->dateSelected)) {
                $dateSelected = Carbon::parse($request->dateSelected, 'Australia/Sydney');
                $dateNow = Carbon::now('Australia/Sydney');

                $data = is_array($request->data) ? $request->data[0] : $request->data;

                if ($dateSelected->isAfter($dateNow)) {
                    // Future status, schedule change
                    $emp->scheduled_status = $data;
                    $emp->scheduled_date = $dateSelected->format('Y-m-d');
                    $emp->save();

                    $delaySeconds = abs($dateSelected->diffInSeconds($dateNow));
                    ScheduleEmployeeStatusJob::dispatch($emp->id, $emp->scheduled_date, $emp->scheduled_status)
                        ->delay($delaySeconds);
                } else {
                    // Immediate status update
                    $emp->status = $data;
                    $emp->date_hired = $dateSelected->format('Y-m-d');
                    $emp->scheduled_status = null;
                    $emp->scheduled_date = null;
                    $emp->save();

                    $user = User::find($emp->user_id);

                    if (!empty($selectedRoleId)) {
                        $user->role_id = $selectedRoleId;
                        $user->save();
                    }

                    $this->notification
                        ->from($user)
                        ->toManagers()
                        ->toHr()
                        ->notify(
                            "Employment Status Change",
                            "<b>{$emp->first_name} {$emp->last_name}</b>'s employment status has been changed."
                        );
                }

                // Activity logs
                $user = User::whereHas('employee', function ($query) use ($request) {
                    $query->where('id', $request->id);
                })->first();

                $log = new ActivitylogRepository($user, 'Employment Status Changed', 'Employment status was changed to ' . ucwords($data) . ' from ' . Carbon::parse($dateSelected)->format('d/m/Y'), 1);
                $log->info(1, 1, null, null);

                // Sync with Myob Employee
                try {
                    Artisan::call("myob:upsert employee {$user->id}");
                } catch (\Exception) {
                    //
                }

                // Sync user_roles table
                if (!empty($selectedRoleId)) {
                    UserRole::where('user_id', $emp->user->id)->delete();
                    UserRole::create([
                        'user_id' => $emp->user->id,
                        'role_id' => $selectedRoleId
                    ]);
                }

                EmployeeStatusHistory::insert([
                    'status' => $data,
                    'employee_id' => $request->id,
                    'user_id' => $user_cred->id,
                    'date_change' => Carbon::parse($dateSelected),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
            }
        }

        if ($request->row == 'password') {
            $this->reset_password($request->id, $request->data);

            $user = User::whereHas('employee', function ($query) use ($request) {
                $query->where('id', $request->id);
            })->with('employee')->first();
            $log = new ActivitylogRepository($user, 'Password Reset', $user->employee->first_name . ' ' . $user->employee->last_name . ' reset their password', 1);
            $log->info(1, 1, null, null);

            $this->notification
                ->from($user)
                ->to([$user->id])
                ->notify(
                    "Password Update Confirmation",
                    "Your password has been updated."
                );
        } else if ($request->row == 'employee') {
            $data = (object) $request->data;
            $tbl = Employee::where("id", $request->id);
            $oldData = $tbl->get();
            Employee::where('id', $request->id)->update([
                'first_name' => $data->first_name,
                'last_name' => $data->last_name,
                'division_id' => $data->division_id,
                'manager_id' => $data->manager_id,
                'job_title' => $data->job_title,
                'position_id'   => $data->position_id,
                'updated_at' => Carbon::now()
            ]);

            $user = User::whereHas('employee', function ($query) use ($request) {
                $query->where('id', $request->id);
            })->first();

            if (!empty($data->role_id)) {
                User::where('id', $user->id)->update(['role_id' => $data->role_id]);
            }

            $log = new ActivitylogRepository($user, 'Employee Details', 'Employee details were changed', 1);
            $log->info(1, 1, null, null);

            // Sync with Myob Employee
            try {
                Artisan::call("myob:upsert employee {$user->id}");
            } catch (\Exception) {
                //
            }

            $newData = $tbl->get();

            // Notifications
            if (empty($data->manager_id)) {
                $user = User::find($oldData[0]->user_id);
                $this->notification
                    ->from($user)
                    ->toManagers(false, true)
                    ->toHr()
                    ->notify(
                        "Employee Unassigned",
                        "<b>{$user->employee->first_name} {$user->employee->last_name}</b> is not assigned a Line Manager."
                    );
            }
        } else if ($request->row == 'contact') {
            $data = (object) $request->data;
            $tbl = Employee::where("id", $request->id);
            $oldData = $tbl->get();
            Employee::where('id', $request->id)->update([
                'work_phone' => $data->work_phone,
                'work_email' => $data->work_email,
                'location_id' => $data->location_id,
                'updated_at' => Carbon::now()
            ]);
            //activity logs
            $user = User::whereHas('employee', function ($query) use ($request) {
                $query->where('id', $request->id);
            })->first();
            $log = new ActivitylogRepository($user, 'Contact Details', 'Contact details were changed', 1);
            $log->info(1, 1, null, null);

            // Sync with Myob Employee
            try {
                Artisan::call("myob:upsert employee {$user->id}");
            } catch (\Exception) {
                //
            }

            $newData = $tbl->get();

            // Manager, HR
            $this->notification
                ->from($user)
                ->toHr()
                ->toManagers()
                ->notify(
                    "Contact Details Change Confirmation",
                    "<b>{$user->employee->first_name} {$user->employee->first_name}</b>'s contact details have changed."
                );
            // User
            $this->notification
                ->from($user)
                ->to([$user->id])
                ->notify(
                    "Contact Details Change Confirmation",
                    "Your contact details have changed."
                );
        } else if ($request->row == 'personal_details') {
            $data = (object) $request->data;
            $tbl = Employee::where("id", $request->id);
            $oldData = $tbl->get();

            // --- SAFELY PARSE DOB ---
            $dobValue = null;
            try {
                if (!empty($data->dob2) && strtolower($data->dob2) !== 'invalid date') {
                    $dobValue = \Carbon\Carbon::parse($data->dob2);
                }
            } catch (\Exception $e) {
                $dobValue = null;
            }

            Employee::where('id', $request->id)->update([
                'first_name' => $data->first_name,
                'last_name' => $data->last_name,
                'gender' => $data->gender,
                'pronouns' => $data->pronouns,
                'indigenous' => $data->indigenous,
                'dob' => $dobValue,
                'personal_phone' => $data->personal_phone,
                'personal_email' => $data->personal_email,
                'updated_at' => Carbon::now()
            ]);
            if ($data->address2 != null)
                address::updateOrCreate(
                    ["employee_id" => $request->id],
                    [
                        "name" => "Employee details address",
                        "label" => "home",
                        "address" => $data->address2,
                        "is_default" => true,
                        "is_active" => true,
                    ]
                );
            //activity logs
            $user = User::whereHas('employee', function ($query) use ($request) {
                $query->where('id', $request->id);
            })->first();
            $log = new ActivitylogRepository($user, 'Personal Details', 'Personal details were changed', 1);
            $log->info(1, 1, null, null);

            // Sync with Myob Employee
            try {
                Artisan::call("myob:upsert employee {$user->id}");
            } catch (\Exception) {
                //
            }

            $newData = $tbl->get();

            // Manager, HR
            $this->notification
                ->from($user)
                ->toHr()
                ->toManagers()
                ->notify(
                    "Personal Details Update Confirmation",
                    "<b>{$user->employee->first_name} {$user->employee->first_name}</b>'s personal information has been updated."
                );
            // User
            $this->notification
                ->from($user)
                ->to([$user->id])
                ->notify(
                    "Personal Details Update Confirmation",
                    "Your personal information has been updated."
                );
        }

        DB::table('logs')->insert([
            'user_id' => $user_cred->id,
            'function_name' => 'update_row',
            'log_type' => 'Message',
            'action' => 'Update Employee ' . $request->row,
            'ip_address' => Helper::instance()->getIp(),
            'data' => "update Employee id " . $request->id . "\nRow: " . $request->row .
                "\nFrom: " . $oldData . "\nTo: " . $newData . "\nmyobemp:" . strval($myobemp),
            'created_at' => Carbon::now(),
        ]);

        $dataResponse->data = $this->show($request->id);
        $dataResponse->message = 'Success';
        event(new NotificationCreated("test broadcasting"));
        DB::commit();
        return response()->json($dataResponse, 200);
    } catch (\Exception $ex) {
        DB::rollBack();
        DB::table('logs')->insert([
            'user_id' => $user_cred->id,
            'function_name' => 'update_row',
            'log_type' => 'Error',
            'action' => 'Update Employee ' . $request->row,
            'ip_address' => Helper::instance()->getIp(),
            'data' => $ex->getMessage(),
            'created_at' => Carbon::now(),
        ]);
        $dataResponse->ErrorResponse($ex);
        return response()->json($dataResponse, 200);
    }
}


    public function update_emergency_contact(Request $request)
    {
        $user_cred = Auth::user();
        $dataResponse = new DataResponse();
        try {
            $oldData = EmergencyContact::where("employee_id", $request->employee_id)->first();

            $newData = EmergencyContact::updateOrCreate(
                ["employee_id" => $request->employee_id],
                $request->all()
            );

            DB::table('logs')->insert([
                //'user_id' => 1,
                'user_id' => $user_cred->id,
                'function_name' => 'update_emergency_contact',
                'log_type' => 'Message',
                'action' => 'Update Employee Emergency Contact',
                'ip_address' => Helper::instance()->getIp(),
                'data' => "From: " . $oldData . "\r\nTo: " . $newData,
                'created_at' => Carbon::now(),
            ]);

            $dataResponse->data = $this->show($request->employee_id);
            $dataResponse->message = 'Success';

            //activity logs
            $user = User::whereHas('employee', function ($query) use ($request) {
                $query->where('id', $request->employee_id);
            })->first();
            $log = new ActivitylogRepository($user, 'Emergency Contact', 'Emergency Contact details were changed', 1);
            $log->info(1, 1, null, null);

            $this->notification
                ->from($user)
                ->to([$user->id])
                ->notify(
                    "Emergency Contact Details Update Confirmation",
                    "Your emergency contact details have been updated."
                );

            return response()->json($dataResponse, 200);
        } catch (\Exception $ex) {
            DB::table('logs')->insert([
                //'user_id' => 1,
                'user_id' => $user_cred->id,
                'function_name' => 'update_emergency_contact',
                'log_type' => 'Error',
                'action' => 'Update Employee Emergency Contact',
                'ip_address' => Helper::instance()->getIp(),
                'data' => $ex->getMessage(),
                'created_at' => Carbon::now(),
            ]);
            $dataResponse->ErrorResponse($ex);
            return response()->json($dataResponse, 200);
        }
    }
    public function update_medical(Request $request)
    {
        $user_cred = Auth::user();
        $dataResponse = new DataResponse();
        try {
            $oldData = MedicalHistory::where("employee_id", $request->employee_id)->first();

            $newData = MedicalHistory::updateOrCreate(
                ["employee_id" => $request->employee_id],
                $request->all()
            );

            DB::table('logs')->insert([
                //'user_id' => 1,
                'user_id' => $user_cred->id,
                'function_name' => 'update_medical',
                'log_type' => 'Message',
                'action' => 'Update Employee Medical History ',
                'ip_address' => Helper::instance()->getIp(),
                'data' => "From: " . $oldData . "\r\nTo: " . $newData,
                'created_at' => Carbon::now(),
            ]);

            $dataResponse->data = $this->show($request->employee_id);
            $dataResponse->message = 'Success';

            //activity logs
            $user = User::whereHas('employee', function ($query) use ($request) {
                $query->where('id', $request->employee_id);
            })->first();
            $log = new ActivitylogRepository($user, 'Medical', 'Medical details were changed', 1);
            $log->info(1, 1, null, null);

            $this->notification
                ->from($user)
                ->to([$user->id])
                ->notify(
                    "Medical Information Update Confirmation",
                    "Your medical information have been updated."
                );

            return response()->json($dataResponse, 200);
        } catch (\Exception $ex) {
            DB::table('logs')->insert([
                //'user_id' => 1,
                'user_id' => $user_cred->id,
                'function_name' => 'update_medical',
                'log_type' => 'Error',
                'action' => 'Update Employee Medical History ',
                'ip_address' => Helper::instance()->getIp(),
                'data' => $ex->getMessage(),
                'created_at' => Carbon::now(),
            ]);
            $dataResponse->ErrorResponse($ex);
            return response()->json($dataResponse, 200);
        }
    }
    public function update_address(Request $request)
    {
        $user_cred = Auth::user();
        $dataResponse = new DataResponse();
        try {
            $oldData = address::where("employee_id", $request->employee_id)->first();

            $newData = address::updateOrCreate(
                ["employee_id" => $request->employee_id],
                $request->all()
            );

            DB::table('logs')->insert([
                'user_id' => $user_cred->id,
                'function_name' => 'update_address',
                'log_type' => 'Message',
                'action' => 'Update Employee Address ',
                'ip_address' => Helper::instance()->getIp(),
                'data' => "From: " . $oldData . "\r\nTo: " . $newData,
                'created_at' => Carbon::now(),
            ]);

            $dataResponse->data = $this->show($request->employee_id);
            $dataResponse->message = 'Success';

            //activity logs
            $user = User::whereHas('employee', function ($query) use ($request) {
                $query->where('id', $request->employee_id);
            })->first();
            $log = new ActivitylogRepository($user, 'Address Details', 'Address details were changed', 1);
            $log->info(1, 1, null, null);

            return response()->json($dataResponse, 200);
        } catch (\Exception $ex) {
            //asd
            DB::table('logs')->insert([
                'user_id' => $user_cred->id,
                'function_name' => 'update_address',
                'log_type' => 'Error',
                'action' => 'Update Employee Address',
                'ip_address' => Helper::instance()->getIp(),
                'data' => $ex->getMessage(),
                'created_at' => Carbon::now(),
            ]);
            $dataResponse->ErrorResponse($ex);
            return response()->json($dataResponse, 200);
        }
    }
    public function fetch_approvers(Request $request)
    {
        $user_cred = Auth::user();
        $dataResponse = new DataResponse();
        try {
            $employee_id = $request->employee_id;
            $division_id = $request->division_id;


            $data = Employee::where("division_id", $division_id)
                ->whereHas('user.user_role.role', function ($query) {
                    $query->whereIn('id', [2, 3]);
                })
                ->where('id', '!=', $employee_id)
                ->get();

            $dataResponse->data = $data;
            $dataResponse->message = 'Success';
            return response()->json($dataResponse, 200);
        } catch (\Exception $ex) {
            $dataResponse->ErrorResponse($ex);
            return response()->json($dataResponse, 200);
        }
    }
    public function fetch_line_managers(Request $request)
    {
        $user_cred = Auth::user();
        $dataResponse = new DataResponse();
        try {
            $division_id = $request->division_id;


            $data = Employee::where("division_id", $division_id)
                ->whereHas('user.user_role.role', function ($query) {
                    $query->whereIn('id', [2, 3]);
                })
                ->get();

            $dataResponse->data = $data;
            $dataResponse->message = 'Success';
            return response()->json($dataResponse, 200);
        } catch (\Exception $ex) {
            $dataResponse->ErrorResponse($ex);
            return response()->json($dataResponse, 200);
        }
    }

    public function fetch_employee_by_name(Request $request)
    {
        $dataResponse = new DataResponse();
        $user_cred = Auth::user();
        try {

            $data = Employee::with([
                'user.user_role.role',
                'division',
                'employment',
                'bank_info',
                'address',
                'location',
                'emergency_contact',
                'medical',
                'profile_image',
                'cover_image',
                'manager'
            ])
                ->where(DB::raw("LOWER(concat(first_name, ' ', last_name))"), 'like', "%{$request->get('name')}%")
                ->get();

            $dataResponse->data = $data;
            $dataResponse->message = 'Success';
            return response()->json($dataResponse, 200);
        } catch (\Exception $ex) {
            $dataResponse->ErrorResponse($ex);
            return response()->json($dataResponse, 200);
        }
    }

    public function fetch_widget_data()
    {
        $dataResponse = new DataResponse();
        try {

            $totalEmployees = Employee::count();
            $activeEmployees = Employee::where('status', 'active')->count();
            $offswingEmployees = Employee::where('status', 'offswing')->count();
            $pendingEmployees = Employee::where('status', 'pending')->count();
            $extendedLeaveEmployees = Employee::where('status', 'extended leave')->count();

            $activeEmployeesP = round(($activeEmployees / $totalEmployees) * 100, 2);
            $offswingEmployeesP = round(($offswingEmployees / $totalEmployees) * 100, 2);
            $pendingEmployeesP = round(($pendingEmployees / $totalEmployees) * 100, 2);
            $extendedLeaveEmployeesP = round(($extendedLeaveEmployees / $totalEmployees) * 100, 2);


            $result = [
                [
                    'title' => 'Active Employees',
                    'value' => $activeEmployees,
                    'change' => $activeEmployeesP,
                    'desc' => 'Active Total Employees',
                    'icon' => 'tabler-user',
                    'iconColor' => '#62c379',
                ],
                [
                    'title' => 'Offswing',
                    'value' => $offswingEmployees,
                    'change' => $offswingEmployeesP,
                    'desc' => 'Fixed Period Contractors',
                    'icon' => 'tabler-user-plus',
                    'iconColor' => '#9c60e5',
                ],
                [
                    'title' => 'Pending Employees',
                    'value' => $pendingEmployees,
                    'change' => $pendingEmployeesP,
                    'desc' => 'Year to Date',
                    'icon' => 'tabler-user-check',
                    'iconColor' => '#e35306',
                ],
                [
                    'title' => 'Extended Leave',
                    'value' => $extendedLeaveEmployees,
                    'change' => $extendedLeaveEmployeesP,
                    'desc' => 'Year to Date',
                    'icon' => 'tabler-user-exclamation',
                    'iconColor' => '#e63c49',
                ],
            ];

            $dataResponse->data = $result;
            $dataResponse->message = 'Success';
            return response()->json($dataResponse, 200);
        } catch (Exception $ex) {
            $dataResponse->ErrorResponse($ex);
            return response()->json($dataResponse, 200);
            //throw $th;
        }
    }

    public function multipleFilter(Request $request)
    {
        $dataResponse = new DataResponse();
        try {

            $filter = (object) $request->filter;
            $data = (object) $request->data;
            $tbl = Employee::with([
                'user.user_role.role',
                'division',
                'employment',
                'bank_info',
                'address',
                'location',
                'emergency_contact',
                'medical',
                'profile_image',
                'cover_image',
                'manager'
            ]);
            if ($filter->role)
                if ($data->role_id != "all")
                    if ($data->role_id != "Select Role")
                        $tbl->whereHas('user.user_role.role', function ($query) use ($data) {
                            $query->where('id', $data->role_id);
                        });
            if ($filter->division)
                if ($data->division_id != "all")
                    if ($data->division_id != "Select Division")
                        $tbl->where("division_id", $data->division_id);
            if ($filter->status)
                if ($data->status != "all")
                    if ($data->status != "Select Status")
                        $tbl->where("status", $data->status);



            $dataResponse->data = $tbl->get();
            $dataResponse->message = 'Success';
            return response()->json($dataResponse, 200);
        } catch (Exception $ex) {
            $dataResponse->ErrorResponse($ex);
            return response()->json($dataResponse, 500);
        }
    }

    public function reset_password($employee_id, $password)
    {
        try {
            User::whereHas('employee', function ($query) use ($employee_id) {
                $query->where('id', $employee_id);
            })->update(['password' => bcrypt($password)]);

            return 'updated';
        } catch (Exception $ex) {
            return 'error';
        }
    }

    public function update_attachment(Request $request)
    {
        $user_cred = Auth::user();
        $dataResponse = new DataResponse();
        try {
            if (isset($request->attachment_id)) {
                $attachment = Attachment::find($request->attachment_id);
                $attachment->id = $request->attachment_id;
            } else {
                $attachment = new Attachment();
            }

            $attachment->attachment = $request->image_url;
            $attachment->path = $request->image;
            $attachment->name = $request->image;
            $attachment->source = "employee";
            $attachment->source_id = $request->emp_id;
            $AttachmentController = "App\Http\Controllers\AttachmentController";
            app($AttachmentController)->saveAttachment($attachment);

            DB::table('logs')->insert([
                'user_id' => $user_cred->id,
                'function_name' => 'update_attachment',
                'log_type' => 'Message',
                'action' => 'Update Attachment ID ' . $request->attachment_id,
                'ip_address' => Helper::instance()->getIp(),
                'data' => "Updated attachment ID " . $request->attachment_id,
                'created_at' => Carbon::now(),
            ]);

            $dataResponse->data = $this->show($request->emp_id);
            $dataResponse->message = 'Success';
            return response()->json($dataResponse, 200);
        } catch (\Exception $ex) {
            DB::table('logs')->insert([
                'user_id' => $user_cred->id,
                'function_name' => 'update_attachment',
                'log_type' => 'Error',
                'action' => 'Update Attachment',
                'ip_address' => Helper::instance()->getIp(),
                'data' => $ex->getMessage(),
                'created_at' => Carbon::now(),
            ]);
            $dataResponse->ErrorResponse($ex);
            return response()->json($dataResponse, 500);
        }
    }
}

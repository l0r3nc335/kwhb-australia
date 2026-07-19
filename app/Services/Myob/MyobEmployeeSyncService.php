<?php

namespace App\Services\Myob;

use App\Core\v1\Employee\Models\Employee;
use App\Jobs\SyncMyobEmployeeInfoJob;
use App\Models\Role;
use App\Models\User;
use App\Services\Myob\DataMaps\EmployeeMap;
use Illuminate\Support\Arr;

class MyobEmployeeSyncService
{

    //TODO get last EmployeeID
    //

    public function inserEmployeeToMyOB() {

        $myObEmployeeSyncService = new MyobEmployeeSyncService;

        //TODO: get last ID, plus 1, concat e.g.: EMP000010
        $lastId = $myObEmployeeSyncService->getLastEmployeeID();
        $number = (int) preg_replace('/\D/', '', $lastId);
        $nextId = $number + 1;

        $employeeID = 'EMP' . str_pad((int) preg_replace('/\D/', '', $nextId), 6, '0', STR_PAD_LEFT);

        //TODO: insert Employee
        $data = [
            "Email" => "Ebit.Cool@gmail.com",
            "FirstName" => "Ebit",
            "LastName" => "Cool",
            "MiddleName" => "",
            "Phone1" => "0945 8438 257",
            "Phone1Type" => "Cell",
            "Phone2" => "0945 8438 258",
            "Phone2Type" => "Cell",
            "EmployeeID" => $employeeID,
            "DateOfBirth" => "1981-11-12T10:30:00+10:30",
            "Calendar" => "NT MF 375",
            "Department" => "E345",
            "EmployeeClass" => "E345",
            "CashAccount" => "ANZ BANK",
            "ExpenseAccount" => "5000",
            "ExpenseSubAccount" => "0",
            "SalesAccount" => "4000",
            "SalesSubAccount" => "0"
        ];
        $myObEmployeeSyncService->setNewEmployee($data);

        dd( $lastId . " ----- " . $number . " ----- " . $nextId . " ---- ". $employeeID );
    }

    public function getLastEmployeeID() {
        $employees = (new MyobApiService)->getEmployees()->call();

        // normalize to array
        $employees = is_array($employees) ? $employees : (array) $employees;

        $last = end($employees);

        dd($last);

        // sort by employeeID
        usort($employees, function($a, $b) {
            $idA = is_array($a) ? $a['employeeID'] : $a->employeeID;
            $idB = is_array($b) ? $b['employeeID'] : $b->employeeID;
            return $idA <=> $idB;
        });

        $last = end($employees); // last element after sorting

        return is_array($last) ? ($last['employeeID'] ?? null) : ($last->employeeID ?? null);
    }

    public function setNewEmployee($data) {
        $myobApi = new MyobApiService();
        $myobApi->createOrUpdateEmployee()->call($data);
    }

    public function syncEmployees(): void
    {
        $myobApi = new MyobApiService;
        $employeeMap = new EmployeeMap;
        $myobEmployees = $myobApi->getEmployees()->call();

        foreach ($myobEmployees as $myobEmployee) {
            $user = $this->createUser($myobEmployee->email, $myobEmployee->employeeName, $myobEmployee->employeeID);

            $employee = Employee::query()->where('user_id', $user->id)->first() ?? new Employee;
            $employee = $employeeMap->map($employee, $myobEmployee);
            $employee->user_id = $user->id;
            $employee->save();

            SyncMyobEmployeeInfoJob::dispatch($user->id);
        }
    }

    private function createUser(?string $email, string $name, string $employeeId): User
    {
        $user = User::query()->where('username', $email)->orWhere('username', $employeeId)->first();

        if (!$user) {
            $password = strtolower(str_replace(' ', '', trim($name)));
            $role = Role::query()->where('name', 'User')->first();

            $user = new User;
            $user->name = $name;
            $user->username = $email ?: $employeeId;
            $user->password = bcrypt($password);
            $user->role_id = $role->id;
            $user->save();
        }

        return $user;
    }
}

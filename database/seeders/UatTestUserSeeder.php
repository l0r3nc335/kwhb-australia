<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\EmployeeStatusHistory;
use App\Models\EmployeeVaccination;
use App\Models\Employment;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class UatTestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $usernames = [
            'flashgordon',
            'flashgordon_admin',
            'flashgordon_linemanager',
            'flashgordon_user',
            'flashgordon_generalmanager',
            'flashgordon_hr',
            'flashgordon_finance',
            'flashgordon_ceo',
            'flashgordon_board',
            'flashgordon_applicant',
        ];

        $userIds = User::whereIn('username', $usernames)->pluck('id')->toArray();
        $employeeIds = Employee::whereIn('user_id', $userIds)->pluck('id')->toArray();

        EmployeeStatusHistory::query()->whereIn('employee_id', $employeeIds)->forceDelete();
        EmployeeStatusHistory::query()->whereIn('user_id', $userIds)->forceDelete();
        EmployeeVaccination::query()->whereIn('user_id', $userIds)->forceDelete();


        Employee::query()->whereIn('user_id', $userIds)->forceDelete();
        User::query()->whereIn('id', $userIds)->forceDelete();

        Artisan::call("create:test-user flashgordon password 'Super User'");
        Artisan::call("create:test-user flashgordon_admin password Administrator");
        Artisan::call("create:test-user flashgordon_linemanager password 'Line Manager'");
        Artisan::call("create:test-user flashgordon_user password User");
        Artisan::call("create:test-user flashgordon_generalmanager password 'General Manager'");
        Artisan::call("create:test-user flashgordon_hr uat!@#pass 'HR'");
        Artisan::call("create:test-user flashgordon_finance password 'Finance'");
        Artisan::call("create:test-user flashgordon_ceo password 'CEO'");
        Artisan::call("create:test-user flashgordon_board password 'Board'");
        Artisan::call("create:test-user flashgordon_applicant password 'Applicant'");

        foreach($usernames as $username) {
            $user = User::where('username', $username)->first();

            if (!empty($user)) {
                $this->createEmployee($user);
            }
        }

        $lineManager = User::where('username', 'flashgordon_linemanager')->first();
        $generalManager = User::where('username', 'flashgordon_generalmanager')->first();

        $userIds = User::whereIn('username', [
            'flashgordon_admin',
            'flashgordon_applicant',
            'flashgordon_finance',
            'flashgordon_hr',
            'flashgordon_user',
        ])->get()->pluck('id')->toArray();

        Employee::query()->whereIn('id', $userIds)->update([
            'manager_id' => $generalManager->id,
            'line_manager_id' => $lineManager->id
        ]);
    }

    protected function createEmployee(User $user)
    {
        $empDetails = Employee::query()->where('user_id', $user->id)->first();
        if (!empty($empDetails)) {
            $empDetails->first_name = 'Flash';
            $empDetails->job_title = 'Digital Ninja';
            $empDetails->status = 'active';
            $empDetails->employment_id = optional(Employment::find(3))->id;
            $empDetails->location_id = optional(Employment::find(2))->id;
            $empDetails->division_id = optional(Employment::find(2))->id;
            $lastName = 'Gordon';

            switch ($user->username) {
                case 'flashgordon_admin':
                    $lastName .= '-Admin';
                    break;
                case 'flashgordon_linemanager':
                    $lastName .= '-LineManager';
                    break;
                case 'flashgordon_user':
                    $lastName .= '-User';
                    break;
                case 'flashgordon_generalmanager':
                    $lastName .= '-GeneralManager';
                    break;
                case 'flashgordon_hr':
                    $lastName .= '-HR';
                    break;
                case 'flashgordon_finance':
                    $lastName .= '-Finance';
                    break;
                case 'flashgordon_ceo':
                    $lastName .= '-CEO';
                    break;
                case 'flashgordon_board':
                    $lastName .= '-Board';
                    break;
                case 'flashgordon_applicant':
                    $lastName .= '-Applicant';
                    break;
            }

            $empDetails->last_name = $lastName;

            $empDetails->save();
        }

    }
}

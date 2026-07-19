<?php

namespace App\Console\Commands;

use App\Core\v1\Employee\Models\Employee;
use App\Jobs\SyncMyobEmployeeInfoJob;
use App\Models\Role;
use App\Models\User;
use App\Services\Myob\DataMaps\EmployeeMap;
use Illuminate\Console\Command;
use App\Services\Myob\MyobApiService;

class SyncMyobEmployeesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'myob:sync-employees';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $myobApi = new MyobApiService;
        $employeeMap = new EmployeeMap;
        $myobEmployees = $myobApi->getEmployees()->call();

        foreach ($myobEmployees as $myobEmployee) {
            $this->info("Syncing {$myobEmployee->employeeID} - {$myobEmployee->employeeName}...");
            $user = $this->createUser($myobEmployee->email, $myobEmployee->employeeName, $myobEmployee->employeeID);
            $employee = Employee::query()->where('user_id', $user->id)->first();
            if (empty($employee)) {
                $employee = new Employee;
            }
            $employee = $employeeMap->map($employee, $myobEmployee);
            $employee->user_id = $user->id;
            $employee->save();

            try {
                SyncMyobEmployeeInfoJob::dispatch($user->id);
            } catch (\Exception $exception) {
                $this->error('Failed syncing employee info.');
                $this->error("[{$myobEmployee->employeeID} - {$myobEmployee->employeeName}]");
                $this->error($exception->getMessage());
            }

            $this->info("Success!");
        }
    }

    private function createUser(string|null $email, string $name, string $employeeId): User
    {
        $user = User::query()->where('username', $email)->orWhere('username', $employeeId)->first();

        if (empty($user)) {
            $password = strtolower(str_replace(' ', '', trim($name)));
            // Every employee defaults to User role first
            $role = Role::query()->where('name', 'User')->first();
            $user = new User;
            $user->name = $name;
            $user->username = empty($email) ? $employeeId : $email;
            $user->password = bcrypt($password);
            $user->role_id = $role->id;
            $user->save();
        }

        return $user;
    }
}

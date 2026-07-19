<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Myob\DataMaps\EmployeeMap;
use App\Services\Myob\MyobApiService;
use Illuminate\Console\Command;

class UpserMyobEmployeeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'myob:upsert employee {user_id}';

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
        $userId = $this->argument('user_id');

        $user = User::find($userId);

        if (empty($user)) {
            $this->error('Invalid user.');
            return false;
        }

        $myobApi = new MyobApiService();
        $employeeDataMap = new EmployeeMap();
        $employee = $user->employee;

        $data = [];

        foreach ($employeeDataMap->fields() as $key => $val) {
            $data[$val] = $employee->{$key};
        }

        $myobApi->createOrUpdateEmployee()->call($data);
        $this->info("Myob Employee details updated for [{$employee->employee_number}] {$employee->first_name} {$employee->last_name}");
    }
}

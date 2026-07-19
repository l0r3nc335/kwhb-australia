<?php

namespace App\Console\Commands;

use App\Models\Division;
use App\Models\Employee;
use App\Models\Employment;
use App\Models\Location;
use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class CreateTestUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:test-user {username} {password} {role}';

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
        $username = $this->argument('username');
        $password = $this->argument('password');
        $roleName = $this->argument('role');

        $user = User::query()->where('username', $username)->first();

        if (!empty($user)) {
            $this->error('Username already exist');
            return false;
        }

        $role = Role::query()->where('name', $roleName)->first();
        if (empty($role)) {
            $this->error('Invalid role name');
            return false;
        }

        $division = Division::first();
        $location = Location::first();
        $employment = Employment::first();

        $employee = new Employee;
        $employee->first_name = fake()->firstName;
        $employee->last_name = fake()->lastName;
        $employee->job_title = fake()->jobTitle;
        $employee->status = 'active';
        $employee->division_id = $division->id;
        $employee->location_id = $location->id;
        $employee->employment_id = $employment->id;

        $user = new User;
        $user->name = $employee->first_name . ' ' . $employee->last_name;
        $user->username = $username;
        $user->password = bcrypt($password);
        $user->role_id = $role->id;
        $user->save();

        $employee->user_id = $user->id;
        $employee->save();

        $this->info('Test User Created');

        // Sync to Myob
//        Artisan::call("myob:upsert employee {$user->id}");

        return true;
    }
}

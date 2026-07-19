<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Employment;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class TestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create users for Paul
        Artisan::call("create:test-user pauladmin password Administrator");
        Artisan::call("create:test-user paulmanager password 'Line Manager'");
        Artisan::call("create:test-user pauluser password User");
        Artisan::call("create:test-user paulsuperuser password 'Super User'");

        // Create users for Bel
        Artisan::call("create:test-user beladmin password Administrator");
        Artisan::call("create:test-user belmanager password 'Line Manager'");
        Artisan::call("create:test-user beluser password User");
        Artisan::call("create:test-user belsuperuser password 'Super User'");

        // Create users for Devs
        Artisan::call("create:test-user devadmin password Administrator");
        Artisan::call("create:test-user devmanager password 'Line Manager'");
        Artisan::call("create:test-user devuser password User");
        Artisan::call("create:test-user devsuperuser password 'Super User'");

        // Create UAT test users
        Artisan::call("create:test-user uat_admin uat!@#pass Administrator");
        Artisan::call("create:test-user uat_linemanager uat!@#pass 'Line Manager'");
        Artisan::call("create:test-user uat_user uat!@#pass User");
        Artisan::call("create:test-user uat_superuser uat!@#pass 'Super User'");
        Artisan::call("create:test-user uat_generalmanager uat!@#pass 'General Manager'");
        Artisan::call("create:test-user uat_hr uat!@#pass 'HR'");
        Artisan::call("create:test-user uat_finance uat!@#pass 'Finance'");
        Artisan::call("create:test-user uat_ceo uat!@#pass 'CEO'");
        Artisan::call("create:test-user uat_board uat!@#pass 'Board'");
        Artisan::call("create:test-user uat_applicant uat!@#pass 'Applicant'");
        Artisan::call("create:test-user uat_board uat!@#pass 'Board'");

        // Create UAT - Client Request
        Artisan::call("create:test-user flashgordon password 'Super User'");
        $flashgordonUser = User::query()->where('username', 'flashgordon')->first();
        if (!empty($flashgordonUser)) {
            $empDetails = Employee::query()->where('user_id', $flashgordonUser->id)->first();
            if (!empty($empDetails)) {
                $empDetails->first_name = 'Flash';
                $empDetails->last_name = 'Gordon';
                $empDetails->job_title = 'Manager';
                $empDetails->status = 'active';
                $empDetails->employment_id = optional(Employment::find(3))->id;
                $empDetails->location_id = optional(Employment::find(2))->id;
                $empDetails->division_id = optional(Employment::find(2))->id;
                $empDetails->save();
            }
        }
    }
}

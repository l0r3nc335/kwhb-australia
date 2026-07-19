<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use App\Models\User;
use App\Models\Employee;
use App\Models\Location;
use App\Models\Division;
use App\Models\Employment;
use App\Models\Position;
use App\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Division::create(['id' => 1, 'name' => 'CEO']);
        Division::create(['id' => 2, 'name' => 'Corporate']);
        Division::create(['id' => 3, 'name' => 'Clinical']);
        Division::create(['id' => 4, 'name' => 'Program']);
        Division::create(['id' => 5, 'name' => 'Community']);
        Division::create(['id' => 6, 'name' => 'Board']);

        Location::create(['id' => 1, 'name' => 'Darwin']);
        Location::create(['id' => 2, 'name' => 'Katherine']);
        Location::create(['id' => 3, 'name' => 'Kalkaringi']);
        Location::create(['id' => 4, 'name' => 'Lajamanu']);
        Location::create(['id' => 5, 'name' => 'Timber Creek']);
        Location::create(['id' => 6, 'name' => 'Yarralin']);

        Employment::create(['id' => 1, 'name' => 'Volunteer']);
        Employment::create(['id' => 2, 'name' => 'Casual']);
        Employment::create(['id' => 3, 'name' => 'Full Time']);
        Employment::create(['id' => 4, 'name' => 'PART-TIME 0.2']);
        Employment::create(['id' => 5, 'name' => 'PART-TIME 0.4']);
        Employment::create(['id' => 6, 'name' => 'PART-TIME 0.6']);
        Employment::create(['id' => 7, 'name' => 'PART-TIME 0.8']);
        Employment::create(['id' => 8, 'name' => 'Board']);

        Position::create(['id' => 1, 'name' => 'Chief Executive Officer (CEO)']);
        Position::create(['id' => 2, 'name' => 'Director of Clinical Services']);
        Position::create(['id' => 3, 'name' => 'Head of Administration']);
        Position::create(['id' => 4, 'name' => 'Chief Financial Officer (CFO)']);
        Position::create(['id' => 5, 'name' => 'Clinical Operations Manager']);
        Position::create(['id' => 6, 'name' => 'Director of Patient Services']);

        User::create([
            'id' => 1,
            'name' => 'Admin',
            'username' => 'admin',
            'password' => bcrypt('admin123'),
        ]);

        Employee::create([
            'id' => 1,
            'user_id' => 1,
            'first_name' => 'my firstname',
            'last_name' => 'my lastname',
            'gender' => 'male',
            'job_title' => 'developer',
            'status' => 'active',
            'location_id' => 1,
            'division_id' => 1,
            'employment_id' => 1,
        ]);
        $this->call([
            UsersTableSeeder::class,
            LeaveTypeSeeder::class,
        ]);
    }
}

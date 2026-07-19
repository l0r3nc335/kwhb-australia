<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Employee;
use App\Models\Role;
use App\Models\UserRole;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Artisan;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        for ($i = 0; $i < 100; $i++) {
            $randomNumber1 = rand(1, 2);
            $randomNumber2 = rand(2, 3);
            $randomNumber3 = mt_rand(1, 6);

            $year = rand(2000, 2023);
            $month = rand(1, 12);
            $day = rand(1, 28);
            $randomDate = sprintf('%04d-%02d-%02d', $year, $month, $day);

            $user = User::create([
                'name' => $faker->name,
                'username' => $faker->userName,
                'password' => bcrypt('password'),
            ]);

            Employee::create([
                'user_id' => $user->id,
                'first_name' => $faker->firstName,
                'last_name' => $faker->lastName,
                'gender' => $faker->randomElement(['Male', 'Female']),
                'job_title' => $faker->jobTitle,
                'status' => $faker->randomElement(['active', 'pending', 'offswing', 'extended leave', 'terminated']),
                'location_id' => $randomNumber1,
                'division_id' => $randomNumber2,
                'employment_id' => $randomNumber3,
                'date_hired' => $randomDate,
            ]);

            try {
                Artisan::call("myob:upsert employee {$user->id}");
            } catch (\Exception) {
                //
            }
        }
        $users = User::all();
        foreach ($users as $user) {
            UserRole::create([
                'user_id' => $user->id,
                'role_id' => rand(1, 6)
            ]);
        }
    }
}

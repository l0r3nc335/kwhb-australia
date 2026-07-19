<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\LeaveType;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;

class LeaveTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        LeaveType::create(['id' => 1, 'name' => 'Annual Leave']);
        LeaveType::create(['id' => 2, 'name' => 'Personal Leave']);
        LeaveType::create(['id' => 3, 'name' => 'Leave without pay']);
        LeaveType::create(['id' => 4, 'name' => 'Compassionate Leave']);
        LeaveType::create(['id' => 5, 'name' => 'Ceremonial Leave']);
        LeaveType::create(['id' => 6, 'name' => 'Special/Sorry Leave']);
        LeaveType::create(['id' => 7, 'name' => 'FOIL']);
        LeaveType::create(['id' => 8, 'name' => 'Parental Leave']);
        LeaveType::create(['id' => 9, 'name' => 'Jury Service Leave']);
        LeaveType::create(['id' => 10, 'name' => 'Defense Reserve Leave']);
        LeaveType::create(['id' => 11, 'name' => 'Long Service Leave']);
        //maternity and paternity leave?

        $employees = Employee::all();
        $leaveTypes = LeaveType::all();

        foreach ($employees as $employee) {
            foreach ($leaveTypes as $leaveType) {
                DB::table('leave_balances')->insert([
                    'employee_id' => $employee->id,
                    'leave_type_id' => $leaveType->id,
                    'balance' => rand(5, 20),
                    'availed' => rand(0, 5),
                    'enroll_year' => date('Y'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}

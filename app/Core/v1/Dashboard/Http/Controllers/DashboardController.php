<?php

namespace App\Core\v1\Dashboard\Http\Controllers;

use App\Core\v1\Leave\Repositories\LeaveRepository;
use App\Core\v1\Timesheet\Repositories\TimesheetRepository;
use App\Http\Controllers\Controller;
use App\Models\EmployeeCompliance;
use App\Models\EmployeeVaccination;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        protected LeaveRepository $leaveRepository,
        protected TimesheetRepository $timesheetRepository)
    {
    }

    public function index(Request $request)
    {
        $leaveRequestData = [];
        $timesheetData = [];
        $expiredVaccineData = [];
        $expiredComplianceData = [];

        for ($i=1; $i<=12; $i++) {
            $month = Carbon::now()->setMonth($i);
            $start = $month->copy()->startOfMonth()->format('Y-m-d');
            $end = $month->copy()->endOfMonth()->format('Y-m-d');

            // Leave Requests
            $leaveRequestQuery = $this->leaveRepository->newQuery();
            $leaveRequestData[] = $leaveRequestQuery->filterDate('date', $start, '>=')->filterDate('date', $end, '<=')->count();
            // Timesheets
            $timesheetQuery = $this->timesheetRepository->newQuery();
            $timesheetData[] = $timesheetQuery->filterDate('date', $start, '>=')->filterDate('date', $end, '<=')->count();
            // Expired Vaccines
            $expiredVaccineData[] = EmployeeVaccination::query()->where('expiry_date', '>=', $start)->where('expiry_date', '<=', $end)->count();
            // Expired Compliance
            $expiredComplianceData[] = EmployeeCompliance::query()->where('expiry_date', '>=', $start)->where('expiry_date', '<=', $end)->count();
        }

        return response()->json([
            'leave_requests' => $leaveRequestData,
            'timesheets' => $timesheetData,
            'expired_vaccinations' => $expiredVaccineData,
            'expired_compliance' => $expiredComplianceData
        ]);
    }
}

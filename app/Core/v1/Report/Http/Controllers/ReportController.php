<?php

namespace App\Core\v1\Report\Http\Controllers;

use App\Core\v1\Report\Repositories\ReportRepository;
use App\Http\Controllers\AppController;
use Illuminate\Http\Request;

class ReportController extends AppController
{
    public function __construct(protected $repository = new ReportRepository)
    {
        parent::__construct();
    }

    public function indexGraph(Request $request)
    {
        $dateRangeType = $request->get('date_range_type', 'weekly');
        $dateRangeIndex = $request->get('date_range_index', 0);
        $start = $request->get('start');
        $end = $request->get('end');

        return response()->json([
            [
                'label' => 'People',
                'data' => $this->repository
                    ->setDateRangeType($dateRangeType)
                    ->setDateStart($start)
                    ->setDateEnd($end)
                    ->peopleReports(true)
            ],
            [
                'label' => 'Timesheets',
                'data' => $this->repository
                    ->setDateRangeType($dateRangeType)
                    ->setDateStart($start)
                    ->setDateEnd($end)
                    ->timesheetReports(true)
            ],
            [
                'label' => 'Leave',
                'data' => $this->repository
                    ->setDateRangeType($dateRangeType)
                    ->setDateStart($start)
                    ->setDateEnd($end)
                    ->leaveReports(true)
            ],
            [
                'label' => 'Letter of Offer',
                'data' => $this->repository
                    ->setDateRangeType($dateRangeType)
                    ->setDateStart($start)
                    ->setDateEnd($end)
                    ->letterOfOfferReports(true)
            ],
            [
                'label' => 'Contracts',
                'data' => $this->repository
                    ->setDateRangeType($dateRangeType)
                    ->setDateStart($start)
                    ->setDateEnd($end)
                    ->contractReports(true)
            ],
            [
                'label' => 'Vaccination',
                'data' => $this->repository
                    ->setDateRangeType($dateRangeType)
                    ->setDateStart($start)
                    ->setDateEnd($end)
                    ->vaccinationReports(true)
            ],
            [
                'label' => 'Compliance',
                'data' => $this->repository
                    ->setDateRangeType($dateRangeType)
                    ->setDateStart($start)
                    ->setDateEnd($end)
                    ->complianceReports(true)
            ],
        ]);
    }

    public function index()
    {
        return response()->json([
            [
                'label' => 'People',
                'data' => $this->repository->peopleReports()
            ],
            [
                'label' => 'Timesheets',
                'data' => $this->repository->timesheetReports()
            ],
            [
                'label' => 'Leave',
                'data' => $this->repository->leaveReports()
            ],
            [
                'label' => 'Letter of Offer',
                'data' => $this->repository->letterOfOfferReports()
            ],
            [
                'label' => 'Contracts',
                'data' => $this->repository->contractReports()
            ],
            [
                'label' => 'Vaccination',
                'data' => $this->repository->vaccinationReports()
            ],
            [
                'label' => 'Compliance',
                'data' => $this->repository->complianceReports()
            ],
        ]);
    }
}

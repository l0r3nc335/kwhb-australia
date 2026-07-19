<?php

namespace App\Core\v1\Employee\Http\Controllers;

use App\Core\v1\Employee\Http\Requests\StoreRequest;
use App\Core\v1\Employee\Repositories\EmployeeRepository;
use App\Core\v1\Permission\Models\Permission;
use App\Http\Controllers\AppController;
use App\Repositories\ModelRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeController extends AppController
{
    public function __construct(protected ModelRepository $repository = new EmployeeRepository)
    {
        parent::__construct();
    }

    public function reports(Request $request)
    {
        return response()->json([
            'reports' => $this->repository->reports()
        ]);
    }

    public function getJobTitles()
    {
        return response()->json(
            $this->repository->jobTitles()
        );
    }

    public function getStatuses()
    {
        return response()->json(
            $this->repository->statuses()
        );
    }

    public function all(Request $request)
    {
        $this->authService->allowed(
            Permission::FEATURE_PEOPLE,
            Permission::SUB_FEATURES[Permission::FEATURE_PEOPLE]['employee_dashboard'],
            Permission::ACTION_VIEW
        );

        $locationId = $request->get('location_id');
        $divisionId = $request->get('division_id');

        $query = $this->repository->useAuthService($this->authService);
        $query = $query->newQuery(true);

        if (!empty($locationId)) {
            $query = $query->filter(['location_id' => $locationId], true, true);
        }

        if (!empty($divisionId)) {
            $query = $query->filter(['division_id' => $divisionId], true, true);
        }

        return response()->json(
            $query->query->select([
                DB::raw('users.id as user_id'),
                DB::raw('employees.id as employee_id'),
                DB::raw("concat(first_name, ' ', last_name) as name"),
                'job_title',
                'role_id',
                'division_id',
                'location_id',
            ])->join('users', 'users.id', '=', 'employees.user_id')->get()
        );
    }

    public function index(Request $request)
    {
        $this->authService->allowed(
            Permission::FEATURE_PEOPLE,
            Permission::SUB_FEATURES[Permission::FEATURE_PEOPLE]['employee_dashboard'],
            Permission::ACTION_VIEW
        );

        $paginate = $request->get('itemsPerPage', $request->get('per_page'));
        $paginate = ($paginate == -1) ? null : $paginate;
        $search = $request->get('search');
        $filters = [];
        $data = $request->get('data');
        $roleId = $request->get('role_id') ??  ($data['role_id'] ?? null);
        $roleId = ($roleId != 'Select Role') ? $roleId : null;
        $requestFilters = $request->get('data');
        $location = $request->get('location_id');
        $division = $request->get('division_id');
        $employment = $request->get('employment_id');
        $jobTitle = $request->get('job_title');
        $status = $request->get('status');


        if (isset($requestFilters['division_id']) && is_numeric($requestFilters['division_id'])) {
            $filters['division_id'] = $requestFilters['division_id'];
        }

        if (isset($requestFilters['status']) && $requestFilters['status'] != 'Select Status' && $requestFilters['status'] != 'all') {
            $filters['status'] = $requestFilters['status'];
        }

        if (!empty($location)) {
            $filters['location_id'] = $location;
        }

        if (!empty($division)) {
            $filters['division_id'] = $division;
        }

        if (!empty($employment)) {
            $filters['employment_id'] = $employment;
        }

        if (!empty($jobTitle)) {
            $filters['job_title'] = $jobTitle;
        }

        if (!empty($status)) {
            $filters['status'] = $status;
        }


        $data = $this->repository
            ->useAuthService($this->authService)
            ->newQuery(true, 'employees.user_id')
            ->filter($filters, true, true)
            ->search($search, ['first_name', 'last_name'])
            ->query
            ->with([
                'user.user_role.role',
                'division',
                'employment',
                'bank_info',
                'address',
                'location',
                'emergency_contact',
                'medical',
                'profile_image',
                'cover_image',
                'manager'
            ])->orderBy('id', 'desc');

        if (!empty($roleId)) {
            $data = $data->whereHas('user.role', function ($q) use ($roleId) {
                $q->where('id', $roleId);
            });
        }

        if (is_null($paginate)) {
            return response()->json(['data' => $data->get()]);
        } else {
            return response()->json($data->paginate($paginate));
        }
    }

    public function item($id)
    {
        $employee = $this->repository->find($id);

        if (empty($employee)) {
            return response()->json([
                'message' => 'Employee not found.'
            ], 404);
        }

        return response()->json([
            'employee' => $employee
        ]);
    }

    public function getApprovers(Request $request, $id)
    {
        $employee = $this->repository->find($id, true);

        $approvers = !empty($employee) ? $employee->approvers() : [];

        return response()->json(
            $approvers
        );
    }

    public function getLineManagers($id)
    {
        return response()->json(
            $this->repository->find($id, true)->lineManagers()
        );
    }

    public function store(Request $request)
    {
        $employee = $this->repository->save($request->all());

        return response()->json($employee);
    }

    public function update(Request $request, $id)
    {
        $employee = $this->repository->find($id);

        if (empty($employee)) {
            return response()->json([
                'message' => 'Employee not found.'
            ], 404);
        }

        $updatedEmployee = $this->repository
            ->use($employee)
            ->update($request->all());

        return response()->json([
            'employee' => $updatedEmployee
        ]);
    }

    public function updateMedicalHistories(Request $request, $id)
    {
        $employee = $this->repository->find($id);

        if (empty($employee)) {
            return response()->json([
                'message' => 'Employee not found.'
            ], 404);
        }

        $this->repository->use($employee)->updateMedical($request->all());

        return response()->json([
            'medical_history' => $employee->refresh()->medical()->first(),
            'message' => 'Employee medical updated.'
        ]);
    }

    public function updateEmergencyContact(Request $request, $id)
    {
        $employee = $this->repository->find($id);

        if (empty($employee)) {
            return response()->json([
                'message' => 'Employee not found.'
            ], 404);
        }

        $this->repository->use($employee)->updateEmergencyContact($request->all());

        return response()->json([
            'medical_history' => $employee->refresh()->emergency_contact()->first(),
            'message' => 'Employee emergency contact updated.'
        ]);
    }

    public function updateAddress(Request $request, $id)
    {
        $employee = $this->repository->find($id);

        if (empty($employee)) {
            return response()->json([
                'message' => 'Employee not found.'
            ], 404);
        }

        $this->repository->use($employee)->updateAddress($request->all());

        return response()->json([
            'medical_history' => $employee->refresh()->address()->first(),
            'message' => 'Employee address updated.'
        ]);
    }
}

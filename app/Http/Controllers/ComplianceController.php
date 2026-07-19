<?php

namespace App\Http\Controllers;

use App\Core\v1\Division\Models\Division;
use App\Core\v1\Role\Models\Role;
use App\Models\Compliance;
use App\Http\Requests\StoreComplianceRequest;
use App\Http\Requests\UpdateComplianceRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Core\v1\User\Http\Controllers\UserController;
use Illuminate\Support\Facades\DB;

class ComplianceController extends Controller
{
    public function index()
    {
        $tbl = Compliance::where('is_active', 1)->get();
        return response()->json($tbl);
    }

    public function create(StoreComplianceRequest $request)
    {
        $data = $request->validated();

        if ($data['validity'] == 'Custom') {
            $data['custom_expiry'] = Carbon::parse($request->get('custom_expiry'))->format('Y-m-d');
        }

        Compliance::create($data);

        $divisionArray = explode(",", $data['division']);

        if($data['required'] === "1" || $data['required'] === 1) {
            $this->autoCreateEmployeeCompliance($data['name'], $divisionArray);
        }


        return response()->json(array('message' => "Compliance successfully saved"));
    }

    private function autoCreateEmployeeCompliance(string $type, array $divisions)
    {
        $userIds = UserController::getUserIdsByDivisions($divisions);

        $employeeRequiredCompliance = [];

        foreach ($userIds as $userId) {
            $employeeRequiredCompliance[] = [
                'user_id' => $userId,
                'compliance' => $type,
                'file_status' => "Required",
                'assignee_user_id' => $userId,
            ];
        }

        DB::table('employee_compliances')->insert($employeeRequiredCompliance);
    }

    public function update(UpdateComplianceRequest $request)
    {

        $data = $request->validated();
        Compliance::where('id', $data['id'])->update($data);

        return response()->json(array('message' => "Compliance successfully updated"));
    }

    public function getComplianceByRole(Request $request)
    {
        $role = $request->get('role');
        $query = Compliance::query()->select('id');

        if (!empty($role)) {
            $roles = explode(',', $role);

            $query->where(function ($q) use ($roles) {
                foreach ($roles as $r) {
                    $q->orWhere('role', 'LIKE', "%$r%");
                }
            });
        }

        $data = $query->pluck('id')->map(fn($id) => ['id' => $id])->values();

        return response()->json([
            'data' => $data
        ]);
    }

    public function compliance(Request $request)
    {
        $limit = $request->get('itemsPerPage');
        $page = $request->page;
        //$offset = ($page - 1) * $limit;

        if ($page === 1) {
            $offset = 0;
        }
        if ($page > 1 ) {
            $offset = (($page - 1) * $limit) ;
        }

        $query = Compliance::query();

        if ($request->type) {
            $query->where('name', $request->type);
        }

        if ($request->validity) {
            $query->where('validity', $request->validity);
        }

        if ($request->requirement) {
            $req = $request->requirement == 'Mandatory' ? 0 : 1;
            $query->where('required', $req);
        }



        if ($request->division) {
            if($request->division === "All"){
                $divisionCount = count(Division::all());

                $query->whereRaw("
                    (LENGTH(REPLACE(division, ' ', '')) - LENGTH(REPLACE(REPLACE(division, ' ', ''), ',', '')) + 1) = ?
                ", [$divisionCount]);
            }else{
                $query->where('division', 'LIKE', '%' . $request->division . '%');
            }
        }

        if ($request->role) {
            if($request->role === "All"){
                $roleCount = 6; //TODO: hardcoded count(Role::all());
                $query->whereRaw("
                    (LENGTH(REPLACE(role, ' ', '')) - LENGTH(REPLACE(REPLACE(role, ' ', ''), ',', '')) + 1) = ?
                ", [$roleCount]);
            }else{
                $query->where('role', 'LIKE', '%' . $request->role . '%');
            }
        }

        $total = $query->count();
        $data = $query->offset($offset)->limit($limit)->get();
        
        return response()->json([
            'data' => $data,
            'total' => $total
        ]);
    }

    public function delete($id)
    {
        Compliance::where('id', $id)->update(['is_active' => 0]);
        return response()->json(array('message' => "Compliance successfully deleted"));
    }
}

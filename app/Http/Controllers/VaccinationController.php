<?php

namespace App\Http\Controllers;

use App\Models\Vaccination;
use App\Http\Requests\StoreVaccinationRequest;
use App\Http\Requests\UpdateVaccinationRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Core\v1\User\Http\Controllers\UserController;

class VaccinationController extends Controller
{
    public function index()
    {
        $tbl = Vaccination::where('is_active', 1)->get();
        return response()->json($tbl);
    }

    public function create(StoreVaccinationRequest $request)
    {
        $data = $request->validated();

        if ($data['validity'] == 'Custom') {
            $data['custom_expiry'] = Carbon::parse($request->get('custom_expiry'))->format('Y-m-d');
        }

        Vaccination::create($data);


        
        $divisionArray = explode(",", $data['division']);

        if($data['required'] === "1" || $data['required'] === 1) {
            $this->autoCreateEmployeeVaccination($data['name'], $divisionArray);
        }

        return response()->json(array('message' => "Vaccination successfully saved"));
    }


    private function autoCreateEmployeeVaccination(string $type, array $divisions)
    {
        $userIds = UserController::getUserIdsByDivisions($divisions);

        $employeeVaccinationRequired = [];

        foreach ($userIds as $userId) {
            $employeeVaccinationRequired[] = [
                'user_id' => $userId,
                'vaccination' => $type,
                'file_status' => "Required",
                'assignee_user_id' => $userId,
            ];
        }

        DB::table('employee_vaccinations')->insert($employeeVaccinationRequired);
    }

    public function update(UpdateVaccinationRequest $request)
    {
        $data = $request->validated();
        if ($data['validity'] == 'Custom') {
            $data['custom_expiry'] = Carbon::parse($request->get('custom_expiry'))->format('Y-m-d');
        }

        Vaccination::where('id', $data['id'])->update($data);

        return response()->json(array('message' => "Vaccination successfully updated"));
    }

    public function vaccination(Request $request)
    {
        $limit = $request->get('itemsPerPage');
         $page = $request->page; //$request->input('page', 1);
        //$offset = ($page) * $limit;
        
        if ($page === 1) {
            $offset = 0;
        }
        if ($page > 1 ) {
            $offset = (($page - 1) * $limit) ;
        }


        $query = Vaccination::query();
        $total = $query->count();
        $data = $query->offset($offset)->limit($limit)->get();
        
        return response()->json([
            'data' => $data,
            'total' => $total
        ]);
    }

    public function getVaccinationsByRole(Request $request)
    {
        $role = $request->get('role');
        $query = Vaccination::query();//->select('id');

        if (!empty($role)) {
            $roles = explode(',', $role);

            $query->where(function ($q) use ($roles) {
                foreach ($roles as $r) {
                    $q->orWhere('role', 'LIKE', "%$r%");
                }
            });
        }

        // Ensure each item is an object with key 'name'
        //$data = $query->pluck('name')->map(fn($name) => ['name' => $name])->values();
        $data = $query->get(); //->pluck('id')->map(fn($id) => ['id' => $id])->values();

        return response()->json([
            'data' => $data
        ]);
    }

    public function delete($id)
    {
        Vaccination::where('id', $id)->update(['is_active' => 0]);
        return response()->json(array('message' => "Vaccination successfully deleted"));
    }
}

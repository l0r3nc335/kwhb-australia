<?php

namespace App\Http\Controllers;

use App\Models\Employment;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use App\Helpers\DataResponse;
use App\Http\Requests\StoreEmploymentRequest;
use App\Http\Requests\UpdateEmploymentRequest;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EmploymentController extends Controller
{

    // private $user;
    // public function __construct()
    // {
    //     $this->user = Auth::user();
    // }
    public function index()
    {
        $tbl = Employment::where('is_active', 1)->orderBy('name', 'asc')->get();
        return response()->json($tbl);
    }

    public function create()
    {
        //
    }


    public function employment(Request $request)
    {
        $limit = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $offset = ($page - 1) * $limit;

        $query = Employment::query()->where('is_active', 1)->orderBy('name', 'asc');
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $searchTerm = '%' . $request->search . '%';

                $q->where('name', 'like', $searchTerm);
            });
        }
        $data = $query->offset($offset)->limit($limit)->get();
        $total = $data->count();

        return response()->json([
            'data' => $data,
            'total' => $total
        ]);
    }

    public function store(StoreEmploymentRequest $request)
    {
        $data = $request->validated();
        Employment::create($data);

        return response()->json(array('message' => "Employment successfully saved"));
    }


    public function show(Employee $Employee)
    {
        //
    }

    public function edit(Employee $Employee)
    {
        //
    }

    public function update(UpdateEmploymentRequest $request)
    {
        $data = $request->validated();
        // $model = Employment::where('name', $data['name'])->where('is_active', 1)->first();
        // if ($model) {
        //     Employment::where('id', $data['id'])->update($data);
        // } else {
        //     Employment::where('id', $data['id'])->update(['is_active' => 0]);
        //     Employment::create(['name' => $data['name']]);
        // }
        Employment::where('id', $data['id'])->update($data);

        return response()->json(array('message' => "Employment successfully updated"));
    }

    public function destroy($id)
    {
        Employment::where('id', $id)->update(['is_active' => 0]);
        return response()->json(array('message' => "Employment successfully archived"));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Division;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use App\Http\Requests\StoreDivisionRequest;
use App\Http\Requests\UpdateDivisionRequest;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DivisionController extends Controller
{

    // private $user;
    // public function __construct()
    // {
    //     $this->user = Auth::user();
    // }
    public function index()
    {
        $tbl = Division::where('is_active', 1)->orderBy('name', 'asc')->get();
        return response()->json($tbl);
    }

    public function create()
    {
        //
    }

    public function division(Request $request)
    {
        $limit = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $offset = ($page - 1) * $limit;

        $query = Division::query()->where('is_active', 1)->orderBy('name', 'asc');
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

    public function store(StoreDivisionRequest $request)
    {
        $data = $request->validated();
        Division::create($data);

        return response()->json(array('message' => "Division successfully saved"));
    }


    public function show(Employee $Employee)
    {
        //
    }

    public function edit(Employee $Employee)
    {
        //
    }

    public function update(UpdateDivisionRequest $request)
    {
        $data = $request->validated();
        // $model = Division::where('name', $data['name'])->where('is_active', 1)->first();
        // if ($model) {
        //     Division::where('id', $data['id'])->update($data);
        // } else {
        //     Division::where('id', $data['id'])->update(['is_active' => 0]);
        //     Division::create(['name' => $data['name']]);
        // }
        Division::where('id', $data['id'])->update($data);

        return response()->json(array('message' => "Division successfully updated"));
    }

    public function destroy($id)
    {
        Division::where('id', $id)->update(['is_active' => 0]);
        return response()->json(array('message' => "Division successfully archived"));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use App\Http\Requests\StoreLocationRequest;
use App\Http\Requests\UpdateLocationRequest;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Core\v1\Employee\Models\Employee;

class LocationController extends Controller
{

    // private $user;
    // public function __construct()
    // {
    //     $this->user = Auth::user();
    // }
    public function index()
    {
        $tbl = Location::where('is_active', 1)->orderBy('name', 'asc')->get();
        return response()->json($tbl);
    }

    public function activeIndex()
    {
        $today = \Carbon\Carbon::today()->toDateString();

        $tbl = Location::where('is_active', 1)
            ->where(function ($q) use ($today) {
                $q->whereNull('valid_from')
                ->orWhereDate('valid_from', '<=', $today);
            })
            ->orderBy('name', 'asc')
            ->get();

        return response()->json($tbl);
    }


    public function create()
    {
        //
    }

    public function location(Request $request)
    {
        $limit = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $offset = ($page - 1) * $limit;

        $query = Location::query()->where('is_active', 1)->orderBy('name', 'asc');
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

    public function store(StoreLocationRequest $request)
    {
        $data = $request->validated();
        Location::create($data);

        return response()->json(array('message' => "Location successfully saved"));
    }


    public function show(Employee $Employee)
    {
        //
    }

    public function edit(Employee $Employee)
    {
        //
    }

    public function update(UpdateLocationRequest $request)
    {
        $data = $request->validated();
        // $model = Location::where('name', $data['name'])->where('is_active', 1)->first();
        // if ($model) {
        //     Location::where('id', $data['id'])->update($data);
        // } else {
        //     Location::where('id', $data['id'])->update(['is_active' => 0]);
        //     Location::create(['name' => $data['name']]);
        // }
        Location::where('id', $data['id'])->update($data);

        return response()->json(array('message' => "Location successfully updated"));
    }

    public function destroy($id)
    {
        Location::where('id', $id)->update(['is_active' => 0]);
        return response()->json(array('message' => "Location successfully archived"));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Http\Requests\StorePositionRequest;
use App\Http\Requests\UpdatePositionRequest;
use App\Models\Position;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    public function index()
    {
        $tbl = Position::with('payClassificationsData')
            ->where('is_active', 1)
            ->orderBy('name', 'asc')
            ->get();
        return response()->json($tbl);
    }

    public function activeIndex()
    {
        $today = \Carbon\Carbon::today()->toDateString();

        $tbl = Position::where('is_active', 1)
            ->where(function ($q) use ($today) {
                $q->whereNull('start_date')
                ->orWhereDate('start_date', '<=', $today);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('end_date')
                ->orWhereDate('end_date', '>=', $today);
            })
            ->orderBy('name', 'asc')
            ->get();

        return response()->json($tbl);
    }



    public function employees(Request $request)
    {
        $employee = Employee::where('job_title', $request->position)->get();
        return response()->json($employee);
    }

    public function position(Request $request)
    {
        $limit = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $offset = ($page - 1) * $limit;

        $query = Position::query()->where('is_active', 1)->orderBy('name', 'asc');
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $searchTerm = '%' . $request->search . '%';

                $q->where('name', 'like', $searchTerm);
            });
        }
        $data = $query->offset($offset)->limit($limit);
        $total = $data->count();

        return response()->json([
            'data' => $data->get(),
            'total' => $total
        ]);
    }

    public function store(StorePositionRequest $request)
    {
        $data = $request->validated();
        Position::create($data);

        return response()->json(array('message' => "Job Title successfully saved"));
    }

    public function update(UpdatePositionRequest $request)
    {
        $data = $request->validated();

        $model = Position::where('name', $data['name'])->where('is_active', 1)->first();
        if ($model) {
            Position::where('id', $data['id'])->update($data);
        } else {
            if ($request->update_rule == 'update_all') {
                $position = Position::where('id', $data['id'])->first();

                Position::where('id', $data['id'])->update(['is_active' => 0]);
                Position::create(['name' => $data['name']]);

                $employees = Employee::where('job_title', $position->name)->pluck('id')->toArray();
                if ($employees) {
                    foreach ($employees as $item) {
                        Employee::where('id', $item)->update(['job_title' => $data['name']]);
                    }
                }
            } else if ($request->update_rule == 'update_none') {
                Position::where('id', $data['id'])->update(['is_active' => 0]);
                Position::create(['name' => $data['name']]);
            } else if ($request->update_rule == 'employee') {
                Position::where('id', $data['id'])->update(['is_active' => 0]);
                Position::create(['name' => $data['name']]);

                $ids = explode(',', $request->employee_ids);
                foreach ($ids as $item) {
                    Employee::where('id', $item)->update(['job_title' => $data['name']]);
                }
            }
        }

        return response()->json(array('message' => "Job Title successfully updated"));
    }

    public function destroy($id)
    {
        Position::where('id', $id)->update(['is_active' => 0]);
        return response()->json(array('message' => "Job Title successfully archived"));
    }
}

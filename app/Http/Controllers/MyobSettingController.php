<?php

namespace App\Http\Controllers;

use App\Models\MyobSetting;
use Illuminate\Http\Request;

class MyobSettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tbl = MyobSetting::all();
        return response()->json($tbl);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            MyobSetting::create($request->all());
            return $this->index();
        } catch (Exception $ex) {

            return response()->json(['error' => $ex->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MyobSetting $myobSetting)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $cmd  = MyobSetting::findOrFail($id);
            $input = $request->all();
            $cmd->fill($input)->save();
            return $this->index();
        } catch (Exception $ex) {
            return response()->json(['error' => $ex->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MyobSetting $myobSetting)
    {
        //
    }
}

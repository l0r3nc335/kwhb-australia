<?php

namespace App\Http\Controllers;

use App\Models\EmployeeCompliance;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class EmployeeComplianceController extends Controller
{
    public function create(Request $request)
    {
        $data = $request->all();
        $data['file_status'] = $request->file_status ? $request->file_status : 'Pending review';


        //if($data['no_expiry'] === "false") {
            $data['expiry_date'] = ($request->expiry_date === 'null' || $request->expiry_date === null || $request->expiry_date === 'Invalid date') ?
                null : Carbon::createFromFormat('d-m-Y', $request->expiry_date)->format('Y-m-d');
        //}

        if ($request->hasFile('file')) {
            $file      = $request->file('file');
            $file_name = Str::uuid() . '.' . $file->extension();
            $data['file'] = $file_name;
            $data['file_name'] = $file->getClientOriginalName();
            $request->file->storeAs('public/attachments/compliance', $file_name);

            $data['assignee_user_id'] = -1; //All HR can pick this
        }else{
            $data['assignee_user_id'] =  $data['user_id']; //$this->userService->getRandomHrUserId();
        }

        EmployeeCompliance::create($data);

        return response()->json(array('message' => "Compliance Submitted."));
    }

    public function update(Request $request)
    {
        $data = $request->all();
        $data['file_status'] = $request->file_status ? $request->file_status : 'Pending review';

        if ($request->hasFile('file')) {
            $comp = EmployeeCompliance::where('id', $data['id'])->first();
            $path = storage_path() . '/app/public/attachments/compliance/' . $comp->file;
            if ($comp->file && File::exists($path)) {
                array_map('unlink', glob($path));
            }

            $file      = $request->file('file');
            $file_name = Str::uuid() . '.' . $file->extension();
            $data['file'] = $file_name;
            $data['file_name'] = $file->getClientOriginalName();
            $request->file->storeAs('public/attachments/compliance', $file_name);
            $data['assignee_user_id'] = -1; //All HR can pick this

        //}else{
            //TODO: check if reassinging user id is needed when updated
            //$data['assignee_user_id'] =  $data['user_id']; //$this->userService->getRandomHrUserId();
        }
        EmployeeCompliance::where('id', $data['id'])->update($data);

        return response()->json(array('message' => "Employee compliance successfully updated"));
    }

    public function employee_compliance(Request $request)
    {
        $limit = 10;
        // $limit = $request->get('itemsPerPage'); IMPLEMENT THIS
       $page = $request->page; //$request->input('page', 1);
        //$offset = ($page) * $limit;
        
        if ($page === 1) {
            $offset = 0;
        }
        if ($page > 1 ) {
            $offset = (($page - 1) * $limit) ;
        }


        //$query = EmployeeCompliance::query()->where('user_id', $request->id);
        $query = EmployeeCompliance::query()
            ->with(['user.employee', 'complianceModel', 'assignee'])
            ->where('is_active', 1)
            ->where('user_id', $request->id);

        $total = $query->count();

        $data = $query->offset($offset)->limit($limit)->get();

        return response()->json([
            'data' => $data,
            'total' => $total
        ]);
    }

    public function compliance(Request $request)
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

        //$query = EmployeeCompliance::query()->whereNotIn('file_status', ['Current', 'Required'])->with('user.employee', 'compliance')->where('is_active', 1);
        $query = EmployeeCompliance::
            with('user.employee', 'complianceModel', 'assignee')
            ->where('is_active', 1)->whereNotNull('compliance');

        if ($request->status) {
            $query->where('file_status', $request->status);
        }

        if ($request->type) {
            $query->where('compliance', $request->type);
        }

        if ($request->assignee) {
            $query->whereHas('assignee', function ($q) use ($request) {
                $q->where('username', $request->assignee);
            });
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
        EmployeeCompliance::where('id', $id)->update(['is_active' => 0]);
        return response()->json(array('message' => "Employee compliance successfully deleted"));
    }

    public function employeeCompliancePdf($file)
    {
        $filePath = storage_path('app/public/attachments/compliance/' . $file);
        if (!file_exists($filePath)) {
            abort(404, 'The PDF file does not exist.');
        }

        $mimeType = Storage::mimeType($filePath);
        return response()->download(
            $filePath,
            null,
            ['Content-Type' => $mimeType],
            'inline'
        );
    }

    public function approval(Request $request)
    {
        $status = $request->approval_status != 1 ? 'approved' : 'rejected';
        $fileStatus = match (true) {
            //$status === 'approved' && $request->is_exemptions == 1 => 'Exempt',
            $status === 'approved' => 'Current',
            default => 'Rejected',
        };

        EmployeeCompliance::where('id', $request->id)->update([
            'approval_status' => $status,
            'file_status' => $fileStatus,
            'review_comments' => $request->review_comments,
            'assignee_user_id' => $request->user_id,
        ]);
        return response()->json(array('message' => "Employee compliance status updated"));

        //TODO: This is the old version of the code before refactoring to more structured.
        //revert to this if status and assignee is failing
        /*
        if ($request->approval_status != 1) {
            EmployeeCompliance::where('id', $request->id)->update(['approval_status' => 'approved', 'file_status' => 'Current', 'review_comments' => $request->review_comments]);
        } else {
            EmployeeCompliance::where('id', $request->id)->update(['approval_status' => 'rejected', 'file_status' => 'Rejected', 'review_comments' => $request->review_comments]);
        }
        */
    }
}

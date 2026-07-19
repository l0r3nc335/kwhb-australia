<?php

namespace App\Http\Controllers;

use App\Models\EmployeeVaccination;
use App\Services\User\UserService;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use function Laravel\Prompts\alert;

class EmployeeVaccinationController extends Controller
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function create(Request $request)
    {
        $data = $request->all();
        $data['file_status'] = $request->file_status ? $request->file_status : 'Pending review';

            $data['expiry_date'] = ($request->expiry_date === 'null' || $request->expiry_date === null || $request->expiry_date === 'Invalid date' ) ?
                null : Carbon::createFromFormat('d-m-Y', $request->expiry_date)->format('Y-m-d');

        $data['file'] = null;
        if (is_null($request->expiry_date)) {
            //$data['expiry_date'] = Carbon::now()->addDays(30)->format('Y-m-d') ;
            $data['expiry_date'] = $request->get("expiry_date");
        }
        
        if ($request->hasFile('file')) {
            $file      = $request->file('file');
            $file_name = Str::uuid() . '.' . $file->extension();
            $data['file'] = $file_name;
            $data['file_name'] = $file->getClientOriginalName();
            $request->file->storeAs('public/attachments/vaccination', $file_name);

            $data['assignee_user_id'] = -1; //All HR can pick this

        }else{
            $data['assignee_user_id'] =  $data['user_id']; //$this->userService->getRandomHrUserId();
        }

        EmployeeVaccination::create($data);

        return response()->json(array('message' => "Vaccination Submitted."));
    }

    public function update(Request $request)
    {
        $data = $request->all();
        $data['file_status'] = $request->file_status ? $request->file_status : 'Pending review';

        if ($request->hasFile('file')) {
            $vacc = EmployeeVaccination::where('id', $data['id'])->first();
            $path = storage_path() . '/app/public/attachments/vaccination/' . $vacc->file;
            if ($vacc->file && File::exists($path)) {
                array_map('unlink', glob($path));
            }

            $file      = $request->file('file');
            $file_name = Str::uuid() . '.' . $file->extension();
            $data['file'] = $file_name;
            $data['file_name'] = $file->getClientOriginalName();
            $request->file->storeAs('public/attachments/vaccination', $file_name);
            $data['assignee_user_id'] = -1; //All HR can pick this

        //}else{
            //TODO: check if reassinging user id is needed when updated
            //$data['assignee_user_id'] =  $data['user_id']; //$this->userService->getRandomHrUserId();
        }

        EmployeeVaccination::where('id', $data['id'])->update($data);

        return response()->json(array('message' => "Employee vaccination successfully updated"));
    }

    public function employee_vaccination(Request $request)
    {
        $limit = 10;
        $page = $request->page; //$request->input('page', 1);
        //$offset = ($page) * $limit;
        
        if ($page === 1) {
            $offset = 0;
        }
        if ($page > 1 ) {
            $offset = (($page - 1) * $limit) ;
        }
        
        //$query = EmployeeVaccination::query()->where('user_id', $request->id);
        $query = EmployeeVaccination::query()
            ->with(['user.employee', 'vaccination', 'assignee'])
            ->where('is_active', 1)
            ->where('user_id', $request->id); // <-- added condition

        $total = $query->count();
        $data = $query->offset($offset)->limit($limit)->get();
        
        
        return response()->json([
            'data' => $data,
            'total' => $total
        ]);
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

        //$query = EmployeeVaccination::query()->with('user.employee', 'vaccination')->where('is_active', 1);
        $query = EmployeeVaccination::query()
            ->with(['user.employee', 'vaccination', 'assignee'])
            ->where('is_active', 1);
        
        if ($request->type) {
            $query->where('vaccination', $request->type);
        }

        if ($request->status) {
            $query->where('file_status', $request->status);
        }

        if($request->assignee) {
            $id = $this->userService->getUserIdByUsername($request->assignee);
            $query->where('assignee_user_id',  $id);
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
        EmployeeVaccination::where('id', $id)->update(['is_active' => 0]);
        return response()->json(array('message' => "Employee vaccination successfully deleted"));
    }

    public function employeeVaccinationPdf($file)
    {
        $filePath = storage_path('app/public/attachments/vaccination/' . $file);
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
            $status === 'approved' && $request->is_exemptions == 1 => 'Exempt',
            $status === 'approved' => 'Current',
            default => 'Rejected',
        };

        EmployeeVaccination::where('id', $request->id)->update([
            'approval_status' => $status,
            'file_status' => $fileStatus,
            'review_comments' => $request->reviewer_comments,
            'assignee_user_id' => $request->user_id,
        ]);

        return response()->json(array('message' => "Employee vaccination status updated"));

        //TODO: This is the old version of the code before refactoring to more structured.
        //revert to this if status and assignee is failing
        /*
        if ($request->approval_status != 1) {

            if($request->is_exemptions == 1){
                EmployeeVaccination::where('id', $request->id)->update([
                    'approval_status' => 'approved',
                    'file_status' => 'Exempt',
                    'review_comments' => $request->reviewer_comments,
                    'assignee_user_id' => $request->user_id
                ]);
            }else{
                EmployeeVaccination::where('id', $request->id)->update([
                    'approval_status' => 'approved',
                    'file_status' => 'Current',
                    'review_comments' => $request->reviewer_comments,
                    'assignee_user_id' => $request->user_id
                ]);
            }

        } else {
            EmployeeVaccination::where('id', $request->id)->update([
                'approval_status' => 'rejected',
                'file_status' => 'Rejected',
                'review_comments' => $request->reviewer_comments,
                'assignee_user_id' => $request->user_id
            ]);
        }
        */
    }
}

<?php

namespace App\Core\v1\Contract\Http\Controllers;

use App\Core\v1\Contract\Http\Requests\StoreRequest;
use App\Core\v1\Contract\Http\Requests\UpdateRequest;
use App\Core\v1\Contract\Models\Contract;
use App\Core\v1\Contract\Models\ContractVersionHistory;
use App\Core\v1\Contract\Repositories\ContractRepository;
use App\Core\v1\Employee\Models\Employee;
use App\Core\v1\Employment\Models\Employment;
use App\Core\v1\Location\Models\Location;
use App\Core\v1\Permission\Models\Permission;
use App\Core\v1\Template\Models\Template;
use App\Core\v1\User\Models\User;
use App\Http\Controllers\AppController;
use App\Http\Controllers\Controller;
use App\Repositories\ModelRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Str;

class ContractController extends AppController
{
    public function __construct(protected ModelRepository $repository = new ContractRepository())
    {
        parent::__construct();
    }

    public function createContract(Request $request)
    {
        $data = $request->all();
        $contract = Contract::create($data)->load(['created_by']);

        $typeLabel = $contract->type === 'letter_of_offer' ? 'Letter of Offer' : 'Contract';
        $creatorUserId = $contract->created_by->id;
        $creatorName = $contract->created_by->name;

        $fill = [
            'contract_id' => $contract->id,
            'action' => 'created',
            'activity' => "The {$typeLabel} was create by {$creatorName}",
            'status' => 'Created',
            'user_id' => $creatorUserId
        ];

        ContractVersionHistory::create($fill);

        return response()->json(array('message' => "Contract successfully saved."));
    }

    function getContract(Request $request) {
        $data = Contract::with('owner_employee', 'created_by', 'assignee_user', 'location', 'employment_type')
            ->where('id', $request->get('id'))
            ->first();
        return response()->json($data);
    }

    function getContractHistory(Request $request) {

        $contractId = $request->get('contract_id');

        $data = ContractVersionHistory::with('user_logs')
            ->where('contract_id', $contractId)
            ->get();

        return response()->json($data);
    }

    function getContractOfOffer(Request $request): \Illuminate\Http\JsonResponse
    {
        $data = Contract::with('owner_employee') //, 'created_by', 'assignee_user', 'location', 'employment_type'
            ->where('id', $request->get('id'))
            ->where('type', 'contract')
            ->first();
        return response()->json($data);
    }

    public function index(Request $request)
    {
        $this->authService->allowed(
            Permission::FEATURE_PEOPLE,
            Permission::SUB_FEATURES[Permission::FEATURE_PEOPLE]['leave_management_leave_request'],
            Permission::ACTION_VIEW
        );

        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 5);
        $role = $request->get('role');
        $expiry = $request->get('expiry');
        $location = $request->get('location');
        $status = $request->get('status');
        $userId = $request->get('user_id');
        $showAll = $request->boolean('show_all');
        $dateStart = $request->get('date_start');
        $dateEnd = $request->get('date_end');
        $type = $request->get('type');

        $this->checkExpiries();

        $query =  $this->repository
            ->useAuthService($this->authService)
            ->fetchFormattedContracts();

        if ($userId) {
            $query = $query->customWhere('assignee.id', $userId);
        }

        if (!empty($type)) {
            $query = $query->filter([
                'contracts.type' => $type,
            ], true, true);
        }

        if (!empty($location)) {
            $query = $query->filter([
                'contracts.location_id' => $location,
            ], true, true);
        }

        if (!empty($expiry)) {
            $query = $query->filter([
                'contracts.expiry_date' => $expiry,
            ], true, true);
        }

        if (!empty($status)) {

            if($status === 'In-Progress') {
                $query = $query->filter([
                    'contracts.status' => ['pending_0', 'pending_50']
                ], true, true);
            }else{
                $query = $query->filter([
                    'contracts.status' => lcfirst(str_replace(' ', '_', $status)),
                ], true, true);
            }
        }

        if (!empty($role)) {
            $query = $query->filter([
                'employees.job_title' => $role,
            ], true,true);
        }

        if (!empty($dateStart) && !empty($dateEnd)) {
            $query = $query->filters([
                'contracts.created_at' => [$dateStart, $dateEnd]
            ], true, true);
        }

        $total = $query->query->count();

        if ($showAll) {
            $contracts = $query->query->orderBy('created_at', 'desc')->get();
        } else {
            $contracts = $query->query->orderBy('created_at', 'desc')->paginate($perPage, ['*'], 'page', $page);
        }

        $contracts->transform(function ($contract) {
            $contract->created_at = Carbon::parse($contract->created_at)->format('m/d/Y');
            return $contract;
        });

        return response()->json(
            [
                'total' => $total,
                'contracts' => $contracts
            ]
        );
    }

    private function checkExpiries()
    {
        Contract::where('status', 'pending_0')
            ->whereNull('employee_signature_date')
            ->where('created_at', '<=', now()->subDays(14))
            ->update(['status' => 'expired']);
    }

    function getContractJobTitle()
    {
        $data = Employee::whereIn(Employee::ID, function($query){
            $query->select('owner_employee_id')
                ->from(with(new Contract)
                    ->getTable())
                ->whereNull('deleted_at'); ;
        })
            ->select('job_title')
            ->groupBy('job_title')
            ->orderBy('job_title', 'asc')
            ->pluck('job_title');

        return response()->json($data);
    }

    public function getcontractVersionHistory()
    {
        $contractteVersionHistory = ContractVersionHistory::all();

        return $contractteVersionHistory;
    }

    public function create($templateId) 
    {

        $data['employees'] = Employee::all(); // owner_employee_id , assigned_employee_id , created_by_employee_id
        $data['template'] = Template::where('id', $templateId)->first(); // Template content
        $data['location'] = Location::where('is_active', '1')->get(); //location_id
        $data['employment'] = Employment::where('is_active', '1')->get(); // employment_id

        return response()->json($data);
    }

    public function store(StoreRequest $request)
    {
        $title = $request->get('title');
        $content = $request->get('content');
        $ownerEmployeeId = $request->get('owner_employee_id');
        $status = $request->get('status');
        $expiryDate = $request->get('expiry_date');
        $locationId = $request->get('location_id');
        $employmentTypeId = $request->get('employment_type_id');
        $employeeSignature = $request->get('employee_signature');
        $templateFieldData = $request->get('customisationData');
    
        $userId = auth()->user()->id;
        $employee = Employee::where('user_id', $userId)->firstOrFail();
    
        $action = 'created';
        $user = User::where('id', $userId)->firstOrFail();
        $activity = $user->name . ' ' . $action . ' this contract';
    
        $contract = $this->repository->store(
            $title,
            $content,
            $ownerEmployeeId,
            $employee->id,
            $status,
            $expiryDate,
            $locationId,
            $employmentTypeId,
            $employeeSignature
        );
    
        if ($templateFieldData && is_array($templateFieldData)) 
        {
            foreach ($templateFieldData as $fieldKey => $data) 
            {
                $fieldId = $data['id'];
                $fieldValue = $data['value'];
    
                $this->repository->storeContractDetails(
                    $contract->id,
                    $fieldId, 
                    $fieldValue
                );
            }
        }
    
        $this->repository->storeContractVaccinations($contract->id);

        $this->repository->storeContractCertifications($contract->id);

        $this->repository->storeContractAllowances($contract->id);
    
        return response()->json([
            'contractID' => $contract->id,
        ]);
    }
    

    public function edit($id) 
    {

        $data['contract'] =  $this->repository->getContractById($id);
        
        return response()->json($data);
    }

    public function updateContract(Request $request)
    {

        $contract = Contract::findOrFail($request->get('id'));

        $contract->assignee_user_id = $request->filled('assignee_user_id') ? $request->input('assignee_user_id') : null;
        $contract->status = $request->input('status');

        if($request->input('content') !== null){
            $contract->content = $request->input('content');
        }

        if($request->input('title') !== null){
            $contract->title = $request->input('title');
        }

        $contract->signature_page = $request->filled('signature_page') ? $request->input('signature_page') : null;
        $contract->employee_signature = $request->filled('employee_signature') ? $request->input('employee_signature') : null;
        $contract->employee_signature_date = $request->filled('employee_signature_date') ? $request->input('employee_signature_date') : null;

        $contract->save(); // update the DB

        /* ===== HISTORY ===== */
        $typeLabel = $request->input('type') === 'letter_of_offer' ? 'Letter of Offer' : 'Contract';
        $updatedByUserId = $request->input('updated_by_id');
        $updatedbyName = $request->input('updated_by_name');
        $contractId = $request->get('id');
        $action = $request->filled('action') ? $request->input('action') : 'updated';
        $activity = "The {$typeLabel} was {$action} by {$updatedbyName}";
        $status = $request->input('history_status') ?? 'Updated';

        $fill = [
            'contract_id' => $contractId,
            'action' =>  $action,
            'activity' => $activity,
            'status' => $status,
            'user_id' => $updatedByUserId
        ];

        ContractVersionHistory::create($fill);

        return response()->json([
            'message' => 'Contract updated successfully.',
            'contract' => $contract
        ]);
    }

    public function rejectContract(Request $request)
    {

        $contract = Contract::findOrFail($request->get('id'));
        $contract->status = $request->input('status');
        $contract->ceo_reject_message = $request->input('ceo_reject_message');
        $contract->assignee_user_id = $contract->created_by_user_id;

        $contract->save(); // update the DB

        $type = $request->input('type_label');

        $contractId = $request->get('id');
        $action = $request->filled('action') ? $request->input('action') : 'rejected';
        $typeLabel = $request->input('type') === 'letter_of_offer' ? 'Letter of Offer' : 'Contract';
        $updatedByUserId = $request->input('updated_by_id');
        $updatedbyName = $request->input('updated_by_name');
        $activity = "The {$typeLabel} was {$action} by {$updatedbyName}";
        $status = $request->input('history_status') ?? 'Rejected';

        $fill = [
            'contract_id' => $contractId,
            'action' =>  $action,
            'activity' => $activity,
            'status' => $status,
            'user_id' => $updatedByUserId
        ];

        ContractVersionHistory::create($fill);

        return response()->json([
            'message' => $type . ' rejected successfully.',
            'contract' => $contract
        ]);
    }

    public function updateContractStatus(Request $request)
    {
        $contract = Contract::findOrFail($request->get('id'));
        $contract->status = $request->input('status');
        $contract->assignee_user_id = $request->input('user_id');

        $contract->save(); // update the DB
        $type = $request->input('typeLabel');

        $contractId = $request->get('id');
        $action = $request->input('status') === 'archive' ? 'archived' : 'updated'; //make a migration
        $typeLabel =  $type === 'Letter of Offer' ? 'Letter of Offer' : 'Contract';
        $updatedByUserId = $request->input('user_id');
        $updatedbyName = $request->input('user_name');
        $activity = "The {$typeLabel} was {$action} by {$updatedbyName}";
        $status = $action === 'archived' ? 'Archived' : 'Updated';

        $fill = [
            'contract_id' => $contractId,
            'action' =>  $action,
            'activity' => $activity,
            'status' => $status,
            'user_id' => $updatedByUserId
        ];

        ContractVersionHistory::create($fill);

        return response()->json([
            'message' => $type . ' archived successfully.',
            'contract' => $contract
        ]);
    }
    public function duplicateContract(Request $request)
    {
        $post = Contract::find($request->get('id'));

        $label = $post->type == 'letter_of_offer' ? 'Letter of offer' : 'Contract';

        if ($post) {
            $newPost = $post->replicate();
            $newPost->status = 'draft';
            $newPost->save();

            return response()->json([
                'message' => "{$label} successfully duplicated.",
            ]);
        }
    }

    public function updateContractOld(UpdateRequest $request, $id)
    {
        $contract = $this->repository->getContractById($id);
        $contractId = $contract->id;

        $title = $request->get('title');
        $content = $request->get('content');
        $ownerEmployeeId = $request->get('owner_employee_id');
        $assignedEmployeeId = $request->get('assigned_employee_id');
        $positionTitle = $request->get('position_title');
        $status = $request->get('status');
        $expiryDate = $request->get('expiry_date');
        $stage = $request->get('stage');
        $locationId = $request->get('location_id'); 
        $employmentId = $request->get('employment_id');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $salary = $request->get('salary');
        $employeeSignature = $request->get('employee_signature');
        $contractorSignature = $request->get('contractor_signature');

        $userId = auth()->user()->id;

        $user = User::where('id', $userId)->firstOrFail();

        $action = 'updated';

        $activity = $user->name . ' ' . $action . ' this contract';

        $contract = $this->repository->updateContract(
            $contractId,
            $title,
            $content,
            $ownerEmployeeId,
            $assignedEmployeeId,
            $positionTitle,
            $status,
            $expiryDate,
            $stage,
            $locationId,
            $employmentId,
            $startDate,
            $endDate,
            $salary,
            $employeeSignature,
            $contractorSignature
        );

        $this->repository->storeContractVersionHistory(
            $contract->id,
            $action,
            $activity,
            $contract->status,
            $userId,
        );

        return response()->json([
            'contractID' => $contract->id,
        ]);
    }

    public function deleteContract(string $id)
    {        
        $userId = auth()->user()->id;

        $user = User::where('id', $userId)->firstOrFail();

        $action = 'deleted';
        
        $activity = $user->name . ' ' . $action . ' this contract';

        $contract = $this->repository->getContractById($id, ['id']);

        $this->repository->deleteContractById($id);

        $this->repository->storeContractVersionHistory(
            $contract->id,
            $action,
            $activity,
            $contract->status,
            $userId
        );
        
        return response()->json(['deleted'], 201);
    }

    public function saveSignature(Request $request)
    {
        $dataURL = $request->input('signature');

        if (!$dataURL) 
        {
            return response()->json(['error' => 'No signature provided'], 400);
        }

        $data = explode(',', $dataURL);
        if (count($data) === 2) 
        {
            $fileData = base64_decode($data[1]);

            $fileName = 'signature_' . time() . '.png';
            $filePath = public_path('signatures/' . $fileName);

            if (!file_exists(public_path('signatures'))) 
            {
                mkdir(public_path('signatures'), 0777, true);
            }

            file_put_contents($filePath, $fileData);

            return response()->json(['success' => true, 'filename' => $fileName]);
        }

        return response()->json(['error' => 'Invalid signature data'], 400);
    }

    public function expiries()
    {
        $contracts = $this->repository->getContractExpiries();
        return response()->json($contracts->toArray());
    }

    public function getStatuses()
    {
        $statuses = [];

        foreach(Contract::statuses() as $id => $label) {
            $t = new \stdClass();
            $t->id = $id;
            $t->name = $label;
            $statuses[] = $t;
        }

        return response()->json($statuses);
    }
}

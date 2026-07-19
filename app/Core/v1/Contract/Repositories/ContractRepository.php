<?php

namespace App\Core\v1\Contract\Repositories;

use App\Core\v1\Contract\Models\Contract;
use App\Core\v1\Contract\Models\ContractAllowance;
use App\Core\v1\Contract\Models\ContractCertification;
use App\Core\v1\Contract\Models\ContractDetails;
use App\Core\v1\Contract\Models\ContractVaccination;
use App\Core\v1\Contract\Models\ContractVersionHistory;
use App\Repositories\ModelRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ContractRepository extends ModelRepository
{
    public function model(): Model
    {
        return new Contract;
    }

    public function getContracts()
    {
        $contracts = Contract::whereNull('deleted_at')
                    ->get();

        return $contracts;
    }

    public function getContractById ($contractId)
    {
        return Contract::where('id', $contractId)
                    ->whereNull('deleted_at')
                    ->firstOrFail();
    }

    public function store(
        string $title,
        string $content,
        int $ownerEmployeeId  = null,
        int $createdByEmployeeId  = null,
        string $status,
        string $expiryDate = null,
        int $locationId  = null,
        int $employmentTypeId  = null,
        string $employeeSignature = null,
    ): Contract
    {
        return Contract::create([
            'title' => $title,
            'content' => $content,
            'owner_employee_id' => $ownerEmployeeId,
            'created_by_employee_id' => $createdByEmployeeId,
            'status' => $status,
            'expiry_date' => $expiryDate,
            'location_id' => $locationId,
            'employment_type_id' => $employmentTypeId,
            'employee_signature' => $employeeSignature,
        ]);
    } 

    public function storeContractVersionHistory(
        int $contractId,
        string $action,
        string $activity,
        string $status,
        int $user_id,
    ): ContractVersionHistory
    {
        return ContractVersionHistory::create([
            'contract_id' => $contractId,
            'action' => $action,
            'activity' => $activity,
            'status' => $status,
            'user_id' => $user_id,
            'created_at' => now(),
        ]);
    }

    public function storeContractDetails(
        int $contractId,
        int $templateFieldId,
        string $value,
    ): ContractDetails
    {
        return ContractDetails::create([
            'contract_id' => $contractId,
            'template_field_id' => $templateFieldId,
            'value' => $value,
        ]);
    }

    public function storeContractVaccinations(
        int $contractId
    ): ContractVaccination
    {
        return ContractVaccination::create([
            'contract_id' => $contractId
        ]);
    }

    public function storeContractCertifications(
        int $contractId
    ): ContractCertification
    {
        return ContractCertification::create([
            'contract_id' => $contractId
        ]);
    }

    public function storeContractAllowances(
        int $contractId
    ): ContractAllowance
    {
        return ContractAllowance::create([
            'contract_id' => $contractId
        ]);
    }

    public function updateContract(
        $contractId,
        string $title = null,
        string $content = null,
        int $ownerEmployeeId = null,
        int $assignedEmployeeId = null,
        string $positionTitle = null,
        string $status = null,
        string $expiryDate = null,
        string $stage = null,
        int $locationId = null,
        int $employmentId = null,
        string $startDate = null,
        string $endDate = null,
        string $salary = null,
        string $employeeSignature = null,
        string $contractorSignature = null
    ): Contract {
        $contract = Contract::findOrFail($contractId);

        $data = [];
    
        if ($title !== null) 
        {
            $data['title'] = $title;
        }

        if ($content !== null) 
        {
            $data['content'] = $content;
        }

        if ($ownerEmployeeId !== null) {
            $data['owner_employee_id'] = $ownerEmployeeId;
        }

        if ($assignedEmployeeId !== null) 
        {
            $data['assigned_employee_id'] = $assignedEmployeeId;
        }

        if ($positionTitle !== null) 
        {
            $data['position_title'] = $positionTitle;
        }

        if ($status !== null) 
        {
            $data['status'] = $status;
        }

        if ($expiryDate !== null) 
        {
            $data['expiry_date'] = $expiryDate;
        }

        if ($stage !== null) 
        {
            $data['stage'] = $stage;
        }

        if ($locationId !== null) 
        {
            $data['location_id'] = $locationId;
        }

        if ($employmentId !== null) 
        {
            $data['employment_id'] = $employmentId;
        }

        if ($startDate !== null) 
        {
            $data['start_date'] = $startDate;
        }

        if ($endDate !== null) 
        {
            $data['end_date'] = $endDate;
        }

        if ($salary !== null) 
        {
            $data['salary'] = $salary;
        }

        if ($employeeSignature !== null) 
        {
            $data['employee_signature'] = $employeeSignature;
        }

        if ($contractorSignature !== null) 
        {
            $data['contractor_signature'] = $contractorSignature;
        }
    
        $contract->update($data);
    
        return $contract;
    }

    public function deleteContractById ($contractId)
    {
        return Contract::where('id', $contractId)
                    ->update([
                            'deleted_at' => now()
                    ]);
    }

    public function getContractExpiries()
    {
        $contracts = Contract::whereNull('deleted_at')
            ->whereNotNull('expiry_date')
            ->selectRaw("DISTINCT DATE_FORMAT(expiry_date, '%d-%m-%Y') as formatted_expiry_date")
            ->orderByRaw("DATE_FORMAT(expiry_date, '%d-%m-%Y') ASC")
            ->get();

        return $contracts;
    }

    public function fetchFormattedContracts(array $status = [])
    {
        $this->query = $this->model()
            ->newQuery()
            ->select([
                DB::raw('contracts.id as contract_id'),
                DB::raw('contracts.title as contract_title'),
                DB::raw('contracts.content as contract_content'),
                DB::raw('contracts.status as stat'),
                DB::raw('contracts.expiry_date as contract_expiry_date'),
                DB::raw('locations.name as location_name'),
                DB::raw('employees.first_name as employee_first_name'),
                DB::raw('employees.last_name as employee_last_name'),
                DB::raw('CONCAT(manager.first_name, " ", manager.last_name) as manager_full_name'),
                DB::raw('assignee.username as assignee_username'),
                DB::raw('employees.job_title as employee_job_title'),
                DB::raw('contracts.created_at as created_at'),
                DB::raw('contracts.updated_at as updated_at')
            ])
            ->leftJoin('locations', 'contracts.location_id', '=', 'locations.id')
            ->leftJoin('users', 'contracts.owner_user_id', '=', 'users.id')
            ->leftJoin('employees', 'users.id', '=', 'employees.user_id')
            ->leftJoin('employees as manager', 'manager.id', '=', 'employees.manager_id')
            ->leftJoin('users as assignee', 'contracts.assignee_user_id', '=', 'assignee.id');

        //if (count($status) > 0) {
            //$this->query = $this->query->whereIn('contracts.status', $status);
        //}

        //$this->query = $this->authService->scopedQuery($this->query);
        return $this;
    }

    public function customWhere(string $field, string $value)
    {
        $this->query = $this->query->where($field, $value);
        return $this;
    }
}

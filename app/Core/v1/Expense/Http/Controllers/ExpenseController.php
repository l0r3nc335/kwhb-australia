<?php

namespace App\Core\v1\Expense\Http\Controllers;

use App\Core\v1\Expense\Repositories\ExpenseRepository;
use App\Http\Controllers\AppController;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Core\v1\Expense\Models\Expense;


use App\Services\Myob\MyobApiService;


class ExpenseController extends AppController
{
    public function __construct(protected ExpenseRepository $repository = new ExpenseRepository)
    {
        parent::__construct();
    }

    public function get_codes(Request $request)
    {
        $myobApi = new MyobApiService;
        $getCostCodes = $myobApi->getCostCodes()->call();     
        
    
        return response()->json([
            'codes' => $getCostCodes
        ]);        
    }

public function index(Request $request)
{
    $perPage  = $request->get('per_page', 10);
    $perPage  = strtolower($perPage) === 'all' ? null : (int) $perPage;
    $page     = $request->get('page', 1);
    $search   = $request->get('search');
    $division = $request->get('division');
    $costCode = $request->get('costCode');
    $status   = $request->get('status');
    $date     = $request->get('date');

    $sortBy   = $request->get('sort_by', 'expense_date');
    $sortDesc = $request->boolean('sort_desc', false);
    $sortable = ['expense_date', 'amount', 'status', 'cost_code', 'user'];

    $authUser = auth()->user()->load(['employee', 'user_role']);
    $userId   = $authUser->id;
    $employee = $authUser->employee;
    $userRole = $authUser->role;

    if (! $employee || ! $userRole) {
        return response()->json([
            'error'        => 'No employee or user_role record found for user.',
            'data'         => [],
            'total'        => 0,
            'current_page' => $page,
            'per_page'     => $perPage,
            'total_pages'  => 0,
            'codes'        => [],
        ]);
    }

    $roleId     = (int) $userRole->id;
    $divisionId = $employee->division_id;

    // Determine if we need to join employees for search/division filter
    $needsEmployeeJoin = ! empty($search) || ! empty($division);

    $query = \App\Core\v1\Expense\Models\Expense::query();

    if ($needsEmployeeJoin) {
        $query->join('employees', 'expenses.user_id', '=', 'employees.user_id')
              ->select('expenses.*');
    }

    // Eager‐load for JSON output
    $query->with(['employee', 'user', 'reviewer']);

    // --- SORTING ---
    if ($sortBy === 'user') {
        // join the users table so we can order by users.name
        $query->join('users', 'expenses.user_id', '=', 'users.id')
              ->select('expenses.*');
        $direction = $sortDesc ? 'desc' : 'asc';
        $query->orderBy('users.name', $direction);
    }
    elseif (in_array($sortBy, $sortable)) {
        $query->orderBy("expenses.$sortBy", $sortDesc ? 'desc' : 'asc');
    }
    else {
        $query->orderBy('expenses.expense_date', 'desc');
    }

    // --- ROLE-BASED VISIBILITY ---
    switch ($roleId) {
        case 2: // USER
            $query->where('expenses.user_id', $userId);
            break;
        case 3: // LINE MANAGER
        case 5: // HR
            if ($divisionId) {
                $query->where('employees.division_id', $divisionId);
            } else {
                $query->whereRaw('0=1');
            }
            break;
        case 1: // CEO
        case 4: // GENERAL MANAGER
        case 6: // FINANCE
        case 10: // ADMINISTRATOR
            // full access
            break;
        case 8: // BOARD
            $query->where('expenses.user_id', $userId);
            break;
        default: // APPLICANT & others
            return response()->json([
                'data'         => [],
                'total'        => 0,
                'current_page' => $page,
                'per_page'     => $perPage,
                'total_pages'  => 0,
                'codes'        => [],
            ]);
    }

    // --- SEARCH & FILTER ---
    if (! empty($search)) {
        $query->where(function($q) use ($search) {
            $q->where('employees.first_name', 'LIKE', "%{$search}%")
              ->orWhere('employees.last_name',  'LIKE', "%{$search}%");
        });
    }

    if (! empty($date)) {
        try {
            $query->whereDate(
                'expenses.expense_date',
                \Carbon\Carbon::parse($date, 'Australia/Sydney')->format('Y-m-d')
            );
        } catch (\Exception $e) {
            // ignore invalid date
        }
    }

    if (! empty($division)) {
        $query->where('employees.division_id', $division);
    }

    if (! empty($costCode)) {
        $query->where('expenses.cost_code', $costCode);
    }

    if (! empty($status)) {
        $query->where('expenses.status', $status);
    }

    // --- PAGINATION & RESPONSE ---
    $paginatedData = $perPage
        ? $query->paginate($perPage, ['*'], 'page', $page)
        : new \Illuminate\Pagination\LengthAwarePaginator(
            $query->get(),
            $query->count(),
            $query->count(),
            $page
        );

    return response()->json([
        'data'         => $paginatedData->items(),
        'total'        => $paginatedData->total(),
        'current_page' => $paginatedData->currentPage(),
        'per_page'     => $paginatedData->perPage(),
        'total_pages'  => $paginatedData->lastPage(),
        'codes'        => [],
        'sort_by'      => $sortBy,
        'sort_desc'    => $sortDesc,
        'auth'         => $userId,
        'role_id'      => $roleId,
        'user_role'    => $userRole->id,
    ]);
}

    

    public function store(Request $request)
    {
        $expenseDate = $request->get('date');
        $reason = $request->get('reason');
        $costCode = $request->get('cost_code');
        $amount = $request->get('amount');
        $user_id = $request->get('user_id');
    
        // Handle file upload if present
        $filePath = null;
        if ($request->hasFile('receipt')) {
            $file = $request->file('receipt');
            $filePath = $file->store('receipts', 'public');
        }
    
        $this->repository->store($expenseDate, $reason, $costCode, $amount, $user_id, $filePath);
    
        return response()->json([
            'message' => 'Expense submitted for approval'
        ]);
    }
    

    public function update_expense(Request $request, $id)
    {
        $expenseDate = $request->get('expense_date');
        $reason = $request->get('reason');
        $costCode = $request->get('cost_code');
        $amount = $request->get('amount');
    
        $expense = $this->repository->update_expense($id, $expenseDate, $reason, $costCode, $amount);
    
        if (!$expense) {
            return response()->json([
                'message' => 'Expense not found or failed to update'
            ], 404);
        }
    
        return response()->json([
            'message' => 'Expense updated successfully',
            'data' => $expense
        ]);
    }    


    
    public function update(Request $request, $id)
    {
        $status = $request->get('status');
        $comment = $request->get('reviewer_comment'); // optional
    
        $this->repository->updateStatus($id, $status, $comment);
    
        return response()->json([
            'message' => 'Expense status updated successfully'
        ]);
    }
}

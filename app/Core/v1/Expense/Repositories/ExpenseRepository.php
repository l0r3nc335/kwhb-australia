<?php

namespace App\Core\v1\Expense\Repositories;

use App\Core\v1\Expense\Models\Expense;
use App\Repositories\ModelRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ExpenseRepository extends ModelRepository
{
    public function model(): Model
    {
        return new Expense;
    }

    public function store(
        $date,
        $reason,
        $costCode,
        $amount,
        $user_id,
        $filePath = null
    ) {
        $expense = $this->model();
        $expense->user_id = $user_id ?? auth()->user()->id;
        $expense->expense_date = Carbon::parse($date)
            ->timezone('Australia/Sydney') // Ensure it’s in Sydney time
            ->format('Y-m-d');
        $expense->reason = $reason;
        $expense->cost_code = $costCode;
        $expense->amount = $amount;
        $expense->confirm_expense_is_work_related = true;
    
        if ($filePath) {
            $expense->receipt_image_path = $filePath;
        }
    
        $expense->save();
    }
    

    public function updateStatus($id, $status, $comment = null)
    {
        $expense = Expense::findOrFail($id);
    
        $expense->status = $status;
        $expense->reviewer_id = auth()->id();
    
        if (!is_null($comment)) {
            $expense->reviewer_comment = $comment;
        }
    
        $expense->save();
    }
    public function update(Request $request, $id)
    {
        $this->repository->markAsSubmitted($id);
    
        return response()->json([
            'message' => 'Expense submitted successfully'
        ]);
    }    

    public function update_expense(
        $id,
        $date,
        $reason,
        $costCode,
        $amount
    ) {
        $expense = $this->model()->find($id);
    
        if (!$expense) {
            return false;
        }
    
        $expense->expense_date = Carbon::parse($date)->format('Y-m-d');
        $expense->reason = $reason;
        $expense->cost_code = $costCode;
        $expense->amount = $amount;
        $expense->save();
    
        return $expense;
    }    
}

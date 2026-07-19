<?php

namespace App\Core\v1\Expense\Models;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    const STATUS_PENDING = 1;
    const STATUS_APPROVED = 2;
    const STATUS_CANCELLED = 3;
    const STATUS_REJECTED = 4;

    // Eager load these relationships
    protected $with = ['user', 'employee', 'reviewer'];

    protected $appends = ['status_text', 'submitted_by'];

    protected $fillable = [
        'user_id',
        'expense_date',
        'reason',
        'cost_code',
        'amount',
        'confirm_expense_is_work_related',
        'status',
        'reviewer_id',
        'reviewer_comment',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'user_id', 'user_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function getStatusTextAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING   => 'Pending',
            self::STATUS_APPROVED  => 'Approved',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_REJECTED  => 'Rejected',
            default                => 'Unknown',
        };
    }

    public function getSubmittedByAttribute(): ?array
    {
        if (!$this->relationLoaded('employee') || !$this->employee) {
            return null;
        }

        return [
            'user_id'     => $this->employee->user_id,
            'first_name'  => $this->employee->first_name,
            'last_name'   => $this->employee->last_name,
            'full_name'   => trim($this->employee->first_name . ' ' . $this->employee->last_name),
            'position_id' => $this->employee->position_id ?? null,
            'division_id' => $this->employee->division_id ?? null,
        ];
    }

    protected $casts = [
        'amount' => 'string', // matches the DB precision: DECIMAL(65, 30)
    ];    

    public function getAmountAttribute($value)
    {
        // If no decimal at all → add .00
        if (!str_contains($value, '.')) {
            return $value . '.00';
        }
    
        // Check how many decimal places
        $parts = explode('.', $value);
        $decimals = strlen($parts[1] ?? '');
    
        // If more than 2 decimal places → round to 2
        if ($decimals > 2) {
            return number_format((float) $value, 2, '.', '');
        }
    
        // If 1 or 2 decimals, return as-is (no padding)
        return rtrim(rtrim($value, '0'), '.') === (string)(int)$value
            ? number_format($value, 2, '.', '') // edge case: e.g. 100.00 as 100.00
            : $value;
    }
    
    
}

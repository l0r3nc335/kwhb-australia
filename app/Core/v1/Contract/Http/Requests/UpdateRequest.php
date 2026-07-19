<?php

namespace App\Core\v1\Contract\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'owner_employee_id' => 'nullable|exists:employees,id',
            'assigned_employee_id' => 'nullable|exists:employees,id',
            'position_title' => 'nullable|string|max:255',
            'status' => 'nullable|in:pending,expired,rejected,completed',
            'expiry_date' => 'nullable|date|after:start_date',
            'stage' => 'nullable|in:draft,completed',
            'location_id' => 'nullable|int',
            'location_id' => 'nullable|int',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'salary' => 'nullable|string',
            'employee_signature' => 'nullable|string',
            'contractor_signature' => 'nullable|string',
        ];
    }
}

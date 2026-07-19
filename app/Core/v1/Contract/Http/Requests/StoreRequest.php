<?php

namespace App\Core\v1\Contract\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'owner_employee_id' => 'nullable|exists:employees,id',
            'status' => 'required|in:pending_0,pending_50,expired,rejected,completed',
            'expiry_date' => 'nullable|date',
            'location_id' => 'required|int',
            'employment_type_id' => 'required|int',
            'employee_signature' => 'nullable|string',
        ];
    }
}

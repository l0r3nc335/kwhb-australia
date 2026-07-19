<?php

namespace App\Http\Requests;

use App\Rules\ComplianceExistName;
use Illuminate\Foundation\Http\FormRequest;

class StoreComplianceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', new ComplianceExistName],
            'validity' => ['required'],
            'division' => ['nullable'],
            'required' => ['required'],
            'role' => ['nullable'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Name is required',
            'validity.required' => 'Validity is required',
            'required.required' => 'Requirement is required',
        ];
    }
}

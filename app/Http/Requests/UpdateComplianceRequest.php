<?php

namespace App\Http\Requests;

use App\Rules\ComplianceExistNameUpdate;
use Illuminate\Foundation\Http\FormRequest;

class UpdateComplianceRequest extends FormRequest
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
            'id' => ['required'],
            'name' => ['required', new ComplianceExistNameUpdate],
            'validity' => ['required'],
            'custom_expiry' => ['nullable'],
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
            'id.required' => 'Data not found',
            'name.required' => 'Name is required',
            'validity.required' => 'Validity is required',
            'required.required' => 'Requirement is required',
        ];
    }
}

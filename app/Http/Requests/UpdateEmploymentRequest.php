<?php

namespace App\Http\Requests;

use App\Rules\EmploymentExistNameUpdate;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEmploymentRequest extends FormRequest
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
            'name' => ['required', new EmploymentExistNameUpdate],
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
        ];
    }
}

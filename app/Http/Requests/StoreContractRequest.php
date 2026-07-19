<?php

namespace App\Http\Requests;

use App\Rules\VaccinationExistName;
use Illuminate\Foundation\Http\FormRequest;

class StoreContractRequest extends FormRequest
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
            'title' => ['required', new VaccinationExistName],
            'content' => ['required'],
            'template' => ['nullable'],
            'custom_data' => ['required'],
            'owner_user_id' => ['nullable'],
            'created_by_user_id' => ['nullable'],
            'assignee_user_id' => ['nullable'],
            'status' => ['nullable'],
            'expiry_date' => ['nullable'],
        ];
    }
}

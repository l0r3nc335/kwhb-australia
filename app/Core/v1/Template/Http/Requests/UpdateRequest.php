<?php

namespace App\Core\v1\Template\Http\Requests;

use App\Core\v1\Timesheet\Models\Timesheet;
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
            'name' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'status' => 'nullable|in:draft,published,trash',
            'field_name' => 'array',
            'field_name.*' => 'string|max:255',
            'active' => 'array',
            'active.*' => 'boolean',
        ];
    }
}

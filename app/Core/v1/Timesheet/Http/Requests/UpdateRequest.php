<?php

namespace App\Core\v1\Timesheet\Http\Requests;

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
        $allowedCategories = [];
        foreach (Timesheet::categories() as $key => $val) {
            $allowedCategories[] = $key;
        }

        return [
            'category' => 'required|in:' . implode(',', $allowedCategories),
            'start' => 'nullable|date_format:H:i',
            'end' => 'nullable|date_format:H:i',
            'break' => 'nullable|numeric',
            'comment' => 'nullable'
        ];
    }
}

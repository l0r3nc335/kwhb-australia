<?php

namespace App\Core\v1\Leave\Http\Requests;

use App\Core\v1\Leave\Models\Leave;
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
        $leaveTypes = Leave::types();

        return [
            'user_id' => 'required|exists:users,id',
            'type' => 'required|in:' . implode(',', array_keys($leaveTypes)),
            'reason' => 'nullable|string'
        ];
    }
}

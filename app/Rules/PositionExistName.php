<?php

namespace App\Rules;

use App\Models\Position;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PositionExistName implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $model = Position::where('name', $value)->where('is_active', 1)->first();
        if ($model) {
            $fail('Job title name already exist');
        }
    }
}

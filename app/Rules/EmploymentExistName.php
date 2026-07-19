<?php

namespace App\Rules;

use App\Models\Employment;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class EmploymentExistName implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $model = Employment::where('name', $value)->where('is_active', 1)->first();
        if ($model) {
            $fail('Employment name already exist');
        }
    }
}

<?php

namespace App\Rules;

use App\Models\Compliance;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ComplianceExistName implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $model = Compliance::where('name', $value)->where('is_active', 1)->first();
        if ($model) {
            $fail('Compliance name already exist');
        }
    }
}

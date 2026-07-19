<?php

namespace App\Rules;

use App\Models\Division;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class DivisionExistName implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $model = Division::where('name', $value)->where('is_active', 1)->first();
        if ($model) {
            $fail('Division name already exist');
        }
    }
}

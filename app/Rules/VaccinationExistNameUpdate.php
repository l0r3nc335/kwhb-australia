<?php

namespace App\Rules;

use App\Models\Vaccination;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class VaccinationExistNameUpdate implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $model = Vaccination::where('name', $value)->where('is_active', 1)->first();
        if ($model && $model->id != request()->id) {
            $fail('Vaccination name already exist');
        }
    }
}

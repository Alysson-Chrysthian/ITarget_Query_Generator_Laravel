<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Cnpj implements ValidationRule
{
    private const cnpjRegex = '#^\d{2}\.\d{3}\.\d{3}\/\d{4}\-\d{2}$#';

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!preg_match(self::cnpjRegex, $value)) 
            $fail('validation.cnpj')->translate();
    }
}

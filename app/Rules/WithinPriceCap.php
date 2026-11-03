<?php

namespace App\Rules;

use App\Models\Ticket;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Resale price may not exceed face value + the configured markup.
 */
class WithinPriceCap implements ValidationRule
{
    public function __construct(private readonly float|int|string|null $faceValue) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $max = Ticket::maxPriceFor($this->faceValue);

        if ($max !== null && is_numeric($value) && (float) $value > $max) {
            $fail('The most you can charge is KES '.number_format($max).' ('.rtrim(rtrim(number_format(config('services.tixswap.max_markup_percent'), 1), '0'), '.').'% above face value).');
        }
    }
}

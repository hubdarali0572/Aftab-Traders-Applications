<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
class Recaptcha implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // 1. Send as a POST request (Recommended by Google)
        // 2. Use config() for reliability (optional but recommended)
        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret'   => env('GOOGLE_RECAPTCHA_SECRET'),
            'response' => $value,
            'remoteip' => request()->ip(), // Optional: adds extra security
        ])->json();

        // Check if the response failed
        if (!isset($response['success']) || !$response['success']) {
            $fail('The reCAPTCHA verification failed. Please try again.');
        }
    }
}
<?php

namespace App\Http\Requests\Shop;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Card details for the simulated gateway.
 *
 * Nothing here is stored or sent anywhere: the fields are validated for shape
 * so the form behaves like the real thing, then discarded.
 */
class PaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'card_number' => ['required', 'string', 'regex:/^\d{4} ?\d{4} ?\d{4} ?\d{4}$/'],
            'expiry_date' => ['required', 'string', 'regex:/^(0[1-9]|1[0-2])\/\d{2}$/'],
            'cvv' => ['required', 'string', 'digits:3'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'card_number.regex' => 'Please enter a 16 digit card number.',
            'expiry_date.regex' => 'Please enter the expiry date as MM/YY.',
            'cvv.digits' => 'The security code is 3 digits.',
        ];
    }
}

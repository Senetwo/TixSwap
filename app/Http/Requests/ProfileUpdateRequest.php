<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'payout_phone' => ['nullable', 'string', 'regex:'.\App\Services\Mpesa\PhoneNumber::PATTERN],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('payout_phone')) {
            $this->merge(['payout_phone' => \App\Services\Mpesa\PhoneNumber::clean($this->input('payout_phone')) ?: null]);
        }
    }

    public function messages(): array
    {
        return [
            'payout_phone.regex' => 'Enter a valid Safaricom number, e.g. 0712345678.',
        ];
    }
}

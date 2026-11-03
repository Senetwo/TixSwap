<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('ticket'));
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'price' => ['required', 'numeric', 'min:1', 'max:1000000', new \App\Rules\WithinPriceCap($this->route('ticket')->face_value)],
            'seat_details' => 'required|string|max:255',
            'original_vendor' => 'nullable|string|max:255',
        ];
    }
}

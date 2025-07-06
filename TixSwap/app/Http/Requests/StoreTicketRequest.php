<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Allows any authenticated user with the 'seller' or 'admin' role to proceed.
        return in_array($this->user()->role, ['seller', 'admin']);
    }

    /**
     * Get the validation rules that apply to the request.
     * This version includes all the necessary rules.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'event_name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'date' => 'required|date',
            'price' => 'required|numeric|min:0',
            'seat_details' => 'required|string|max:255',
            'original_vendor' => 'nullable|string|max:255',
            'event_image' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'purchase_proof' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ];
    }
}

<?php

namespace TufikHasan\PaisaPay\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_gateway' => ['required', 'string', 'in:stripe,paypal,bkash'],
            'type' => ['nullable', 'string', 'max:255'],
            'currency' => ['required', 'string', 'size:3'],
            'metadata' => ['nullable', 'array'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'amount.required' => 'Payment amount is required.',
            'amount.numeric' => 'Payment amount must be a number.',
            'amount.min' => 'Payment amount must be at least 0.01.',
            'currency.required' => 'Currency is required.',
            'payment_gateway.required' => 'Payment type is required.',
            'payment_gateway.in' => 'Payment type must be one of: stripe, paypal, bkash.',
            'type.required' => 'Transaction type is required.',
        ];
    }
}

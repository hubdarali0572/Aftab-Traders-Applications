<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $orderReturn = $this->route('order_return') ?? $this->route('orderReturn');
        $orderReturnId = is_object($orderReturn) ? $orderReturn->id : $orderReturn;

        return [
            'reference_no' => 'required|string|max:255|unique:order_returns,reference_no,' . $orderReturnId,
            'order_id' => 'required|exists:orders,id',
            'return_date' => 'required|date',
            'return_status' => 'required|in:draft,approved,cancelled',
            'remarks' => 'nullable|string',
            'status' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'order_id.required' => 'Please select an order to return against.',
            'reference_no.unique' => 'This return reference number is already in use.',
        ];
    }
}

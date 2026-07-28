<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $order = $this->route('order');
        $orderId = is_object($order) ? $order->id : $order;

        return [
            'order_no' => 'required|string|max:255|unique:orders,order_no,' . $orderId,
            'order_date' => 'required|date',
            'customer_id' => 'required|exists:customers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'order_type' => 'required|in:wholesale,retail',
            'subtotal' => 'required|numeric|min:0',
            'discount' => 'required|numeric|min:0',
            'tax' => 'required|numeric|min:0',
            'other_charges' => 'required|numeric|min:0',
            'grand_total' => 'required|numeric|min:0',
            'order_status' => 'required|in:pending,confirmed,processing,completed,cancelled',
            'remarks' => 'nullable|string',
            'status' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'order_no.unique' => 'This order number is already in use.',
            'customer_id.required' => 'Please select a customer.',
            'warehouse_id.required' => 'Please select a warehouse.',
        ];
    }
}

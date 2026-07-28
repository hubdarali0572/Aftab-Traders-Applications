<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrderDetailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $detail = $this->route('order_detail') ?? $this->route('orderDetail');
        $detailId = is_object($detail) ? $detail->id : $detail;

        return [
            'order_id' => 'required|exists:orders,id',
            'product_id' => [
                'required',
                'exists:products,id',
                Rule::unique('order_details')
                    ->where(fn ($q) => $q->where('order_id', $this->order_id)->whereNull('deleted_at'))
                    ->ignore($detailId),
            ],
            'unit_id' => 'required|exists:units,id',
            'quantity' => 'required|numeric|min:0.01',
            'unit_price' => 'required|numeric|min:0',
            'discount' => 'required|numeric|min:0',
            'tax' => 'required|numeric|min:0',
            'remarks' => 'nullable|string',
            'status' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.unique' => 'This product is already added on the selected order.',
            'quantity.min' => 'Quantity must be greater than zero.',
        ];
    }
}

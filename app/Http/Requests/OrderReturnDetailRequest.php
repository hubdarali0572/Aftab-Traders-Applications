<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrderReturnDetailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $detail = $this->route('order_return_detail') ?? $this->route('orderReturnDetail');
        $detailId = is_object($detail) ? $detail->id : $detail;

        return [
            'order_return_id' => 'required|exists:order_returns,id',
            'product_id' => [
                'required',
                'exists:products,id',
                Rule::unique('order_return_details')
                    ->where(fn ($q) => $q->where('order_return_id', $this->order_return_id)->whereNull('deleted_at'))
                    ->ignore($detailId),
            ],
            'unit_id' => 'required|exists:units,id',
            'quantity' => 'required|numeric|min:0.01',
            'reason' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
            'status' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.unique' => 'This product is already on the selected order return.',
            'quantity.min' => 'Return quantity must be greater than zero.',
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaleReturnDetailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $detail = $this->route('sale_return_detail') ?? $this->route('saleReturnDetail');
        $detailId = is_object($detail) ? $detail->id : $detail;

        return [
            'sale_return_id' => [
                'required',
                'exists:sale_returns,id',
                Rule::unique('sale_return_details')
                    ->where(fn ($q) => $q->where('product_id', $this->product_id))
                    ->ignore($detailId),
            ],
            'product_id' => 'required|exists:products,id',
            'unit_id' => 'required|exists:units,id',
            'quantity' => 'required|numeric|min:0.01',
            'reason' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaleReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $saleReturn = $this->route('sale_return') ?? $this->route('saleReturn');
        $saleReturnId = is_object($saleReturn) ? $saleReturn->id : $saleReturn;

        return [
            'reference_no' => 'required|string|unique:sale_returns,reference_no,' . $saleReturnId,
            'sale_id' => 'required|exists:sales,id',
            'return_date' => 'required|date',
            'remarks' => 'nullable|string',
            'status' => 'boolean',
        ];
    }
}

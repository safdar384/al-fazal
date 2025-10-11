<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'productCode' => [
                'nullable',
                'string',
                'max:50',
                'regex:/^[A-Za-z0-9\-]+$/',
                'unique:product_info,productCode'
            ],
            'productName' => [
                'required',
                'string',
                'max:100'
            ],
            'companyName' => [
                'required',
                'string',
                'max:150'
            ],
            'colourName' => [
                'required',
                'string',
                'max:100'
            ],
            'pakingName' => [
                'required',
                'string',
                'max:255'
            ],
            'saleRate' => [
                'required',
                'numeric',
                'min:0',
                'max:9999999.99'
            ],
            'costRate' => [
                'required',
                'numeric',
                'min:0',
                'max:9999999.99'
            ],
            'lessQty' => [
                'required',
                'numeric',
                'min:0'
            ],
            'stockQty' => [
                'required',
                'numeric'
            ],
            'catagoryName' => [
                'required',
                'string',
                'max:100'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'productCode.unique' => 'This product code is already taken.',
            'productCode.regex' => 'Product code must contain only letters, numbers and dashes.',
            'productName.required' => 'Product name is required.',
            'companyName.required' => 'Company name is required.',
            'colourName.required' => 'Colour is required.',
            'pakingName.required' => 'Packing type is required.',
            'saleRate.required' => 'Sale rate is required.',
            'saleRate.min' => 'Sale rate cannot be negative.',
            'costRate.required' => 'Cost rate is required.',
            'costRate.min' => 'Cost rate cannot be negative.',
            'lessQty.required' => 'Minimum quantity is required.',
            'stockQty.required' => 'Stock quantity is required.',
            'catagoryName.required' => 'Category is required.',
        ];
    }
}


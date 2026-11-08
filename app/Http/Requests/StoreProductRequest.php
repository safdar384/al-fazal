<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('productCode')) {
            $this->merge([
                'productCode' => strtoupper(preg_replace('/[^A-Za-z0-9\-]/', '', trim((string) $this->input('productCode')))),
            ]);
        }

        foreach (['productName', 'companyName', 'colourName', 'pakingName', 'unitPerPackName', 'catagoryName'] as $field) {
            if ($this->filled($field)) {
                $this->merge([
                    $field => ucwords(strtolower(trim((string) $this->input($field)))),
                ]);
            }
        }

        // Unit per Pack removed from Add Product UI — keep DB defaults.
        $this->merge([
            'unitPerPackName' => $this->filled('unitPerPackName')
                ? ucwords(strtolower(trim((string) $this->input('unitPerPackName'))))
                : 'Piece',
            'piecesPerUnit' => (int) ($this->input('piecesPerUnit') ?: 1),
        ]);

        if (is_array($this->input('variants'))) {
            $variants = [];
            foreach ($this->input('variants') as $key => $variant) {
                if (! is_array($variant)) {
                    $variants[$key] = $variant;
                    continue;
                }
                if (! empty($variant['name'])) {
                    $variant['name'] = ucwords(strtolower(trim((string) $variant['name'])));
                }
                if (! empty($variant['value'])) {
                    $variant['value'] = ucwords(strtolower(trim((string) $variant['value'])));
                }
                $variants[$key] = $variant;
            }
            $this->merge(['variants' => $variants]);
        }
    }

    public function rules(): array
    {
        return [
            'productCode' => [
                'nullable',
                'string',
                'max:50',
                'regex:/^[A-Z0-9\-]+$/',
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
            'unitPerPackName' => [
                'required',
                'string',
                'max:100'
            ],
            'piecesPerUnit' => [
                'required',
                'integer',
                'min:1',
                'max:999999'
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
            'image' => [
                'nullable',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:5120',
            ],
            'variants' => ['nullable', 'array'],
            'variants.*.name' => ['nullable', 'string', 'max:100'],
            'variants.*.value' => ['nullable', 'string', 'max:100'],
            'variants.*.additionalCost' => ['nullable', 'numeric', 'min:0'],
            'variants.*.additionalPrice' => ['nullable', 'numeric', 'min:0'],
            'variants.*.stockQty' => ['nullable', 'numeric', 'min:0'],
            'variants.*.image' => [
                'nullable',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:5120',
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
            'unitPerPackName.required' => 'Unit per pack is required.',
            'piecesPerUnit.required' => 'Pieces per unit is required.',
            'piecesPerUnit.min' => 'Pieces per unit must be at least 1.',
            'saleRate.required' => 'Sale rate is required.',
            'saleRate.min' => 'Sale rate cannot be negative.',
            'costRate.required' => 'Cost rate is required.',
            'costRate.min' => 'Cost rate cannot be negative.',
            'lessQty.required' => 'Minimum quantity is required.',
            'stockQty.required' => 'Stock quantity is required.',
            'catagoryName.required' => 'Category is required.',
            'image.image' => 'Product image must be a valid image file.',
            'image.mimes' => 'Product image must be JPG, JPEG, PNG, or WEBP.',
            'image.max' => 'Product image may not be greater than 5MB before optimization.',
            'variants.*.image.image' => 'Each variant image must be a valid image file.',
            'variants.*.image.mimes' => 'Variant images must be JPG, JPEG, PNG, or WEBP.',
            'variants.*.image.max' => 'Variant images may not be greater than 5MB before optimization.',
        ];
    }
}


<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->route('id');

        return [
            'productCode' => [
                'required',
                'string',
                'max:50',
                'regex:/^[A-Za-z0-9\-]+$/',
                Rule::unique('product_info', 'productCode')->ignore($productId)
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
            'remove_image' => ['nullable', 'boolean'],
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
            'existing_variants' => ['nullable', 'array'],
            'existing_variants.*.name' => ['nullable', 'string', 'max:100'],
            'existing_variants.*.value' => ['nullable', 'string', 'max:100'],
            'existing_variants.*.additionalCost' => ['nullable', 'numeric', 'min:0'],
            'existing_variants.*.additionalPrice' => ['nullable', 'numeric', 'min:0'],
            'existing_variants.*.stockQty' => ['nullable', 'numeric', 'min:0'],
            'existing_variants.*.remove_image' => ['nullable', 'boolean'],
            'existing_variants.*.image' => [
                'nullable',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:5120',
            ],
            'deleted_variants' => ['nullable', 'array'],
            'deleted_variants.*' => ['integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'productCode.required' => 'Product code is required.',
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
            'existing_variants.*.image.image' => 'Each variant image must be a valid image file.',
            'existing_variants.*.image.mimes' => 'Variant images must be JPG, JPEG, PNG, or WEBP.',
            'existing_variants.*.image.max' => 'Variant images may not be greater than 5MB before optimization.',
        ];
    }
}


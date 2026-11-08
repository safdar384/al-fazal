<?php

namespace App\Http\Requests;

use App\Models\CustomerCatagory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreCustomerCatagoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('catagoryName')) {
            $this->merge([
                'catagoryName' => trim((string) $this->input('catagoryName')),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'catagoryName' => [
                'required',
                'string',
                'max:100',
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $name = $this->input('catagoryName');
            if (! is_string($name) || $name === '') {
                return;
            }

            $exists = CustomerCatagory::query()
                ->whereRaw('LOWER(catagoryName) = ?', [strtolower($name)])
                ->exists();

            if ($exists) {
                $validator->errors()->add('catagoryName', 'This customer category already exists.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'catagoryName.required' => 'Category name is required.',
            'catagoryName.max' => 'Category name cannot exceed 100 characters.',
            'catagoryName.unique' => 'This customer category already exists.',
        ];
    }

    public function attributes(): array
    {
        return [
            'catagoryName' => 'category name',
        ];
    }
}

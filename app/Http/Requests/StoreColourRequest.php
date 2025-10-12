<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreColourRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'colourName' => ['required', 'string', 'max:255', 'unique:colour,colourName'],
        ];
    }

    public function messages(): array
    {
        return [
            'colourName.required' => 'Colour name is required.',
            'colourName.string' => 'Colour name must be a valid string.',
            'colourName.max' => 'Colour name cannot exceed 255 characters.',
            'colourName.unique' => 'This colour name already exists.',
        ];
    }

    public function attributes(): array
    {
        return [
            'colourName' => 'colour name',
        ];
    }
}

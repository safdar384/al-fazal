<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateColourRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $colourId = $this->route('id');
        
        return [
            'colourName' => [
                'required',
                'string',
                'max:255',
                Rule::unique('colour', 'colourName')->ignore($colourId),
            ],
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

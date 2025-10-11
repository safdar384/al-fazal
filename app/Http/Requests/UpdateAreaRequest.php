<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAreaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $areaId = $this->route('id');

        return [
            'areaCode' => [
                'required',
                'string',
                'max:50',
                'regex:/^[A-Za-z0-9]+$/',
                Rule::unique('customerareas', 'areaCode')->ignore($areaId)
            ],
            'areaName' => [
                'required',
                'string',
                'max:100'
            ],
            'ajentName' => [
                'nullable',
                'string',
                'max:100',
                'regex:/^[a-zA-Z\s\.]+$/'
            ],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'areaCode.required' => 'Area code is required.',
            'areaCode.unique' => 'This area code is already taken.',
            'areaCode.regex' => 'Area code must contain only letters and numbers.',
            'areaName.required' => 'Area name is required.',
            'ajentName.regex' => 'Agent name must contain only letters, spaces and periods.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'areaCode' => 'area code',
            'areaName' => 'area name',
            'ajentName' => 'agent name',
        ];
    }
}


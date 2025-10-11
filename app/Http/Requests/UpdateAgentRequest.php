<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAgentRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $agentId = $this->route('id');

        return [
            'ajentCode' => [
                'required',
                'string',
                'max:50',
                'regex:/^[A-Za-z0-9]+$/',
                Rule::unique('new_ajensts', 'ajentCode')->ignore($agentId)
            ],
            'ajentName' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z\s\.]+$/'
            ],
            'AjentAdvance' => [
                'nullable',
                'numeric',
                'min:0',
                'max:9999999.99'
            ],
            'ajentContact' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[0-9\-]+$/'
            ],
            'ajentAdderss' => [
                'nullable',
                'string',
                'max:255'
            ],
            'ajentCommission' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100'
            ],
            'ajentBalance' => [
                'nullable',
                'numeric',
                'min:0',
                'max:9999999.99'
            ],
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'ajentCode.required' => 'Agent code is required.',
            'ajentCode.unique' => 'This agent code is already taken.',
            'ajentCode.regex' => 'Agent code must contain only letters and numbers.',
            'ajentName.required' => 'Agent name is required.',
            'ajentName.regex' => 'Agent name must contain only letters, spaces and periods.',
            'AjentAdvance.numeric' => 'Agent advance must be a valid number.',
            'AjentAdvance.min' => 'Agent advance cannot be negative.',
            'AjentAdvance.max' => 'Agent advance is too large.',
            'ajentContact.regex' => 'Contact number must contain only numbers and dashes.',
            'ajentCommission.min' => 'Commission cannot be negative.',
            'ajentCommission.max' => 'Commission cannot exceed 100%.',
            'ajentBalance.numeric' => 'Balance must be a valid number.',
            'ajentBalance.min' => 'Balance cannot be negative.',
            'ajentBalance.max' => 'Balance is too large.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'ajentCode' => 'agent code',
            'ajentName' => 'agent name',
            'AjentAdvance' => 'agent advance',
            'ajentContact' => 'contact number',
            'ajentAdderss' => 'address',
            'ajentCommission' => 'commission',
            'ajentBalance' => 'balance',
        ];
    }
}


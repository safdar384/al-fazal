<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
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
        $customerId = $this->route('id');

        return [
            'custCode' => [
                'required',
                'string',
                'max:50',
                'regex:/^[A-Za-z0-9]+$/',
                Rule::unique('newcustomers', 'custCode')->ignore($customerId)
            ],
            'custArea' => [
                'required',
                'string',
                'max:100'
            ],
            'bussnessName' => [
                'required',
                'string',
                'max:150'
            ],
            'custName' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-zA-Z\s\.]+$/'
            ],
            'custAddress' => [
                'required',
                'string',
                'max:255'
            ],
            'custContect' => [
                'required',
                'string',
                'max:50',
                'regex:/^[0-9\-]+$/'
            ],
            'custCity' => [
                'required',
                'string',
                'max:100'
            ],
            'custPage' => [
                'required',
                'string',
                'max:100'
            ],
            'custdiscount' => [
                'required',
                'numeric',
                'min:0',
                'max:100'
            ],
            'custPayTermDay' => [
                'required',
                'integer',
                'min:0',
                'max:365'
            ],
            'custCreditLimit' => [
                'required',
                'numeric',
                'min:0',
                'max:99999999.99'
            ],
            'custOppDate' => [
                'required',
                'date'
            ],
            'custBalance' => [
                'nullable',
                'numeric'
            ],
            'custCatagory' => [
                'required',
                'string',
                'max:100'
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
            'custCode.required' => 'Customer code is required.',
            'custCode.unique' => 'This customer code is already taken.',
            'custCode.regex' => 'Customer code must contain only letters and numbers.',
            'custArea.required' => 'Customer area is required.',
            'bussnessName.required' => 'Business name is required.',
            'custName.required' => 'Customer name is required.',
            'custName.regex' => 'Customer name must contain only letters, spaces and periods.',
            'custAddress.required' => 'Address is required.',
            'custContect.required' => 'Contact number is required.',
            'custContect.regex' => 'Contact must contain only numbers and dashes.',
            'custCity.required' => 'City is required.',
            'custPage.required' => 'Page is required.',
            'custdiscount.required' => 'Discount is required.',
            'custdiscount.max' => 'Discount cannot exceed 100%.',
            'custPayTermDay.required' => 'Payment term days are required.',
            'custPayTermDay.max' => 'Payment terms cannot exceed 365 days.',
            'custCreditLimit.required' => 'Credit limit is required.',
            'custCreditLimit.max' => 'Credit limit is too large.',
            'custOppDate.required' => 'Opening date is required.',
            'custOppDate.date' => 'Opening date must be a valid date.',
            'custCatagory.required' => 'Category is required.',
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
            'custCode' => 'customer code',
            'custArea' => 'area',
            'bussnessName' => 'business name',
            'custName' => 'customer name',
            'custAddress' => 'address',
            'custContect' => 'contact',
            'custCity' => 'city',
            'custPage' => 'page',
            'custdiscount' => 'discount',
            'custPayTermDay' => 'payment terms',
            'custCreditLimit' => 'credit limit',
            'custOppDate' => 'opening date',
            'custBalance' => 'balance',
            'custCatagory' => 'category',
        ];
    }
}


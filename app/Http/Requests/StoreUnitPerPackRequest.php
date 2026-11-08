<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUnitPerPackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'unitName' => ['required', 'string', 'max:255', 'unique:unit_per_pack,unitName'],
            'piecesPerUnit' => ['required', 'integer', 'min:1', 'max:999999'],
        ];
    }

    public function messages(): array
    {
        return [
            'unitName.required' => 'Unit name is required.',
            'unitName.unique' => 'This unit name already exists.',
            'piecesPerUnit.required' => 'Pieces per unit is required.',
            'piecesPerUnit.integer' => 'Pieces per unit must be a whole number.',
            'piecesPerUnit.min' => 'Pieces per unit must be at least 1.',
        ];
    }

    public function attributes(): array
    {
        return [
            'unitName' => 'unit name',
            'piecesPerUnit' => 'pieces per unit',
        ];
    }
}

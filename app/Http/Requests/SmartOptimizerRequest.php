<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SmartOptimizerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'presupuesto' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'objetivo' => ['nullable', Rule::in(['variety', 'units', 'margin', 'premium'])],
        ];
    }
}

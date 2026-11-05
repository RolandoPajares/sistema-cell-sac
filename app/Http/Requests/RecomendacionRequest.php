<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RecomendacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'presupuesto' => ['nullable', 'numeric', 'min:0'],
            'uso' => ['nullable', Rule::in(['gaming', 'camera', 'study', 'work', 'social'])],
            'prioridad' => ['nullable', Rule::in(['rendimiento', 'camara', 'bateria', 'valor'])],
        ];
    }
}

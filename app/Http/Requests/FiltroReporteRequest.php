<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FiltroReporteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->rol, ['administrador', 'marketing'], true);
    }

    public function rules(): array
    {
        return [
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date', 'after_or_equal:desde'],
        ];
    }
}

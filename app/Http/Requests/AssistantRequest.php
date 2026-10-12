<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssistantRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (! $this->filled('consulta') && $this->filled('message')) {
            $this->merge(['consulta' => $this->input('message')]);
        }
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['consulta' => ['required', 'string', 'max:300']];
    }
}

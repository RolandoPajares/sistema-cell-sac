<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:120'],
            'contacto' => ['required', 'string', 'max:160'],
            'mensaje' => ['required', 'string', 'max:5000'],
        ];
    }
}

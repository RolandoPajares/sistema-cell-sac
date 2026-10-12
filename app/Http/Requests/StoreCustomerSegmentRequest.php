<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomerSegmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->rol, ['administrador', 'marketing'], true);
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:120', Rule::unique('segmentos_clientes', 'nombre')],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['required', Rule::in(User::ROLES)],
        ];
    }
}

<?php

namespace App\Http\Requests;

use App\Servicios\ServicioAutorizacion;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AnularVentaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null && app(ServicioAutorizacion::class)->allowsRoute($this);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return ['motivo' => ['required', 'string', 'min:5', 'max:500']];
    }
}

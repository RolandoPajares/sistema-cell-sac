<?php

namespace App\Http\Requests;

use App\Servicios\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class GuardarPrecioMayoristaRequest extends FormRequest
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
        abort_unless(Schema::hasTable('precios_mayoristas'), 503,
            'Los precios mayoristas requieren aplicar primero la estructura SQL aprobada.');
        $empresaId = app(ServicioAutorizacion::class)->activeCompanyId();
        abort_unless($empresaId !== null, 409, 'Selecciona una empresa activa para registrar precios mayoristas.');

        return [
            'producto_id' => ['required', 'integer', Rule::exists('productos', 'id')->where('empresa_id', $empresaId)],
            'precio' => ['required', 'numeric', 'gt:0', 'max:99999999.99'],
            'vigente_desde' => ['required', 'date'],
            'vigente_hasta' => ['nullable', 'date', 'after_or_equal:vigente_desde'],
            'motivo' => ['required', 'string', 'max:300'],
        ];
    }
}

<?php

namespace App\Http\Requests\Gestion\Comercial\Proveedores;

use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Define la responsabilidad de la clase GuardarProveedorRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class GuardarProveedorRequest extends FormRequest
{
    /**
     * Completa el nombre persistible a partir de los campos visibles del formulario.
     */
    protected function prepareForValidation(): void
    {
        if (trim((string) $this->input('nombre')) !== '') {
            return;
        }

        $nombre = $this->input('tipo_persona') === 'juridica'
            ? trim((string) $this->input('razon_social'))
            : trim(implode(' ', array_filter([
                trim((string) $this->input('nombres')),
                trim((string) $this->input('apellidos')),
            ])));

        $this->merge(['nombre' => $nombre]);
    }

    /**
     * Determina si la operación cumple las condiciones de autorización o validación.
     *
     * @return bool Resultado de la operación.
     */
    public function authorize(): bool
    {
        return $this->user() !== null && app(ServicioAutorizacion::class)->allowsRoute($this);
    }

    /**
     * Define la configuración de validación utilizada por el componente.
     *
     * @return array Resultado de la operación.
     */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:160'],
            'tipo_persona' => ['required', Rule::in(['natural', 'juridica'])],
            'tipo_documento' => ['required', Rule::in(['dni', 'ruc'])],
            'ruc' => [
                'required', 'string', 'max:20',
                $this->input('tipo_documento') === 'dni' ? 'size:8' : 'size:11',
                'regex:/^[0-9]+$/', Rule::unique('proveedores', 'ruc'),
            ],
            'correo' => ['required', 'email', 'max:160'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'ciudad' => ['nullable', 'string', 'max:80'],
        ];
    }
}

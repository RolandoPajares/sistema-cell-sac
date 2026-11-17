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
    // Ajusta o limpia los datos recibidos antes de aplicar las reglas de validacion.
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
    // Comprueba si el usuario actual tiene autorizacion para enviar esta solicitud.
    public function authorize(): bool
    {
        return $this->user() !== null && app(ServicioAutorizacion::class)->allowsRoute($this);
    }

    /**
     * Define la configuración de validación utilizada por el componente.
     *
     * @return array Resultado de la operación.
     */
    // Define las reglas que validan todos los campos recibidos por este formulario.
    public function rules(): array
    {
        return [
            // Valida el campo 'nombre' con las reglas definidas en la instruccion siguiente.
            'nombre' => ['required', 'string', 'max:160'],
            // Valida el campo 'tipo_persona' con las reglas definidas en la instruccion siguiente.
            'tipo_persona' => ['required', Rule::in(['natural', 'juridica'])],
            // Valida el campo 'tipo_documento' con las reglas definidas en la instruccion siguiente.
            'tipo_documento' => ['required', Rule::in(['dni', 'ruc'])],
            // Valida el campo 'ruc' con las reglas definidas en la instruccion siguiente.
            'ruc' => [
                'required', 'string', 'max:20',
                $this->input('tipo_documento') === 'dni' ? 'size:8' : 'size:11',
                'regex:/^[0-9]+$/', Rule::unique('proveedores', 'ruc'),
            ],
            // Valida el campo 'correo' con las reglas definidas en la instruccion siguiente.
            'correo' => ['required', 'email', 'max:160'],
            // Valida el campo 'telefono' con las reglas definidas en la instruccion siguiente.
            'telefono' => ['nullable', 'string', 'max:30'],
            // Valida el campo 'ciudad' con las reglas definidas en la instruccion siguiente.
            'ciudad' => ['nullable', 'string', 'max:80'],
        ];
    }
}

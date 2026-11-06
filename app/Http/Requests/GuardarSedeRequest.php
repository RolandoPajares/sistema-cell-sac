<?php

namespace App\Http\Requests;

use App\Models\Organizacion\Sede;
use App\Models\User;
use App\Servicios\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Define la responsabilidad de la clase GuardarSedeRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class GuardarSedeRequest extends FormRequest
{
    /**
     * Determina si la operación cumple las condiciones de autorización o validación.
     *
     * @return bool Resultado de la operación.
     */
    public function authorize(): bool
    {
        $usuario = $this->user();
        $autorizacion = app(ServicioAutorizacion::class);

        return $usuario instanceof User
            && $autorizacion->allowsRoute($this)
            && $autorizacion->allows($usuario, 'gestion.organizacion.actualizar');
    }

    /** @return array<string, array<int, mixed>|string> */
    public function rules(): array
    {
        $sede = $this->route('sede');
        $empresaId = app(ServicioAutorizacion::class)->activeCompanyId();

        return [
            'codigo' => [
                'required', 'string', 'max:30', 'regex:/^[A-Za-z0-9_-]+$/',
                Rule::unique('sedes', 'codigo')->where('empresa_id', $empresaId)
                    ->ignore($sede instanceof Sede ? $sede->getKey() : null),
            ],
            'nombre' => ['required', 'string', 'max:140'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'zona_horaria' => ['required', 'string', 'timezone', 'max:64'],
            'empresa_id' => ['prohibited'],
            'activa' => ['prohibited'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'codigo.regex' => 'El código solo puede contener letras, números, guion y guion bajo.',
            'codigo.unique' => 'Ya existe una sede con ese código en la empresa.',
            'nombre.max' => 'El nombre de la sede no puede superar 140 caracteres.',
            'direccion.max' => 'La dirección no puede superar 255 caracteres.',
            'telefono.max' => 'El teléfono no puede superar 30 caracteres.',
            'zona_horaria.timezone' => 'Selecciona una zona horaria reconocida.',
            'zona_horaria.max' => 'La zona horaria no puede superar 64 caracteres.',
        ];
    }
}

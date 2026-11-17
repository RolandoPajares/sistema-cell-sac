<?php

namespace App\Http\Requests\Gestion\Administracion\Organizacion;

use App\Models\Organizacion\Sede;
use App\Models\User;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
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
    // Comprueba si el usuario actual tiene autorizacion para enviar esta solicitud.
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
            // Valida el campo 'codigo' con las reglas definidas en la instruccion siguiente.
            'codigo' => [
                'required', 'string', 'max:30', 'regex:/^[A-Za-z0-9_-]+$/',
                Rule::unique('sedes', 'codigo')->where('empresa_id', $empresaId)
                    ->ignore($sede instanceof Sede ? $sede->getKey() : null),
            ],
            // Valida el campo 'nombre' con las reglas definidas en la instruccion siguiente.
            'nombre' => ['required', 'string', 'max:140'],
            // Valida el campo 'direccion' con las reglas definidas en la instruccion siguiente.
            'direccion' => ['nullable', 'string', 'max:255'],
            // Valida el campo 'telefono' con las reglas definidas en la instruccion siguiente.
            'telefono' => ['nullable', 'string', 'max:30'],
            // Valida el campo 'zona_horaria' con las reglas definidas en la instruccion siguiente.
            'zona_horaria' => ['required', 'string', 'timezone', 'max:64'],
            // Valida el campo 'empresa_id' con las reglas definidas en la instruccion siguiente.
            'empresa_id' => ['prohibited'],
            // Valida el campo 'activa' con las reglas definidas en la instruccion siguiente.
            'activa' => ['prohibited'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            // Muestra el mensaje del campo 'codigo' cuando falla la regla 'regex'.
            'codigo.regex' => 'El código solo puede contener letras, números, guion y guion bajo.',
            // Muestra el mensaje del campo 'codigo' cuando falla la regla 'unique'.
            'codigo.unique' => 'Ya existe una sede con ese código en la empresa.',
            // Muestra el mensaje del campo 'nombre' cuando falla la regla 'max'.
            'nombre.max' => 'El nombre de la sede no puede superar 140 caracteres.',
            // Muestra el mensaje del campo 'direccion' cuando falla la regla 'max'.
            'direccion.max' => 'La dirección no puede superar 255 caracteres.',
            // Muestra el mensaje del campo 'telefono' cuando falla la regla 'max'.
            'telefono.max' => 'El teléfono no puede superar 30 caracteres.',
            // Muestra el mensaje del campo 'zona_horaria' cuando falla la regla 'timezone'.
            'zona_horaria.timezone' => 'Selecciona una zona horaria reconocida.',
            // Muestra el mensaje del campo 'zona_horaria' cuando falla la regla 'max'.
            'zona_horaria.max' => 'La zona horaria no puede superar 64 caracteres.',
        ];
    }
}

<?php

namespace App\Http\Requests\Gestion\Finanzas\Cajas;

use App\Models\User;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Define la responsabilidad de la clase GuardarCajaRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class GuardarCajaRequest extends FormRequest
{
    /**
     * Determina si la operación cumple las condiciones de autorización o validación.
     *
     * @return bool Resultado de la operación.
     */
    // Comprueba si el usuario actual tiene autorizacion para enviar esta solicitud.
    public function authorize(): bool
    {
        $authorization = app(ServicioAutorizacion::class);

        return $this->user() instanceof User
            && $authorization->allowsGlobal($this->user(), 'gestion.seguridad.consultar')
            && $authorization->allowsRoute($this);
    }

    /** @return array<string, array<int, string|ValidationRule>> */
    public function rules(): array
    {
        return [
            // Valida el campo 'sede_id' con las reglas definidas en la instruccion siguiente.
            'sede_id' => ['required', 'integer', 'exists:sedes,id'],
            // Valida el campo 'codigo' con las reglas definidas en la instruccion siguiente.
            'codigo' => ['required', 'string', 'max:40', 'regex:/\A[A-Za-z0-9][A-Za-z0-9._-]*\z/',
                Rule::unique('cajas', 'codigo')->where('sede_id', $this->integer('sede_id'))],
            // Valida el campo 'nombre' con las reglas definidas en la instruccion siguiente.
            'nombre' => ['required', 'string', 'max:120'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            // Muestra el mensaje del campo 'sede_id' cuando falla la regla 'required'.
            'sede_id.required' => 'Selecciona una sede.',
            // Muestra el mensaje del campo 'sede_id' cuando falla la regla 'exists'.
            'sede_id.exists' => 'La sede seleccionada no existe.',
            // Muestra el mensaje del campo 'codigo' cuando falla la regla 'required'.
            'codigo.required' => 'Ingresa el código de caja.',
            // Muestra el mensaje del campo 'codigo' cuando falla la regla 'regex'.
            'codigo.regex' => 'El código solo admite letras, números, puntos, guiones y guiones bajos.',
            // Muestra el mensaje del campo 'codigo' cuando falla la regla 'unique'.
            'codigo.unique' => 'Ya existe una caja con ese código en la sede.',
            // Muestra el mensaje del campo 'codigo' cuando falla la regla 'max'.
            'codigo.max' => 'El código no puede superar 40 caracteres.',
            // Muestra el mensaje del campo 'nombre' cuando falla la regla 'required'.
            'nombre.required' => 'Ingresa el nombre de la caja.',
            // Muestra el mensaje del campo 'nombre' cuando falla la regla 'max'.
            'nombre.max' => 'El nombre no puede superar 120 caracteres.',
        ];
    }
}

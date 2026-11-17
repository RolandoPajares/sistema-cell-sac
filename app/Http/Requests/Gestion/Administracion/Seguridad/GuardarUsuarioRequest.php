<?php

namespace App\Http\Requests\Gestion\Administracion\Seguridad;

use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Define la responsabilidad de la clase GuardarUsuarioRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class GuardarUsuarioRequest extends FormRequest
{
    /**
     * Determina si la operación cumple las condiciones de autorización o validación.
     *
     * @return bool Resultado de la operación.
     */
    // Comprueba si el usuario actual tiene autorizacion para enviar esta solicitud.
    public function authorize(): bool
    {
        return $this->user() !== null
            && app(ServicioAutorizacion::class)->allowsGlobal($this->user(), 'gestion.seguridad.crear');
    }

    /**
     * Define la configuración de validación utilizada por el componente.
     *
     * @return array Resultado de la operación.
     */
    // Define las reglas que validan todos los campos recibidos por este formulario.
    public function rules(): array
    {
        $customerRole = $this->esRolCliente();
        $documentType = $this->string('tipo_documento')->lower()->toString();

        return [
            // Valida el campo 'nombre' con las reglas definidas en la instruccion siguiente.
            'nombre' => ['required', 'string', 'max:120'],
            // Valida el campo 'correo' con las reglas definidas en la instruccion siguiente.
            'correo' => ['required', 'email', 'max:160', Rule::unique('usuarios', 'correo')],
            // Valida el campo 'rol' con las reglas definidas en la instruccion siguiente.
            'rol' => ['required', Rule::in($this->rolesDisponibles())],
            // Valida el campo 'sede_id' con las reglas definidas en la instruccion siguiente.
            'sede_id' => ['nullable', 'integer', Rule::exists('sedes', 'id')->where('activa', true)],
            // Valida el campo 'password' con las reglas definidas en la instruccion siguiente.
            'password' => ['required', Password::min(12)->letters()->numbers()],
            // Valida el campo 'password_confirmation' con las reglas definidas en la instruccion siguiente.
            'password_confirmation' => ['required', 'same:password'],
            // Valida el campo 'tipo_documento' con las reglas definidas en la instruccion siguiente.
            'tipo_documento' => [Rule::excludeIf(! $customerRole), $customerRole ? 'required' : 'nullable', Rule::in(['dni', 'ruc'])],
            // Valida el campo 'documento' con las reglas definidas en la instruccion siguiente.
            'documento' => [Rule::excludeIf(! $customerRole), $customerRole ? 'required' : 'nullable', 'string', $documentType === 'dni' ? 'digits:8' : 'digits:11', $documentType === 'ruc' ? 'regex:/^(10|15|17|20)[0-9]{9}$/' : 'nullable', Rule::unique('clientes', 'documento')],
            // Valida el campo 'razon_social' con las reglas definidas en la instruccion siguiente.
            'razon_social' => [Rule::excludeIf(! $customerRole || $documentType !== 'ruc'), $documentType === 'ruc' ? 'required' : 'nullable', 'string', 'max:160'],
            // Valida el campo 'telefono' con las reglas definidas en la instruccion siguiente.
            'telefono' => [Rule::excludeIf(! $customerRole), 'nullable', 'string', 'max:30'],
            // Valida el campo 'ciudad' con las reglas definidas en la instruccion siguiente.
            'ciudad' => [Rule::excludeIf(! $customerRole), 'nullable', 'string', 'max:80'],
        ];
    }

    /**
     * Define la configuración de validación utilizada por el componente.
     *
     * @return array Resultado de la operación.
     */
    // Define los nombres legibles que se usaran en los mensajes de validacion.
    public function attributes(): array
    {
        return [
            'nombre' => 'nombre completo',
            'correo' => 'correo electrónico',
            'rol' => 'rol del usuario',
            'tipo_documento' => 'tipo de documento',
            'password' => 'contraseña temporal',
            'password_confirmation' => 'confirmación de contraseña',
            'documento' => 'documento',
            'razon_social' => 'razón social',
            'telefono' => 'teléfono',
            'ciudad' => 'ciudad',
        ];
    }

    /**
     * Define la configuración de validación utilizada por el componente.
     *
     * @return array Resultado de la operación.
     */
    // Define los mensajes que se mostraran cuando falle una regla de validacion.
    public function messages(): array
    {
        return [
            // Muestra el mensaje del campo 'nombre' cuando falla la regla 'required'.
            'nombre.required' => 'El nombre completo es obligatorio.',
            // Muestra el mensaje del campo 'nombre' cuando falla la regla 'max'.
            'nombre.max' => 'El nombre completo no puede superar los :max caracteres.',
            // Muestra el mensaje del campo 'correo' cuando falla la regla 'required'.
            'correo.required' => 'El correo electrónico es obligatorio.',
            // Muestra el mensaje del campo 'correo' cuando falla la regla 'email'.
            'correo.email' => 'Ingresa un correo electrónico válido.',
            // Muestra el mensaje del campo 'correo' cuando falla la regla 'max'.
            'correo.max' => 'El correo electrónico no puede superar los :max caracteres.',
            // Muestra el mensaje del campo 'correo' cuando falla la regla 'unique'.
            'correo.unique' => 'El correo electrónico ya está registrado.',
            // Muestra el mensaje del campo 'rol' cuando falla la regla 'required'.
            'rol.required' => 'Selecciona un rol para la cuenta.',
            // Muestra el mensaje del campo 'rol' cuando falla la regla 'in'.
            'rol.in' => 'El rol seleccionado ya no está disponible.',
            // Muestra el mensaje del campo 'tipo_documento' cuando falla la regla 'required'.
            'tipo_documento.required' => 'Selecciona si el documento es DNI o RUC.',
            // Muestra el mensaje del campo 'tipo_documento' cuando falla la regla 'in'.
            'tipo_documento.in' => 'El tipo de documento debe ser DNI o RUC.',
            // Muestra el mensaje del campo 'password' cuando falla la regla 'required'.
            'password.required' => 'La contraseña temporal es obligatoria.',
            // Muestra el mensaje del campo 'password' cuando falla la regla 'min'.
            'password.min' => 'La contraseña debe tener al menos :min caracteres.',
            // Muestra el mensaje del campo 'password' cuando falla la regla 'letters'.
            'password.letters' => 'La contraseña debe contener al menos una letra.',
            // Muestra el mensaje del campo 'password' cuando falla la regla 'numbers'.
            'password.numbers' => 'La contraseña debe contener al menos un número.',
            // Muestra el mensaje del campo 'password_confirmation' cuando falla la regla 'same'.
            'password_confirmation.same' => 'La confirmación debe coincidir con la contraseña.',
            // Muestra el mensaje del campo 'password_confirmation' cuando falla la regla 'required'.
            'password_confirmation.required' => 'Confirma la contraseña temporal.',
            // Muestra el mensaje del campo 'documento' cuando falla la regla 'required'.
            'documento.required' => 'El documento es obligatorio para los roles de cliente.',
            // Muestra el mensaje del campo 'documento' cuando falla la regla 'digits'.
            'documento.digits' => 'El :attribute debe tener :digits dígitos.',
            // Muestra el mensaje del campo 'documento' cuando falla la regla 'regex'.
            'documento.regex' => 'Ingresa un RUC peruano válido de 11 dígitos.',
            // Muestra el mensaje del campo 'documento' cuando falla la regla 'string'.
            'documento.string' => 'El documento debe ser texto válido.',
            // Muestra el mensaje del campo 'documento' cuando falla la regla 'max'.
            'documento.max' => 'El documento no puede superar los :max caracteres.',
            // Muestra el mensaje del campo 'documento' cuando falla la regla 'unique'.
            'documento.unique' => 'El documento ya está registrado.',
            // Muestra el mensaje del campo 'razon_social' cuando falla la regla 'required'.
            'razon_social.required' => 'La razón social es obligatoria cuando el documento es RUC.',
            // Muestra el mensaje del campo 'razon_social' cuando falla la regla 'max'.
            'razon_social.max' => 'La razón social no puede superar los :max caracteres.',
            // Muestra el mensaje del campo 'telefono' cuando falla la regla 'max'.
            'telefono.max' => 'El teléfono no puede superar los :max caracteres.',
            // Muestra el mensaje del campo 'ciudad' cuando falla la regla 'max'.
            'ciudad.max' => 'La ciudad no puede superar los :max caracteres.',
        ];
    }

    /** @return array<int,string> */
    private function rolesDisponibles(): array
    {
        if (Schema::hasTable('roles')) {
            $configuredRoles = DB::table('roles')->where('activo', true)->orderBy('codigo')->pluck('codigo')->all();

            return $configuredRoles;
        }

        return [];
    }

    /**
     * Realiza la operación específica definida por este método y devuelve su resultado.
     *
     * @return bool Resultado de la operación.
     */
    private function esRolCliente(): bool
    {
        return in_array($this->string('rol')->toString(), ['cliente_mayorista', 'cliente_minorista'], true);
    }
}

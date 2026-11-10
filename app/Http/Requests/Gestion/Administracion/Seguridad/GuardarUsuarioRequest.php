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
    public function rules(): array
    {
        $customerRole = $this->isCustomerRole();
        $documentType = $this->string('tipo_documento')->lower()->toString();

        return [
            'nombre' => ['required', 'string', 'max:120'],
            'correo' => ['required', 'email', 'max:160', Rule::unique('usuarios', 'correo')],
            'rol' => ['required', Rule::in($this->availableRoles())],
            'sede_id' => ['nullable', 'integer', Rule::exists('sedes', 'id')->where('activa', true)],
            'password' => ['required', Password::min(12)->letters()->numbers()],
            'password_confirmation' => ['required', 'same:password'],
            'tipo_documento' => [Rule::excludeIf(! $customerRole), $customerRole ? 'required' : 'nullable', Rule::in(['dni', 'ruc'])],
            'documento' => [Rule::excludeIf(! $customerRole), $customerRole ? 'required' : 'nullable', 'string', $documentType === 'dni' ? 'digits:8' : 'digits:11', $documentType === 'ruc' ? 'regex:/^(10|15|17|20)[0-9]{9}$/' : 'nullable', Rule::unique('clientes', 'documento')],
            'razon_social' => [Rule::excludeIf(! $customerRole || $documentType !== 'ruc'), $documentType === 'ruc' ? 'required' : 'nullable', 'string', 'max:160'],
            'telefono' => [Rule::excludeIf(! $customerRole), 'nullable', 'string', 'max:30'],
            'ciudad' => [Rule::excludeIf(! $customerRole), 'nullable', 'string', 'max:80'],
        ];
    }

    /**
     * Define la configuración de validación utilizada por el componente.
     *
     * @return array Resultado de la operación.
     */
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
    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre completo es obligatorio.',
            'nombre.max' => 'El nombre completo no puede superar los :max caracteres.',
            'correo.required' => 'El correo electrónico es obligatorio.',
            'correo.email' => 'Ingresa un correo electrónico válido.',
            'correo.max' => 'El correo electrónico no puede superar los :max caracteres.',
            'correo.unique' => 'El correo electrónico ya está registrado.',
            'rol.required' => 'Selecciona un rol para la cuenta.',
            'rol.in' => 'El rol seleccionado ya no está disponible.',
            'tipo_documento.required' => 'Selecciona si el documento es DNI o RUC.',
            'tipo_documento.in' => 'El tipo de documento debe ser DNI o RUC.',
            'password.required' => 'La contraseña temporal es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos :min caracteres.',
            'password.letters' => 'La contraseña debe contener al menos una letra.',
            'password.numbers' => 'La contraseña debe contener al menos un número.',
            'password_confirmation.same' => 'La confirmación debe coincidir con la contraseña.',
            'password_confirmation.required' => 'Confirma la contraseña temporal.',
            'documento.required' => 'El documento es obligatorio para los roles de cliente.',
            'documento.digits' => 'El :attribute debe tener :digits dígitos.',
            'documento.regex' => 'Ingresa un RUC peruano válido de 11 dígitos.',
            'documento.string' => 'El documento debe ser texto válido.',
            'documento.max' => 'El documento no puede superar los :max caracteres.',
            'documento.unique' => 'El documento ya está registrado.',
            'razon_social.required' => 'La razón social es obligatoria cuando el documento es RUC.',
            'razon_social.max' => 'La razón social no puede superar los :max caracteres.',
            'telefono.max' => 'El teléfono no puede superar los :max caracteres.',
            'ciudad.max' => 'La ciudad no puede superar los :max caracteres.',
        ];
    }

    /** @return array<int,string> */
    private function availableRoles(): array
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
    private function isCustomerRole(): bool
    {
        return in_array($this->string('rol')->toString(), ['cliente_mayorista', 'cliente_minorista'], true);
    }
}

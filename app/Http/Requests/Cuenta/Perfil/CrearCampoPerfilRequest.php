<?php

namespace App\Http\Requests\Cuenta\Perfil;

use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CrearCampoPerfilRequest extends FormRequest
{
    // Ajusta o limpia los datos recibidos antes de aplicar las reglas de validacion.
    protected function prepareForValidation(): void
    {
        if ($this->input('opciones') === null || is_array($this->input('opciones'))) {
            return;
        }

        $this->merge([
            'opciones' => collect(preg_split('/\R+/', (string) $this->input('opciones')) ?: [])
                ->map(static fn (string $option): string => trim($option))
                ->filter()
                ->unique()
                ->values()
                ->all(),
        ]);
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    // Comprueba si el usuario actual tiene autorizacion para enviar esta solicitud.
    public function authorize(): bool
    {
        return $this->user() !== null
            && app(ServicioAutorizacion::class)->allowsGlobal($this->user(), 'gestion.seguridad.actualizar');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    // Define las reglas que validan todos los campos recibidos por este formulario.
    public function rules(): array
    {
        return [
            // Valida el campo 'codigo' con las reglas definidas en la instruccion siguiente.
            'codigo' => ['required', 'string', 'alpha_dash', 'max:80', Rule::unique('perfil_campos', 'codigo')],
            // Valida el campo 'etiqueta' con las reglas definidas en la instruccion siguiente.
            'etiqueta' => ['required', 'string', 'max:120'],
            // Valida el campo 'tipo' con las reglas definidas en la instruccion siguiente.
            'tipo' => ['required', Rule::in(['text', 'textarea', 'email', 'tel', 'date', 'number', 'select'])],
            // Valida el campo 'opciones' con las reglas definidas en la instruccion siguiente.
            'opciones' => ['nullable', 'array', 'max:50'],
            // Valida el campo 'opciones.*' con las reglas definidas en la instruccion siguiente.
            'opciones.*' => ['string', 'max:120'],
            // Valida el campo 'orden' con las reglas definidas en la instruccion siguiente.
            'orden' => ['nullable', 'integer', 'min:0', 'max:9999'],
            // Valida el campo 'activo' con las reglas definidas en la instruccion siguiente.
            'activo' => ['sometimes', 'boolean'],
        ];
    }
}

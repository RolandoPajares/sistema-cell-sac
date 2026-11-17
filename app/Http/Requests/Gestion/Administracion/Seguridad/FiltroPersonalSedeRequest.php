<?php

namespace App\Http\Requests\Gestion\Administracion\Seguridad;

use App\Models\User;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Define la responsabilidad de la clase FiltroPersonalSedeRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
final class FiltroPersonalSedeRequest extends FormRequest
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
            && $autorizacion->allows($usuario, 'gestion.organizacion.consultar');
    }

    /** @return array<string, array<int, mixed>|string> */
    public function rules(): array
    {
        $usuario = $this->user();
        $autorizacion = app(ServicioAutorizacion::class);
        $sedesAutorizadas = $usuario instanceof User
            ? $autorizacion->authorizedSiteIds($usuario, 'gestion.organizacion.consultar')
            : [];

        return [
            // Valida el campo 'buscar' con las reglas definidas en la instruccion siguiente.
            'buscar' => ['nullable', 'string', 'max:120'],
            // Valida el campo 'sede_id' con las reglas definidas en la instruccion siguiente.
            'sede_id' => ['nullable', 'integer', Rule::in($sedesAutorizadas)],
            // Valida el campo 'estado' con las reglas definidas en la instruccion siguiente.
            'estado' => ['nullable', Rule::in(['vigente', 'no_vigente'])],
            // Valida el campo 'por_pagina' con las reglas definidas en la instruccion siguiente.
            'por_pagina' => ['nullable', 'integer', Rule::in([15, 30, 50])],
        ];
    }
}

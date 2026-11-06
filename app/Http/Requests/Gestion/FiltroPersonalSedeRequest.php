<?php

namespace App\Http\Requests\Gestion;

use App\Models\User;
use App\Servicios\ServicioAutorizacion;
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
            'buscar' => ['nullable', 'string', 'max:120'],
            'sede_id' => ['nullable', 'integer', Rule::in($sedesAutorizadas)],
            'estado' => ['nullable', Rule::in(['vigente', 'no_vigente'])],
            'por_pagina' => ['nullable', 'integer', Rule::in([15, 30, 50])],
        ];
    }
}

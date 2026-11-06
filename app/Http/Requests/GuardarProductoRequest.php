<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Servicios\ServicioAutorizacion;
use App\Servicios\ServicioInventario;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Define la responsabilidad de la clase GuardarProductoRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class GuardarProductoRequest extends FormRequest
{
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
        $usuarioActual = $this->user();
        abort_unless($usuarioActual instanceof User, 403);
        $autorizacion = app(ServicioAutorizacion::class);
        $permiso = $autorizacion->permissionForRoute($this) ?? 'gestion.productos.consultar';
        $alcance = app(ServicioInventario::class)->alcanceConsulta($usuarioActual, $permiso);

        return [
            'marca' => ['required', 'string', 'max:80'],
            'nombre' => ['required', 'string', 'max:160'],
            'categoria_id' => ['required', 'integer', Rule::exists('categorias', 'id')->where('empresa_id', $alcance['empresa_id'])],
            'precio' => ['required', 'numeric', 'min:0'],
            'precio_original' => ['nullable', 'numeric', 'min:0'],
            'precio_oferta' => ['nullable', 'numeric', 'min:0', 'lte:precio'],
            'url_imagen' => ['nullable', 'string', 'max:255'],
            'imagen' => ['nullable', 'image', 'max:5120'],
            'etiqueta' => ['nullable', 'string', 'max:80'],
            'descripcion' => ['nullable', 'string'],
            'proveedor_id' => ['nullable', 'integer', Rule::exists('proveedores', 'id')->where('empresa_id', $alcance['empresa_id'])],
            'activo' => ['sometimes', 'boolean'],
        ];
    }
}

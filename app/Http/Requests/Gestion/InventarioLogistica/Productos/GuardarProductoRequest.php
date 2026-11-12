<?php

namespace App\Http\Requests\Gestion\InventarioLogistica\Productos;

use App\Models\User;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use App\Servicios\Gestion\InventarioLogistica\ServicioInventario;
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
        $usuarioActual = $this->user();
        abort_unless($usuarioActual instanceof User, 403);
        $autorizacion = app(ServicioAutorizacion::class);
        $permiso = $autorizacion->permissionForRoute($this) ?? 'gestion.productos.consultar';
        $alcance = app(ServicioInventario::class)->alcanceConsulta($usuarioActual, $permiso);

        return [
            // Valida el campo 'marca' con las reglas definidas en la instruccion siguiente.
            'marca' => ['required', 'string', 'max:80'],
            // Valida el campo 'nombre' con las reglas definidas en la instruccion siguiente.
            'nombre' => ['required', 'string', 'max:160'],
            // Valida el campo 'categoria_id' con las reglas definidas en la instruccion siguiente.
            'categoria_id' => ['required', 'integer', Rule::exists('categorias', 'id')->where('empresa_id', $alcance['empresa_id'])],
            // Valida el campo 'precio' con las reglas definidas en la instruccion siguiente.
            'precio' => ['required', 'numeric', 'min:0'],
            // Valida el campo 'precio_original' con las reglas definidas en la instruccion siguiente.
            'precio_original' => ['nullable', 'numeric', 'min:0'],
            // Valida el campo 'existencias' con las reglas definidas en la instruccion siguiente.
            'existencias' => ['nullable', 'integer', 'min:0', 'max:4294967295'],
            // Valida el campo 'precio_oferta' con las reglas definidas en la instruccion siguiente.
            'precio_oferta' => ['nullable', 'numeric', 'min:0', 'lte:precio'],
            // Valida el campo 'url_imagen' con las reglas definidas en la instruccion siguiente.
            'url_imagen' => ['nullable', 'string', 'max:255'],
            // Valida el campo 'imagen' con las reglas definidas en la instruccion siguiente.
            'imagen' => ['nullable', 'image', 'max:5120'],
            // Valida el campo 'etiqueta' con las reglas definidas en la instruccion siguiente.
            'etiqueta' => ['nullable', 'string', 'max:80'],
            // Valida el campo 'descripcion' con las reglas definidas en la instruccion siguiente.
            'descripcion' => ['nullable', 'string'],
            // Valida el campo 'proveedor_id' con las reglas definidas en la instruccion siguiente.
            'proveedor_id' => ['nullable', 'integer', Rule::exists('proveedores', 'id')->where('empresa_id', $alcance['empresa_id'])],
            // Valida el campo 'activo' con las reglas definidas en la instruccion siguiente.
            'activo' => ['sometimes', 'boolean'],
        ];
    }
}

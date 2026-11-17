<?php

namespace App\Http\Requests\Gestion\InventarioLogistica\Productos;

use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

/**
 * Define la responsabilidad de la clase GuardarConfiguracionTiendaRequest dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class GuardarConfiguracionTiendaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    // Comprueba si el usuario actual tiene autorizacion para enviar esta solicitud.
    public function authorize(): bool
    {
        return $this->user() !== null && app(ServicioAutorizacion::class)->allowsRoute($this);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    // Define las reglas que validan todos los campos recibidos por este formulario.
    public function rules(): array
    {
        abort_unless(Schema::hasTable('configuraciones_tienda'), 503,
            'La configuración persistente requiere aplicar primero la estructura SQL aprobada.');
        abort_unless(app(ServicioAutorizacion::class)->activeCompanyId() !== null, 409,
            'Selecciona una empresa activa para guardar la configuración de tienda.');

        return [
            // Valida el campo 'seccion' con las reglas definidas en la instruccion siguiente.
            'seccion' => ['required', Rule::in(['general', 'apariencia', 'pagos', 'envios', 'notificaciones', 'seo'])],
            // Valida el campo 'configuracion' con las reglas definidas en la instruccion siguiente.
            'configuracion' => ['required', 'array'],
            // Valida el campo 'configuracion.general.nombre_tienda' con las reglas definidas en la instruccion siguiente.
            'configuracion.general.nombre_tienda' => ['sometimes', 'required', 'string', 'max:180'],
            // Valida el campo 'configuracion.general.dominio' con las reglas definidas en la instruccion siguiente.
            'configuracion.general.dominio' => ['nullable', 'url', 'max:253'],
            // Valida el campo 'configuracion.general.correo' con las reglas definidas en la instruccion siguiente.
            'configuracion.general.correo' => ['nullable', 'email', 'max:160'],
            // Valida el campo 'configuracion.general.telefono' con las reglas definidas en la instruccion siguiente.
            'configuracion.general.telefono' => ['nullable', 'string', 'max:30'],
            // Valida el campo 'configuracion.general.descripcion' con las reglas definidas en la instruccion siguiente.
            'configuracion.general.descripcion' => ['nullable', 'string', 'max:1000'],
            // Valida el campo 'configuracion.apariencia.color_principal' con las reglas definidas en la instruccion siguiente.
            'configuracion.apariencia.color_principal' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            // Valida el campo 'configuracion.apariencia.estilo_portada' con las reglas definidas en la instruccion siguiente.
            'configuracion.apariencia.estilo_portada' => ['nullable', Rule::in(['banner', 'productos_destacados'])],
            // Valida el campo 'configuracion.pagos.tipo_precios' con las reglas definidas en la instruccion siguiente.
            'configuracion.pagos.tipo_precios' => ['nullable', Rule::in(['minorista', 'minorista_mayorista'])],
            // Valida el campo 'configuracion.pagos.medios_pago' con las reglas definidas en la instruccion siguiente.
            'configuracion.pagos.medios_pago' => ['sometimes', 'array'],
            // Valida el campo 'configuracion.pagos.medios_pago.*' con las reglas definidas en la instruccion siguiente.
            'configuracion.pagos.medios_pago.*' => ['integer', Rule::exists('medios_pago', 'id')->where('activo', true)],
            // Valida el campo 'configuracion.envios.costo_estandar' con las reglas definidas en la instruccion siguiente.
            'configuracion.envios.costo_estandar' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            // Valida el campo 'configuracion.envios.recojo_tienda' con las reglas definidas en la instruccion siguiente.
            'configuracion.envios.recojo_tienda' => ['nullable', 'boolean'],
            // Valida el campo 'configuracion.envios.plazo_estimado' con las reglas definidas en la instruccion siguiente.
            'configuracion.envios.plazo_estimado' => ['nullable', 'integer', 'min:0', 'max:365'],
            // Valida el campo 'configuracion.notificaciones.confirmacion_pedido' con las reglas definidas en la instruccion siguiente.
            'configuracion.notificaciones.confirmacion_pedido' => ['nullable', Rule::in(['pendiente', 'correo'])],
            // Valida el campo 'configuracion.notificaciones.cambio_estado' con las reglas definidas en la instruccion siguiente.
            'configuracion.notificaciones.cambio_estado' => ['nullable', Rule::in(['pendiente', 'correo'])],
            // Valida el campo 'configuracion.notificaciones.correo_remitente' con las reglas definidas en la instruccion siguiente.
            'configuracion.notificaciones.correo_remitente' => ['nullable', 'email', 'max:160'],
            // Valida el campo 'configuracion.seo.titulo' con las reglas definidas en la instruccion siguiente.
            'configuracion.seo.titulo' => ['nullable', 'string', 'max:180'],
            // Valida el campo 'configuracion.seo.descripcion' con las reglas definidas en la instruccion siguiente.
            'configuracion.seo.descripcion' => ['nullable', 'string', 'max:500'],
            // Valida el campo 'configuracion.seo.analitica_id' con las reglas definidas en la instruccion siguiente.
            'configuracion.seo.analitica_id' => ['nullable', 'string', 'max:120'],
            // Valida el campo 'configuracion.seo.pixel_id' con las reglas definidas en la instruccion siguiente.
            'configuracion.seo.pixel_id' => ['nullable', 'string', 'max:120'],
        ];
    }
}

<?php

namespace App\Http\Requests;

use App\Servicios\ServicioAutorizacion;
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
    public function authorize(): bool
    {
        return $this->user() !== null && app(ServicioAutorizacion::class)->allowsRoute($this);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        abort_unless(Schema::hasTable('configuraciones_tienda'), 503,
            'La configuraciÃ³n persistente requiere aplicar primero la estructura SQL aprobada.');
        abort_unless(app(ServicioAutorizacion::class)->activeCompanyId() !== null, 409,
            'Selecciona una empresa activa para guardar la configuraciÃ³n de tienda.');

        return [
            'seccion' => ['required', Rule::in(['general', 'apariencia', 'pagos', 'envios', 'notificaciones', 'seo'])],
            'configuracion' => ['required', 'array'],
            'configuracion.general.nombre_tienda' => ['sometimes', 'required', 'string', 'max:180'],
            'configuracion.general.dominio' => ['nullable', 'url', 'max:253'],
            'configuracion.general.correo' => ['nullable', 'email', 'max:160'],
            'configuracion.general.telefono' => ['nullable', 'string', 'max:30'],
            'configuracion.general.descripcion' => ['nullable', 'string', 'max:1000'],
            'configuracion.apariencia.color_principal' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'configuracion.apariencia.estilo_portada' => ['nullable', Rule::in(['banner', 'productos_destacados'])],
            'configuracion.pagos.tipo_precios' => ['nullable', Rule::in(['minorista', 'minorista_mayorista'])],
            'configuracion.pagos.medios_pago' => ['sometimes', 'array'],
            'configuracion.pagos.medios_pago.*' => ['integer', Rule::exists('medios_pago', 'id')->where('activo', true)],
            'configuracion.envios.costo_estandar' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'configuracion.envios.recojo_tienda' => ['nullable', 'boolean'],
            'configuracion.envios.plazo_estimado' => ['nullable', 'integer', 'min:0', 'max:365'],
            'configuracion.notificaciones.confirmacion_pedido' => ['nullable', Rule::in(['pendiente', 'correo'])],
            'configuracion.notificaciones.cambio_estado' => ['nullable', Rule::in(['pendiente', 'correo'])],
            'configuracion.notificaciones.correo_remitente' => ['nullable', 'email', 'max:160'],
            'configuracion.seo.titulo' => ['nullable', 'string', 'max:180'],
            'configuracion.seo.descripcion' => ['nullable', 'string', 'max:500'],
            'configuracion.seo.analitica_id' => ['nullable', 'string', 'max:120'],
            'configuracion.seo.pixel_id' => ['nullable', 'string', 'max:120'],
        ];
    }
}

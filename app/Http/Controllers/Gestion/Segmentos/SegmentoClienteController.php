<?php

namespace App\Http\Controllers\Gestion\Segmentos;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActualizarSegmentoClienteRequest;
use App\Http\Requests\GuardarSegmentoClienteRequest;
use App\Models\Marketing\SegmentoCliente;
use App\Servicios\ExportadorTabular;
use App\Servicios\RegistradorAuditoria;
use App\Servicios\ServicioAutorizacion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Define la responsabilidad de la clase SegmentoClienteController dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class SegmentoClienteController extends Controller
{
    /**
     * Obtiene y prepara la información solicitada para la operación.
     *
     * @return View Resultado de la operación.
     */
    public function index(Request $request, ServicioAutorizacion $authorization): View
    {
        $search = trim((string) request('buscar', ''));
        $tipo = request('tipo');
        $estado = request('estado');
        $query = SegmentoCliente::query()->orderBy('nombre');

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('nombre', 'like', "%{$search}%")
                    ->orWhere('tipo', 'like', "%{$search}%");
            });
        }
        if ($tipo === 'roles') {
            $query->where('tipo', $tipo);
        }
        if (in_array($estado, ['activo', 'inactivo'], true)) {
            $query->where('activo', $estado === 'activo');
        }

        $allSegments = SegmentoCliente::query();

        return view('gestion.marketing.segmentos.listar', [
            'segments' => $query->paginate(30)->withQueryString(),
            'activeSegments' => (clone $allSegments)->where('activo', true)->count(),
            'dynamicSegments' => (clone $allSegments)->where('tipo', '!=', 'roles')->count(),
            'roles' => DB::table('roles')->where('activo', true)->orderBy('nombre')->pluck('codigo'),
            'isAdmin' => $authorization->allows($request->user(), 'gestion.seguridad.consultar'),
        ]);
    }

    /**
     * Registra la información recibida y coordina su persistencia.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $audit  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function store(GuardarSegmentoClienteRequest $request, RegistradorAuditoria $audit): RedirectResponse
    {
        $data = $request->validated();
        $segment = SegmentoCliente::create([
            'nombre' => $data['nombre'],
            'tipo' => 'roles',
            'criterios' => ['roles' => array_values(array_unique($data['roles']))],
            'activo' => true,
        ]);
        $audit->record($request->user()->id, 'segment.created', 'segmentos_clientes', (int) $segment->id, null, ['nombre' => $segment->nombre, 'criterios' => $segment->criterios], $request->ip());

        return back()->with('status', 'Segmento creado. Sus campaÃ±as se mostrarÃ¡n a los roles seleccionados.');
    }

    /**
     * Genera la salida exportable de la información disponible.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $exporter  Parámetro recibido por la operación.
     * @param  mixed  $audit  Parámetro recibido por la operación.
     * @return Response|StreamedResponse Resultado de la operación.
     */
    public function export(Request $request, ExportadorTabular $exporter, RegistradorAuditoria $audit): Response|StreamedResponse
    {
        $format = (string) ($request->route('format') ?: $request->string('format', 'csv')->toString());
        abort_unless(in_array($format, ['csv', 'excel', 'pdf'], true), 422, 'Formato de exportación no permitido.');
        $query = SegmentoCliente::query()->orderBy('nombre')
            ->when($request->filled('buscar'), fn ($q) => $q->where(fn ($terms) => $terms->where('nombre', 'like', '%'.$request->string('buscar')->toString().'%')->orWhere('tipo', 'like', '%'.$request->string('buscar')->toString().'%')))
            ->when($request->string('tipo')->toString() === 'roles', fn ($q) => $q->where('tipo', 'roles'))
            ->when(in_array($request->string('estado')->toString(), ['activo', 'inactivo'], true), fn ($q) => $q->where('activo', $request->string('estado')->toString() === 'activo'));
        $segments = $query->limit(5000)->get();
        $rows = $segments->map(fn (SegmentoCliente $segment): array => [
            $segment->nombre,
            $segment->tipo ?: 'manual',
            $segment->activo ? 'Activo' : 'Inactivo',
            implode(', ', $segment->criterios['roles'] ?? []),
            $segment->created_at?->format('Y-m-d H:i:s'),
        ])->all();
        $response = $exporter->download($format, 'segmentos-clientes-'.now()->format('Ymd'),
            ['Segmento', 'Tipo', 'Estado', 'Roles', 'Creado'], $rows, [
                'title' => 'REPORTE DE SEGMENTOS DE CLIENTES',
                'subtitle' => 'Datos reales de segmentos almacenados en MariaDB.',
                'module' => 'Ecommerce / Marketing', 'report_name' => 'Segmentos',
                'filters' => $request->only(['buscar', 'tipo', 'estado']),
                'generated_by' => (string) $request->user()->nombre,
                'role' => 'Usuario autorizado', 'scope' => 'Empresa y permisos vigentes.',
                'indicators' => [['label' => 'Segmentos incluidos', 'value' => count($rows)]],
            ]);
        $audit->record($request->user()->id, 'exportar', 'segmentos_clientes', null, null, ['formato' => $format, 'cantidad' => count($rows)], $request->ip());

        return $response;
    }

    /**
     * Actualiza la información existente con los datos recibidos.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $segment  Parámetro recibido por la operación.
     * @param  mixed  $audit  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function update(ActualizarSegmentoClienteRequest $request, SegmentoCliente $segment, RegistradorAuditoria $audit): RedirectResponse
    {
        $before = $segment->getAttributes();
        $data = $request->validated();
        $segment->update([
            'nombre' => $data['nombre'],
            'criterios' => ['roles' => array_values(array_unique($data['roles']))],
            'activo' => (bool) ($data['activo'] ?? $segment->activo),
        ]);
        $audit->record($request->user()->id, 'segment.updated', 'segmentos_clientes', (int) $segment->id, $before, $segment->fresh()->getAttributes(), $request->ip());

        return back()->with('status', 'Segmento actualizado correctamente.');
    }

    /**
     * Modifica el estado o elimina el registro según la operación solicitada.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $segment  Parámetro recibido por la operación.
     * @param  mixed  $audit  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function deactivate(Request $request, SegmentoCliente $segment, RegistradorAuditoria $audit): RedirectResponse
    {
        $before = $segment->getAttributes();
        $segment->update(['activo' => false]);
        $audit->record($request->user()->id, 'segment.deactivated', 'segmentos_clientes', (int) $segment->id, $before, $segment->fresh()->getAttributes(), $request->ip());

        return back()->with('status', 'Segmento desactivado correctamente.');
    }
}

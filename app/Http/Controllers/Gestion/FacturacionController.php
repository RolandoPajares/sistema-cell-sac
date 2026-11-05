<?php

namespace App\Http\Controllers\Gestion;

use App\Http\Controllers\Controller;
use App\Servicios\ExportadorTabular;
use App\Servicios\RegistradorAuditoria;
use App\Servicios\ServicioAutorizacion;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FacturacionController extends Controller
{
    public function index(Request $request, ServicioAutorizacion $autorizacion): View
    {
        $filtros = $request->validate([
            'buscar' => ['nullable', 'string', 'max:100'],
            'estado' => ['nullable', 'string', 'max:30'],
            'tipo' => ['nullable', 'string', 'size:2'],
            'periodo' => ['nullable', 'in:mes,ultimos_30_dias,anio'],
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date', 'after_or_equal:desde'],
        ]);
        $empresaId = $autorizacion->activeCompanyId();
        abort_unless($empresaId !== null, 409, 'Selecciona una empresa activa para consultar facturación.');
        $sedesId = $autorizacion->authorizedSiteIds($request->user(), 'gestion.facturacion.consultar');

        $consulta = DB::table('documentos_electronicos as documentos')
            ->leftJoin('tipos_documento_electronico as tipos', 'tipos.codigo', '=', 'documentos.tipo_documento_id')
            ->where('documentos.empresa_id', $empresaId)
            ->whereIn('documentos.sede_id', $sedesId)
            ->when($filtros['buscar'] ?? null, static function (Builder $consulta, string $buscar): void {
                $consulta->where(static function (Builder $coincidencias) use ($buscar): void {
                    $coincidencias->where('documentos.serie', 'like', '%'.$buscar.'%')
                        ->orWhere('documentos.correlativo', 'like', '%'.$buscar.'%')
                        ->orWhere('documentos.estado', 'like', '%'.$buscar.'%');
                });
            })
            ->when($filtros['tipo'] ?? null, static fn (Builder $consulta, string $tipo): Builder => $consulta->where('documentos.tipo_documento_id', $tipo))
            ->when($filtros['estado'] ?? null, static fn (Builder $consulta, string $estado): Builder => $consulta->where('documentos.estado', $estado))
            ->when(($filtros['periodo'] ?? null) === 'mes', static fn (Builder $consulta): Builder => $consulta->whereBetween('documentos.fecha_emision', [now()->startOfMonth(), now()->endOfMonth()]))
            ->when(($filtros['periodo'] ?? null) === 'ultimos_30_dias', static fn (Builder $consulta): Builder => $consulta->where('documentos.fecha_emision', '>=', now()->subDays(30)))
            ->when(($filtros['periodo'] ?? null) === 'anio', static fn (Builder $consulta): Builder => $consulta->whereYear('documentos.fecha_emision', now()->year));

        $cantidadDocumentos = (clone $consulta)->count('documentos.id');
        $cantidadEstados = (clone $consulta)->select('documentos.estado')->distinct()->get()->count();
        $cantidadTipos = (clone $consulta)->select('documentos.tipo_documento_id')->distinct()->get()->count();
        $importesPorMoneda = (clone $consulta)->select('documentos.moneda')
            ->selectRaw('SUM(documentos.total) AS importe_total')
            ->groupBy('documentos.moneda')->get();
        $documentos = (clone $consulta)->select([
            'documentos.id', 'documentos.sede_id', 'documentos.tipo_documento_id', 'tipos.nombre as tipo_nombre',
            'documentos.serie', 'documentos.correlativo', 'documentos.estado', 'documentos.moneda',
            'documentos.total', 'documentos.fecha_emision', 'documentos.receptor_historico',
        ])->orderByDesc('documentos.fecha_emision')->orderByDesc('documentos.id')->paginate(20)->withQueryString();
        $estados = DB::table('documentos_electronicos')->where('empresa_id', $empresaId)
            ->whereIn('sede_id', $sedesId)->distinct()->orderBy('estado')->pluck('estado')->all();
        $resumenEstados = (clone $consulta)->select('documentos.estado')
            ->selectRaw('COUNT(*) AS cantidad')->groupBy('documentos.estado')->orderBy('documentos.estado')->get();

        $modulo = require resource_path('views/gestion/facturacion/datos.php');
        $modulo['mode'] = 'consult';
        $modulo['connected'] = true;
        $modulo['area'] = 'Finanzas y Facturación';
        $modulo['route'] = 'management.billing';
        $modulo['table_title'] = 'Listado de comprobantes';
        $modulo['record_plural'] = 'comprobantes';
        $modulo['export_route'] = 'management.billing.export';
        $modulo['export_label'] = 'Exportar comprobantes';
        $tipos = DB::table('tipos_documento_electronico')->where('activo', true)->orderBy('nombre')->pluck('nombre', 'codigo')->all();
        $modulo['filters'] = $filtros + ['buscar' => null, 'estado' => null, 'tipo' => null, 'periodo' => null];
        $modulo['filter_options'] = ['statuses' => $estados, 'types' => $tipos];
        $modulo['records'] = $documentos->getCollection()->map(static function (object $documento): array {
            $receptor = json_decode((string) $documento->receptor_historico, true);
            $nombreReceptor = is_array($receptor)
                ? ($receptor['razon_social'] ?? $receptor['nombre'] ?? $receptor['nombre_completo'] ?? 'Sin nombre en el receptor histórico')
                : 'Sin nombre en el receptor histórico';

            return [
                'cells' => [
                    $documento->serie.'-'.str_pad((string) $documento->correlativo, 8, '0', STR_PAD_LEFT),
                    $nombreReceptor,
                    $documento->tipo_nombre ?? $documento->tipo_documento_id,
                    $documento->moneda.' '.number_format((float) $documento->total, 2, '.', ','),
                    $documento->estado,
                    $documento->fecha_emision,
                ],
            ];
        })->all();
        $modulo['columns'] = ['Comprobante', 'Receptor', 'Tipo', 'Importe', 'Estado', 'Emitido'];
        $modulo['pagination'] = $documentos;
        $modulo['total_count'] = $documentos->total();
        $modulo['summary'] = $resumenEstados->map(static fn (object $estado): array => [
            'title' => (string) $estado->estado,
            'icon' => 'bi-receipt',
            'tone' => 'blue',
            'text' => number_format((int) $estado->cantidad).' documentos en MariaDB',
        ])->all();
        $modulo['kpis'] = [
            ['label' => 'Documentos encontrados', 'value' => $cantidadDocumentos, 'helper' => 'Registros en el alcance autorizado', 'icon' => 'bi-receipt', 'tone' => 'green'],
            ['label' => 'Importes registrados', 'value' => $importesPorMoneda->map(static fn (object $importe): string => $importe->moneda.' '.number_format((float) $importe->importe_total, 2, '.', ','))->implode(' · ') ?: '0', 'helper' => 'Totales separados por moneda', 'icon' => 'bi-cash-coin', 'tone' => 'orange'],
            ['label' => 'Estados existentes', 'value' => $cantidadEstados, 'helper' => 'Estados presentes en MariaDB', 'icon' => 'bi-list-check', 'tone' => 'red'],
            ['label' => 'Tipos documentales', 'value' => $cantidadTipos, 'helper' => 'Tipos usados en MariaDB', 'icon' => 'bi-file-earmark-text', 'tone' => 'purple'],
        ];

        return view('gestion.facturacion.listar', ['module' => $modulo]);
    }

    public function export(
        Request $request,
        string $format,
        ServicioAutorizacion $autorizacion,
        ExportadorTabular $exportador,
        RegistradorAuditoria $auditoria,
    ): Response|StreamedResponse {
        abort_unless(in_array($format, ['csv', 'excel', 'pdf'], true), 422, 'Formato de exportación no permitido.');
        $filtros = $request->validate([
            'buscar' => ['nullable', 'string', 'max:100'],
            'estado' => ['nullable', 'string', 'max:30'],
            'tipo' => ['nullable', 'string', 'size:2'],
            'periodo' => ['nullable', 'in:mes,ultimos_30_dias,anio'],
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date', 'after_or_equal:desde'],
        ]);
        $empresaId = $autorizacion->activeCompanyId();
        abort_unless($empresaId !== null, 409, 'Selecciona una empresa activa para exportar facturación.');
        $sedesId = $autorizacion->authorizedSiteIds($request->user(), 'gestion.facturacion.consultar');
        $consulta = DB::table('documentos_electronicos as documentos')
            ->leftJoin('tipos_documento_electronico as tipos', 'tipos.codigo', '=', 'documentos.tipo_documento_id')
            ->where('documentos.empresa_id', $empresaId)
            ->whereIn('documentos.sede_id', $sedesId)
            ->when($filtros['buscar'] ?? null, static function (Builder $query, string $buscar): void {
                $query->where(static function (Builder $coincidencias) use ($buscar): void {
                    $coincidencias->where('documentos.serie', 'like', '%'.$buscar.'%')
                        ->orWhere('documentos.correlativo', 'like', '%'.$buscar.'%')
                        ->orWhere('documentos.estado', 'like', '%'.$buscar.'%');
                });
            })
            ->when($filtros['tipo'] ?? null, static fn (Builder $query, string $tipo): Builder => $query->where('documentos.tipo_documento_id', $tipo))
            ->when($filtros['estado'] ?? null, static fn (Builder $query, string $estado): Builder => $query->where('documentos.estado', $estado))
            ->when(($filtros['periodo'] ?? null) === 'mes', static fn (Builder $query): Builder => $query->whereBetween('documentos.fecha_emision', [now()->startOfMonth(), now()->endOfMonth()]))
            ->when(($filtros['periodo'] ?? null) === 'ultimos_30_dias', static fn (Builder $query): Builder => $query->where('documentos.fecha_emision', '>=', now()->subDays(30)))
            ->when(($filtros['periodo'] ?? null) === 'anio', static fn (Builder $query): Builder => $query->whereYear('documentos.fecha_emision', now()->year))
            ->select([
                'documentos.id', 'documentos.serie', 'documentos.correlativo', 'documentos.tipo_documento_id',
                'tipos.nombre as tipo_nombre', 'documentos.estado', 'documentos.moneda', 'documentos.total',
                'documentos.fecha_emision', 'documentos.receptor_historico',
            ])->orderBy('documentos.fecha_emision')->orderBy('documentos.id')->get();
        $rows = $consulta->map(static function (object $documento): array {
            $receptor = json_decode((string) $documento->receptor_historico, true);
            $nombre = is_array($receptor) ? ($receptor['razon_social'] ?? $receptor['nombre'] ?? $receptor['nombre_completo'] ?? 'Sin nombre') : 'Sin nombre';

            return [
                $documento->serie.'-'.str_pad((string) $documento->correlativo, 8, '0', STR_PAD_LEFT),
                $nombre,
                $documento->tipo_nombre ?? $documento->tipo_documento_id,
                $documento->moneda,
                (float) $documento->total,
                $documento->estado,
                $documento->fecha_emision,
            ];
        })->all();
        $response = $exportador->download($format, 'comprobantes-electronicos',
            ['Comprobante', 'Receptor', 'Tipo', 'Moneda', 'Total', 'Estado', 'Emitido'],
            $rows,
            [
                'title' => 'COMPROBANTES ELECTRÓNICOS',
                'subtitle' => 'Datos reales consultados desde MariaDB.',
                'module' => 'Finanzas y Facturación',
                'report_name' => 'Facturación',
                'filters' => $filtros,
                'generated_by' => (string) $request->user()->nombre,
                'role' => 'Usuario autorizado',
                'scope' => 'Empresa y sedes autorizadas por RBAC.',
                'indicators' => [['label' => 'Comprobantes incluidos', 'value' => count($rows)]],
                'column_types' => ['text', 'text', 'text', 'text', 'currency', 'text', 'datetime'],
            ]);
        $auditoria->record($request->user()->getKey(), 'exportar', 'documentos_electronicos', null, null,
            ['formato' => $format, 'cantidad' => count($rows), 'filtros' => $filtros], $request->ip(), 'exito', null, ['empresa_id' => $empresaId]);

        return $response;
    }
}

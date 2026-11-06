<?php

namespace App\Http\Controllers\Gestion\Reportes;

use App\Http\Controllers\Controller;
use App\Models\Auditoria\RegistroAuditoria;
use App\Models\Catalogo\Producto;
use App\Models\Clientes\Cliente;
use App\Models\Comercio\Pedido;
use App\Models\Comercio\Venta;
use App\Models\Compras\Compra;
use App\Models\Inventario\MovimientoInventario;
use App\Models\Marketing\Campania;
use App\Models\User;
use App\Servicios\ExportadorTabular;
use App\Servicios\RegistradorAuditoria;
use App\Servicios\ServicioAutorizacion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class PanelAnalisisAuditoriaController extends Controller
{
    public function globales(Request $request, ServicioAutorizacion $autorizacion): View
    {
        $autorizacion->assertAllowed($request->user(), 'gestion.reportes.consultar');

        return $this->mostrar($request, 'globales', $autorizacion);
    }

    public function operaciones(Request $request, ServicioAutorizacion $autorizacion): View
    {
        $autorizacion->assertAllowed($request->user(), 'gestion.auditoria.consultar');

        return $this->mostrar($request, 'operaciones', $autorizacion);
    }

    public function mostrar(Request $request, string $tipo, ServicioAutorizacion $autorizacion): View
    {
        abort_unless(in_array($tipo, ['globales', 'operaciones'], true), 404);

        $usuario = $request->user();
        $filtros = $request->validate([
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date', 'after_or_equal:desde'],
            'modulo' => ['nullable', 'string', 'max:80'],
            'accion' => ['nullable', 'string', 'max:80'],
            'resultado' => ['nullable', 'string', 'max:20'],
            'rol_codigo' => ['nullable', 'string', 'max:40'],
            'estado' => ['nullable', 'string', 'max:30'],
            'canal' => ['nullable', 'string', 'max:30'],
            'sede' => ['nullable', 'integer', 'min:1'],
            'usuario_id' => ['nullable', 'integer', 'min:1'],
            'reporte' => ['nullable', 'in:ventas,pedidos,clientes,productos,inventario,compras,campanas'],
            'buscar' => ['nullable', 'string', 'max:120'],
        ]);

        $filtros['reporte'] = $filtros['reporte'] ?? 'ventas';

        $pagina = $this->estructura($tipo);
        if ($tipo === 'globales') {
            $this->cargarReporte($pagina, $tipo, $usuario, $autorizacion, $filtros);
        } else {
            $this->cargarAuditoria($pagina, $tipo, $usuario, $autorizacion, $filtros);
        }

        return view($this->vista($tipo), [
            'pagina' => $pagina,
            'tipo' => $tipo,
            'filtros' => $filtros,
            'puedeExportar' => $tipo === 'globales' && $autorizacion->allows($usuario, 'gestion.reportes.exportar'),
            'puedeExportarAuditoria' => $tipo === 'operaciones' && $autorizacion->allows($usuario, 'gestion.auditoria.exportar'),
        ]);
    }

    public function exportar(Request $request, string $tipo, ServicioAutorizacion $autorizacion, ExportadorTabular $exportador, RegistradorAuditoria $auditoria, string $format = 'csv'): Response|StreamedResponse
    {
        abort_unless(in_array($tipo, ['globales', 'operaciones'], true), 404);
        abort_unless(in_array($format, ['csv', 'excel', 'pdf'], true), 422, 'Formato de exportación no permitido.');
        $autorizacion->assertAllowed($request->user(), $tipo === 'globales' ? 'gestion.reportes.exportar' : 'gestion.auditoria.exportar');
        $filtros = $request->validate([
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date', 'after_or_equal:desde'],
            'canal' => ['nullable', 'string', 'max:30'],
            'sede' => ['nullable', 'integer', 'min:1'],
            'usuario_id' => ['nullable', 'integer', 'min:1'],
            'modulo' => ['nullable', 'string', 'max:80'],
            'accion' => ['nullable', 'string', 'max:80'],
            'resultado' => ['nullable', 'string', 'max:20'],
            'rol_codigo' => ['nullable', 'string', 'max:40'],
            'estado' => ['nullable', 'string', 'max:30'],
            'reporte' => ['nullable', 'in:ventas,pedidos,clientes,productos,inventario,compras,campanas'],
            'buscar' => ['nullable', 'string', 'max:120'],
        ]);

        if ($tipo === 'globales' && ($filtros['reporte'] ?? 'ventas') !== 'ventas') {
            $pagina = $this->estructura('globales');
            $this->cargarReportePorTipo($pagina, (string) $filtros['reporte'], $request->user(), $autorizacion, $filtros);
            $columnas = $pagina['columnas'];
            $filas = $pagina['filas'];
        } elseif ($tipo === 'globales') {
            $consulta = $autorizacion->scopeRecordsFor(Venta::query()->where('estado', 'confirmada'), $request->user(), 'gestion.ventas.consultar')
                ->when(isset($filtros['desde']), fn (Builder $q) => $q->whereDate('fecha_venta', '>=', $filtros['desde']))
                ->when(isset($filtros['hasta']), fn (Builder $q) => $q->whereDate('fecha_venta', '<=', $filtros['hasta']))
                ->when(isset($filtros['sede']), fn (Builder $q) => $q->where('sede_id', $filtros['sede']))
                ->when(isset($filtros['estado']), fn (Builder $q) => $q->where('estado', $filtros['estado']))
                ->when(isset($filtros['canal']), fn (Builder $q) => $q->whereHas('pedido', fn (Builder $pedido) => $pedido->where('canal', $filtros['canal'])))
                ->select(['id', 'codigo', 'empresa_id', 'sede_id', 'total', 'estado', 'fecha_venta']);
            $registros = $consulta->get();
            $columnas = ['Id', 'Código', 'Empresa', 'Sede', 'Total', 'Estado', 'Fecha'];
            $filas = $registros->map(static fn (Venta $venta): array => [
                $venta->id, $venta->codigo, $venta->empresa_id, $venta->sede_id,
                $venta->total, $venta->estado, $venta->fecha_venta,
            ])->all();
        } else {
            $consulta = $autorizacion->scopeRecordsFor(RegistroAuditoria::query(), $request->user(), 'gestion.auditoria.consultar')
                ->when(isset($filtros['desde']), fn (Builder $q) => $q->whereDate('creado_en', '>=', $filtros['desde']))
                ->when(isset($filtros['hasta']), fn (Builder $q) => $q->whereDate('creado_en', '<=', $filtros['hasta']))
                ->when(isset($filtros['usuario_id']), fn (Builder $q) => $q->where('usuario_id', $filtros['usuario_id']))
                ->when(isset($filtros['modulo']), fn (Builder $q) => $q->where('modulo', $filtros['modulo']))
                ->when(isset($filtros['accion']), fn (Builder $q) => $q->where('accion', $filtros['accion']))
                ->when(isset($filtros['resultado']), fn (Builder $q) => $q->where('resultado', $filtros['resultado']))
                ->when(isset($filtros['rol_codigo']), fn (Builder $q) => $q->where('rol_codigo', $filtros['rol_codigo']))
                ->select(['id', 'usuario_id', 'modulo', 'accion', 'entidad', 'entidad_id', 'resultado', 'direccion_ip', 'creado_en']);
            $registros = $consulta->get();
            $columnas = ['Id', 'Usuario ID', 'Módulo', 'Acción', 'Entidad', 'Entidad ID', 'Resultado', 'Dirección IP', 'Fecha'];
            $filas = $registros->map(static fn (RegistroAuditoria $registro): array => [
                $registro->id, $registro->usuario_id, $registro->modulo, $registro->accion,
                $registro->entidad, $registro->entidad_id, $registro->resultado,
                $registro->direccion_ip, $registro->creado_en,
            ])->all();
        }

        $respuesta = $exportador->download($format, 'analisis-auditoria-'.$tipo, $columnas, $filas, [
            'title' => mb_strtoupper((string) $tipo).' — ANÁLISIS Y AUDITORÍA',
            'subtitle' => 'Datos reales consultados desde MariaDB.',
            'module' => 'Análisis y Auditoría',
            'report_name' => $tipo,
            'filters' => $filtros,
            'generated_by' => (string) $request->user()->nombre,
            'role' => 'Usuario autorizado',
            'scope' => 'Empresa, sede y permisos RBAC vigentes.',
            'indicators' => [['label' => 'Registros incluidos', 'value' => count($filas)]],
            'column_types' => array_fill(0, count($columnas), 'text'),
        ]);
        $auditoria->record((int) $request->user()->getKey(), 'exportar', 'registros_auditoria', null, null, [
            'tipo' => $tipo,
            'formato' => $format,
            'desde' => $filtros['desde'] ?? null,
            'hasta' => $filtros['hasta'] ?? null,
            'sede_id' => $filtros['sede'] ?? null,
            'cantidad' => count($filas),
        ], $request->ip());

        return $respuesta;
    }

    /** @param array<string, mixed> $pagina @param array<string, mixed> $filtros */
    private function cargarReporte(array &$pagina, string $tipo, User $usuario, ServicioAutorizacion $autorizacion, array $filtros): void
    {
        $this->cargarReportePorTipo($pagina, (string) ($filtros['reporte'] ?? 'ventas'), $usuario, $autorizacion, $filtros);

    }

    /** @param array<string, mixed> $pagina @param array<string, mixed> $filtros */
    private function cargarReportePorTipo(array &$pagina, string $reporte, User $usuario, ServicioAutorizacion $autorizacion, array $filtros): void
    {
        $reporte = array_key_exists($reporte, ['ventas' => true, 'pedidos' => true, 'clientes' => true, 'productos' => true, 'inventario' => true, 'compras' => true, 'campanas' => true]) ? $reporte : 'ventas';
        $consulta = match ($reporte) {
            'ventas' => $autorizacion->scopeRecordsFor(Venta::query()->where('estado', 'confirmada'), $usuario, 'gestion.ventas.consultar'),
            'pedidos' => $autorizacion->scopeRecordsFor(Pedido::query()->whereNotIn('estado', ['Cancelado', 'Cancelada']), $usuario, 'gestion.pedidos.consultar'),
            'clientes' => $autorizacion->scopeRecordsFor(Cliente::query(), $usuario, 'gestion.clientes.consultar'),
            'productos' => $autorizacion->scopeRecordsFor(Producto::query(), $usuario, 'gestion.productos.consultar'),
            'inventario' => $autorizacion->scopeRecordsFor(MovimientoInventario::query()->with('product'), $usuario, 'gestion.inventario.consultar'),
            'compras' => $autorizacion->scopeRecordsFor(Compra::query()->where('activo', true)->whereNotIn('estado', ['Cancelada', 'Cancelado']), $usuario, 'gestion.compras.consultar'),
            'campanas' => $autorizacion->scopeRecordsFor(Campania::query(), $usuario, 'gestion.campanias.consultar'),
        };
        $campoFecha = in_array($reporte, ['ventas'], true) ? 'fecha_venta' : ($reporte === 'compras' ? 'fecha_compra' : 'creado_en');
        $consulta->when(isset($filtros['desde']), fn (Builder $query) => $query->whereDate($campoFecha, '>=', $filtros['desde']))
            ->when(isset($filtros['hasta']), fn (Builder $query) => $query->whereDate($campoFecha, '<=', $filtros['hasta']))
            ->when(isset($filtros['estado']) && in_array($reporte, ['ventas', 'pedidos', 'clientes', 'compras'], true), fn (Builder $query) => $query->where('estado', $filtros['estado']));
        $pagina['opcionesFiltros'] = ['estado' => (clone $consulta)->whereNotNull('estado')->distinct()->orderBy('estado')->pluck('estado', 'estado')];
        $registros = $consulta->latest($campoFecha)->latest('id')->paginate(15)->withQueryString();
        $pagina['paginacion'] = $registros;
        $pagina['tabla'] = match ($reporte) {
            'ventas' => 'Ventas confirmadas', 'pedidos' => 'Pedidos válidos', 'clientes' => 'Clientes',
            'productos' => 'Productos', 'inventario' => 'Movimientos de inventario', 'compras' => 'Compras', 'campanas' => 'Campañas',
        };
        $pagina['columnas'] = match ($reporte) {
            'ventas' => ['ID', 'Código', 'Total', 'Estado', 'Fecha'], 'pedidos' => ['ID', 'Usuario ID', 'Total', 'Estado', 'Fecha'],
            'clientes' => ['ID', 'Cliente', 'Correo', 'Tipo', 'Activo', 'Fecha'], 'productos' => ['ID', 'Producto', 'Categoría', 'Precio', 'Stock', 'Activo'],
            'inventario' => ['ID', 'Producto', 'Movimiento', 'Cantidad', 'Notas', 'Fecha'], 'compras' => ['ID', 'Proveedor ID', 'Total', 'Estado', 'Fecha'],
            'campanas' => ['ID', 'Campaña', 'Ubicación', 'Vistas', 'Clics', 'Activo', 'Fecha'],
        };
        $pagina['filas'] = $registros->getCollection()->map(static function (object $registro) use ($reporte): array {
            return match ($reporte) {
                'ventas' => [(string) $registro->id, (string) $registro->codigo, (string) $registro->total, (string) $registro->estado, (string) $registro->fecha_venta],
                'pedidos' => [(string) $registro->id, (string) $registro->usuario_id, (string) $registro->total, (string) $registro->estado, (string) $registro->creado_en],
                'clientes' => [(string) $registro->id, (string) $registro->nombre_contacto, (string) $registro->correo, (string) $registro->tipo, $registro->activo ? 'Sí' : 'No', (string) $registro->creado_en],
                'productos' => [(string) $registro->id, (string) $registro->nombre, (string) $registro->categoria, (string) $registro->precio, (string) $registro->existencias, $registro->activo ? 'Sí' : 'No'],
                'inventario' => [(string) $registro->id, (string) ($registro->product?->nombre ?? $registro->producto_id), (string) $registro->tipo_movimiento, (string) $registro->cantidad, (string) $registro->notas, (string) $registro->creado_en],
                'compras' => [(string) $registro->id, (string) $registro->proveedor_id, (string) $registro->total, (string) $registro->estado, (string) $registro->fecha_compra],
                'campanas' => [(string) $registro->id, (string) $registro->nombre, (string) $registro->ubicacion, (string) $registro->vistas, (string) $registro->clics, $registro->activo ? 'Sí' : 'No', (string) $registro->creado_en],
            };
        })->all();
        $pagina['metricas'][0][0] = 'Registros';
        $pagina['metricas'][0][1] = (string) $registros->total();
        $pagina['metricas'][1][0] = in_array($reporte, ['ventas', 'pedidos', 'compras'], true) ? 'Importe total' : 'Registros en página';
        $pagina['metricas'][1][1] = in_array($reporte, ['ventas', 'pedidos', 'compras'], true) ? number_format((float) (clone $consulta)->sum('total'), 2) : (string) $registros->count();
        $pagina['metricas'][2][1] = $reporte === 'ventas' ? (string) (clone $consulta)->whereNotNull('cliente_id')->distinct()->count('cliente_id') : '—';
        $pagina['metricas'][3][1] = $reporte === 'productos' ? (string) (clone $consulta)->where('activo', true)->count() : '—';
        $pagina['barras'] = [];
        $pagina['resumen_filas'] = [['Registros visibles', (string) $registros->count()]];
    }

    /** @param array<string, mixed> $pagina @param array<string, mixed> $filtros */
    private function cargarAuditoria(array &$pagina, string $tipo, User $usuario, ServicioAutorizacion $autorizacion, array $filtros): void
    {
        $consultaBase = $autorizacion->scopeRecordsFor(RegistroAuditoria::query(), $usuario, 'gestion.auditoria.consultar');
        $pagina['opcionesFiltros'] = [
            'usuario_id' => User::query()->whereIn('id', (clone $consultaBase)->whereNotNull('usuario_id')->distinct()->pluck('usuario_id'))->orderBy('nombre')->pluck('nombre', 'id'),
            'modulo' => (clone $consultaBase)->whereNotNull('modulo')->distinct()->orderBy('modulo')->pluck('modulo', 'modulo')->mapWithKeys(fn (string $modulo): array => [$modulo => $this->etiquetaAuditoria($modulo)]),
            'accion' => (clone $consultaBase)->distinct()->orderBy('accion')->pluck('accion', 'accion')->mapWithKeys(fn (string $accion): array => [$accion => $this->etiquetaAuditoria($accion)]),
            'rol_codigo' => (clone $consultaBase)->whereNotNull('rol_codigo')->distinct()->orderBy('rol_codigo')->pluck('rol_codigo', 'rol_codigo'),
            'resultado' => (clone $consultaBase)->whereNotNull('resultado')->distinct()->orderBy('resultado')->pluck('resultado', 'resultado'),
        ];
        $consulta = $autorizacion->scopeRecordsFor(RegistroAuditoria::query()->with('user'), $usuario, 'gestion.auditoria.consultar')
            ->when(isset($filtros['desde']), fn (Builder $q) => $q->whereDate('creado_en', '>=', $filtros['desde']))
            ->when(isset($filtros['hasta']), fn (Builder $q) => $q->whereDate('creado_en', '<=', $filtros['hasta']))
            ->when(isset($filtros['usuario_id']), fn (Builder $q) => $q->where('usuario_id', $filtros['usuario_id']))
            ->when(isset($filtros['modulo']), fn (Builder $q) => $q->where('modulo', $filtros['modulo']))
            ->when(isset($filtros['accion']), fn (Builder $q) => $q->where('accion', $filtros['accion']))
            ->when(isset($filtros['resultado']), fn (Builder $q) => $q->where('resultado', $filtros['resultado']))
            ->when(isset($filtros['buscar']), fn (Builder $q) => $q->where(function (Builder $buscar) use ($filtros): void {
                $buscar->where('accion', 'like', '%'.$filtros['buscar'].'%')
                    ->orWhere('entidad', 'like', '%'.$filtros['buscar'].'%')
                    ->orWhere('observaciones', 'like', '%'.$filtros['buscar'].'%');
            }));

        $registros = $consulta->latest('creado_en')->latest('id')->paginate(10)->withQueryString();
        $pagina['filas'] = $registros->map(fn (RegistroAuditoria $registro): array => [
            $registro->creado_en?->format('Y-m-d H:i:s') ?? '—',
            $registro->user?->nombre ?? '—',
            $this->etiquetaAuditoria($registro->accion),
            $this->etiquetaAuditoria($registro->modulo ?? $registro->entidad),
            $registro->observaciones ?? $registro->entidad,
            $registro->direccion_ip ?? '—',
            $registro->resultado ?? 'registrado',
        ])->all();
        $pagina['detalles'] = $registros->getCollection()->map(fn (RegistroAuditoria $registro): array => [
            'id' => (string) $registro->id,
            'fecha' => $registro->ocurrido_en?->format('Y-m-d H:i:s') ?? $registro->creado_en?->format('Y-m-d H:i:s') ?? '—',
            'usuario' => $registro->user?->nombre ?? 'Sistema',
            'modulo' => $this->etiquetaAuditoria($registro->modulo ?? $registro->modulo_codigo ?? $registro->entidad),
            'accion' => $this->etiquetaAuditoria($registro->accion),
            'entidad' => $registro->entidad,
            'entidad_id' => $registro->entidad_id ?? $registro->identificador_entidad,
            'resultado' => $registro->resultado ?? 'registrado',
            'direccion_ip' => $registro->direccion_ip ?? '—',
            'sede_id' => $registro->sede_id ?? '—',
            'tabla' => $registro->tabla_afectada ?? $registro->entidad,
            'observaciones' => $registro->observaciones ?? $registro->observacion ?? 'Sin observaciones registradas.',
            'origen' => $registro->origen ?? 'laravel',
        ])->values()->all();
        $pagina['metricas'][0][0] = 'Eventos hoy';
        $pagina['metricas'][0][1] = (string) (clone $consultaBase)->whereDate('creado_en', now()->toDateString())->count();
        $pagina['metricas'][1][0] = 'Operaciones exitosas';
        $pagina['metricas'][1][1] = (string) (clone $consulta)->whereIn('resultado', ['exito', 'exitoso'])->count();
        $pagina['metricas'][2][0] = 'Accesos denegados';
        $pagina['metricas'][2][1] = (string) (clone $consulta)->whereIn('resultado', ['denegado', 'fallo', 'fallido'])->count();
        $pagina['metricas'][3][0] = 'Usuarios activos';
        $pagina['metricas'][3][1] = (string) User::query()->where('activo', true)->count();
        $conteosResultado = (clone $consulta)->reorder()->selectRaw("COALESCE(resultado, 'registrado') AS resultado, COUNT(*) AS cantidad")
            ->groupBy('resultado')->pluck('cantidad', 'resultado');
        $pagina['metricas'][1][1] = (string) $conteosResultado->filter(fn ($cantidad, $resultado): bool => in_array(mb_strtolower((string) $resultado), ['exito', 'exitoso'], true))->sum();
        $pagina['metricas'][2][1] = (string) $conteosResultado->filter(fn ($cantidad, $resultado): bool => in_array(mb_strtolower((string) $resultado), ['advertencia', 'revertido'], true))->sum();
        $pagina['metricas'][3][1] = (string) $conteosResultado->filter(fn ($cantidad, $resultado): bool => in_array(mb_strtolower((string) $resultado), ['fallo', 'fallido', 'denegado'], true))->sum();
        $conteosModulo = (clone $consulta)->reorder()->selectRaw('COALESCE(modulo, entidad) AS grupo, COUNT(*) AS cantidad')
            ->groupBy('grupo')->orderByDesc('cantidad')->limit(6)->get();
        $maximo = max(1, (int) $conteosModulo->max('cantidad'));
        $pagina['barras'] = $conteosModulo->map(fn (object $fila): array => [$fila->grupo, (int) round(((int) $fila->cantidad / $maximo) * 100), (string) $fila->cantidad])->all();
        $pagina['resumen_filas'] = $conteosResultado->map(fn (mixed $cantidad, string $resultado): array => [$resultado, (string) $cantidad])->all();
        $pagina['paginacion'] = $registros;
    }

    /** @return array<string, mixed> */
    private function estructura(string $tipo): array
    {
        $titulos = [
            'globales' => ['Reportes globales', 'bi-bar-chart-line', 'Indicadores y resultados calculados desde registros reales de MariaDB.'],
            'operaciones' => ['Auditoría de operaciones', 'bi-shield-check', 'Eventos de operaciones persistidos en el registro append-only de MariaDB.'],
        ];
        [$titulo, $icono, $descripcion] = $titulos[$tipo];
        $reporte = $tipo === 'globales';
        $columnas = $reporte
            ? ['Indicador', 'Origen', 'Valor real']
            : ['Fecha y hora', 'Usuario', 'Módulo', 'Acción', 'Detalle', 'Dirección IP', 'Resultado'];
        $metricas = match ($tipo) {
            'globales' => [['Ventas registradas', '0', 'bi-cart3', 'azul'], ['Transacciones', '0', 'bi-receipt', 'verde'], ['Clientes', '0', 'bi-people', 'violeta'], ['Productos activos', '0', 'bi-box-seam', 'ambar']],
            default => [['Registros encontrados', '0', 'bi-list-check', 'azul'], ['Exitosos', '0', 'bi-check-lg', 'verde'], ['Advertencias', '0', 'bi-exclamation-triangle', 'ambar'], ['Fallidos', '0', 'bi-shield-exclamation', 'rojo']],
        };

        $acciones = match ($tipo) {
            'globales' => [['Exportar reporte', 'bi-download', 'exportar']],
            default => [],
        };
        $filtros = match ($tipo) {
            'globales' => [['Tipo de reporte', 'reporte', 'Seleccionar reporte'], ['Estado', 'estado', 'Todos los estados'], ['Desde', 'desde', 'fecha'], ['Hasta', 'hasta', 'fecha']],
            default => [['Desde', 'desde', 'fecha'], ['Hasta', 'hasta', 'fecha'], ['Usuario', 'usuario_id', 'Todos los usuarios'], ['Módulo', 'modulo', 'Todos los módulos'], ['Tipo de operación', 'accion', 'Todas las acciones'], ['Resultado', 'resultado', 'Todos los resultados']],
        };

        return [
            'titulo' => $titulo, 'icono' => $icono, 'descripcion' => $descripcion, 'acciones' => $acciones,
            'metricas' => $metricas, 'filtros' => $filtros,
            'filtrosBloqueados' => [],
            'tabla' => $titulo, 'columnas' => $columnas, 'filas' => [], 'grafico' => 'Registros por grupo',
            'barras' => [], 'resumen' => 'Resumen de registros', 'resumen_filas' => [], 'paginacion' => null,
            'opcionesFiltros' => [], 'opcionesReportes' => ['ventas' => 'Ventas confirmadas', 'pedidos' => 'Pedidos válidos', 'clientes' => 'Clientes', 'productos' => 'Productos', 'inventario' => 'Movimientos de inventario', 'compras' => 'Compras', 'campanas' => 'Campañas'],
        ];
    }

    private function etiquetaAuditoria(?string $valor): string
    {
        $valor = trim((string) $valor);
        $etiquetas = [
            'inicio_sesion' => 'Inicio de sesión',
            'inicio_sesion_fallido' => 'Inicio de sesión fallido',
            'cierre_sesion' => 'Cierre de sesión',
            'catalogo_analisis_auditoria_reducido' => 'Catálogo de auditoría reducido',
            'exito' => 'Exitoso',
            'fallo' => 'Fallido',
            'denegado' => 'Denegado',
            'seguridad' => 'Seguridad',
            'inventario' => 'Inventario',
            'ventas' => 'Ventas',
            'pedidos' => 'Pedidos',
            'reportes' => 'Reportes',
        ];

        return $etiquetas[$valor] ?? mb_convert_case(str_replace(['_', '.'], ' ', $valor), MB_CASE_TITLE, 'UTF-8');
    }

    private function vista(string $tipo): string
    {
        return 'paneles.administrador.analisis-auditoria.'.$this->directorio($tipo).'.listar';
    }

    private function directorio(string $tipo): string
    {
        return ['globales' => 'reportes-globales', 'operaciones' => 'auditoria-operaciones'][$tipo];
    }
}

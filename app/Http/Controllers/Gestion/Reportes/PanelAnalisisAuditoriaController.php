<?php

namespace App\Http\Controllers\Gestion\Reportes;

use App\Http\Controllers\Controller;
use App\Models\Auditoria\RegistroAuditoria;
use App\Models\Catalogo\Categoria;
use App\Models\Catalogo\Producto;
use App\Models\Clientes\Cliente;
use App\Models\Comercio\Pedido;
use App\Models\Comercio\Venta;
use App\Models\User;
use App\Servicios\RegistradorAuditoria;
use App\Servicios\ServicioAutorizacion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class PanelAnalisisAuditoriaController extends Controller
{
    public function globales(Request $request, ServicioAutorizacion $autorizacion): View
    {
        return $this->mostrar($request, 'globales', $autorizacion);
    }

    public function sedes(Request $request, ServicioAutorizacion $autorizacion): View
    {
        return $this->mostrar($request, 'sedes', $autorizacion);
    }

    public function operaciones(Request $request, ServicioAutorizacion $autorizacion): View
    {
        return $this->mostrar($request, 'operaciones', $autorizacion);
    }

    public function roles(Request $request, ServicioAutorizacion $autorizacion): View
    {
        return $this->mostrar($request, 'roles', $autorizacion);
    }

    public function seguridad(Request $request, ServicioAutorizacion $autorizacion): View
    {
        return $this->mostrar($request, 'seguridad', $autorizacion);
    }

    public function exportaciones(Request $request, ServicioAutorizacion $autorizacion): View
    {
        return $this->mostrar($request, 'exportaciones', $autorizacion);
    }

    public function mostrar(Request $request, string $tipo, ServicioAutorizacion $autorizacion): View
    {
        abort_unless(in_array($tipo, ['globales', 'sedes', 'operaciones', 'roles', 'seguridad', 'exportaciones'], true), 404);

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
        ]);

        $pagina = $this->estructura($tipo);
        if (in_array($tipo, ['globales', 'sedes'], true)) {
            $this->cargarReporte($pagina, $tipo, $usuario, $autorizacion, $filtros);
        } else {
            $this->cargarAuditoria($pagina, $tipo, $usuario, $autorizacion, $filtros);
        }

        return view($this->vista($tipo), [
            'pagina' => $pagina,
            'tipo' => $tipo,
            'filtros' => $filtros,
            'puedeExportar' => $autorizacion->allows($usuario, 'gestion.reportes.exportar'),
        ]);
    }

    public function exportar(Request $request, string $tipo, ServicioAutorizacion $autorizacion, RegistradorAuditoria $auditoria): StreamedResponse
    {
        abort_unless(in_array($tipo, ['globales', 'sedes', 'operaciones', 'roles', 'seguridad', 'exportaciones'], true), 404);
        $autorizacion->assertAllowed($request->user(), 'gestion.reportes.exportar');
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
        ]);

        if (in_array($tipo, ['globales', 'sedes'], true)) {
            $consulta = $autorizacion->scopeRecordsFor(Venta::query(), $request->user(), 'gestion.ventas.consultar')
                ->when(isset($filtros['desde']), fn (Builder $q) => $q->whereDate('fecha_venta', '>=', $filtros['desde']))
                ->when(isset($filtros['hasta']), fn (Builder $q) => $q->whereDate('fecha_venta', '<=', $filtros['hasta']))
                ->when(isset($filtros['sede']), fn (Builder $q) => $q->where('sede_id', $filtros['sede']))
                ->when(isset($filtros['estado']), fn (Builder $q) => $q->where('estado', $filtros['estado']))
                ->when(isset($filtros['canal']), fn (Builder $q) => $q->whereHas('pedido', fn (Builder $pedido) => $pedido->where('canal', $filtros['canal'])))
                ->select(['id', 'codigo', 'empresa_id', 'sede_id', 'total', 'estado', 'fecha_venta']);
            $columnas = ['Id', 'Código', 'Empresa', 'Sede', 'Total', 'Estado', 'Fecha'];
        } else {
            $consulta = $autorizacion->scopeRecordsFor(RegistroAuditoria::query(), $request->user(), 'gestion.auditoria.consultar')
                ->when(isset($filtros['desde']), fn (Builder $q) => $q->whereDate('creado_en', '>=', $filtros['desde']))
                ->when(isset($filtros['hasta']), fn (Builder $q) => $q->whereDate('creado_en', '<=', $filtros['hasta']))
                ->when(isset($filtros['usuario_id']), fn (Builder $q) => $q->where('usuario_id', $filtros['usuario_id']))
                ->when(isset($filtros['modulo']), fn (Builder $q) => $q->where('modulo', $filtros['modulo']))
                ->when(isset($filtros['accion']), fn (Builder $q) => $q->where('accion', $filtros['accion']))
                ->when(isset($filtros['resultado']), fn (Builder $q) => $q->where('resultado', $filtros['resultado']))
                ->when(isset($filtros['rol_codigo']), fn (Builder $q) => $q->where('rol_codigo', $filtros['rol_codigo']))
                ->when($tipo === 'roles', fn (Builder $q) => $q->where(fn (Builder $filtro) => $filtro->where('entidad', 'like', '%rol%')->orWhere('entidad', 'like', '%permiso%')))
                ->when($tipo === 'seguridad', fn (Builder $q) => $q->where(fn (Builder $filtro) => $filtro->where('modulo', 'like', '%seguridad%')->orWhere('accion', 'like', '%login%')->orWhere('accion', 'like', '%acceso%')))
                ->when($tipo === 'exportaciones', fn (Builder $q) => $q->where('accion', 'like', '%export%'))
                ->select(['id', 'usuario_id', 'modulo', 'accion', 'entidad', 'entidad_id', 'resultado', 'direccion_ip', 'creado_en']);
            $columnas = ['Id', 'Usuario ID', 'Módulo', 'Acción', 'Entidad', 'Entidad ID', 'Resultado', 'Dirección IP', 'Fecha'];
        }

        $auditoria->record((int) $request->user()->getKey(), 'exportar', 'registros_auditoria', null, null, [
            'tipo' => $tipo,
            'formato' => 'csv',
            'desde' => $filtros['desde'] ?? null,
            'hasta' => $filtros['hasta'] ?? null,
            'sede_id' => $filtros['sede'] ?? null,
        ], $request->ip());

        return response()->streamDownload(function () use ($consulta, $columnas): void {
            $salida = fopen('php://output', 'w');
            fwrite($salida, "\xEF\xBB\xBF");
            fputcsv($salida, $columnas);
            foreach ($consulta->cursor() as $registro) {
                fputcsv($salida, array_values($registro->getAttributes()));
            }
            fclose($salida);
        }, 'analisis-auditoria-'.$tipo.'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** @param array<string, mixed> $pagina @param array<string, mixed> $filtros */
    private function cargarReporte(array &$pagina, string $tipo, User $usuario, ServicioAutorizacion $autorizacion, array $filtros): void
    {
        $consultaVentas = $autorizacion->scopeRecordsFor(Venta::query(), $usuario, 'gestion.ventas.consultar')
            ->when(isset($filtros['desde']), fn (Builder $consulta) => $consulta->whereDate('fecha_venta', '>=', $filtros['desde']))
            ->when(isset($filtros['hasta']), fn (Builder $consulta) => $consulta->whereDate('fecha_venta', '<=', $filtros['hasta']))
            ->when(isset($filtros['sede']), fn (Builder $consulta) => $consulta->where('sede_id', $filtros['sede']))
            ->when(isset($filtros['estado']), fn (Builder $consulta) => $consulta->where('estado', $filtros['estado']))
            ->when(isset($filtros['canal']), fn (Builder $consulta) => $consulta->whereHas('pedido', fn (Builder $pedido) => $pedido->where('canal', $filtros['canal'])));

        if ($tipo === 'sedes') {
            $consultaBase = $autorizacion->scopeRecordsFor(Venta::query(), $usuario, 'gestion.ventas.consultar');
            $pagina['opcionesFiltros'] = [
                'sede' => (clone $consultaBase)->join('sedes', 'sedes.id', '=', 'ventas.sede_id')->select('sedes.id', 'sedes.nombre')->distinct()->orderBy('sedes.nombre')->pluck('sedes.nombre', 'sedes.id'),
                'canal' => Pedido::query()->whereIn('id', (clone $consultaBase)->whereNotNull('pedido_id')->distinct()->pluck('pedido_id'))->whereNotNull('canal')->distinct()->orderBy('canal')->pluck('canal', 'canal'),
            ];
            $resultados = (clone $consultaVentas)->join('sedes', 'sedes.id', '=', 'ventas.sede_id')
                ->select('sedes.id', 'sedes.nombre')->selectRaw('COUNT(ventas.id) AS cantidad')
                ->selectRaw('COALESCE(SUM(ventas.total), 0) AS total')
                ->groupBy('sedes.id', 'sedes.nombre')->orderBy('sedes.nombre')->paginate(15)->withQueryString();
            $pagina['filas'] = $resultados->map(fn (object $fila): array => [$fila->nombre, number_format((float) $fila->total, 2), (string) $fila->cantidad])->all();
            $pagina['metricas'][0][1] = (string) $resultados->total();
            $pagina['metricas'][1][1] = number_format((float) (clone $consultaVentas)->sum('total'), 2);
            $pagina['metricas'][2][1] = (string) (clone $consultaVentas)->count();
            $pagina['metricas'][3][1] = (string) (clone $consultaVentas)->whereNotNull('cliente_id')->distinct('cliente_id')->count('cliente_id');
            $maximo = max(1, (float) $resultados->getCollection()->max('total'));
            $pagina['barras'] = $resultados->getCollection()->map(fn (object $fila): array => [$fila->nombre, (int) round(((float) $fila->total / $maximo) * 100), number_format((float) $fila->total, 2)])->all();
            $pagina['resumen_filas'] = $resultados->getCollection()->map(fn (object $fila): array => [$fila->nombre, number_format((float) $fila->total, 2)])->all();
            $pagina['paginacion'] = $resultados;

            return;
        }

        $pagina['opcionesFiltros'] = [
            'estado' => (clone $autorizacion->scopeRecordsFor(Venta::query(), $usuario, 'gestion.ventas.consultar'))->distinct()->orderBy('estado')->pluck('estado', 'estado'),
            'canal' => Pedido::query()->whereIn('id', (clone $autorizacion->scopeRecordsFor(Venta::query(), $usuario, 'gestion.ventas.consultar'))->whereNotNull('pedido_id')->distinct()->pluck('pedido_id'))->whereNotNull('canal')->distinct()->orderBy('canal')->pluck('canal', 'canal'),
        ];
        $pagina['metricas'][0][1] = number_format((float) (clone $consultaVentas)->sum('total'), 2);
        $pagina['metricas'][1][1] = (string) (clone $consultaVentas)->count();
        $pagina['metricas'][2][1] = (string) $autorizacion->scopeRecordsFor(Cliente::query(), $usuario, 'gestion.clientes.consultar')->count();
        $pagina['metricas'][3][1] = (string) $autorizacion->scopeRecordsFor(Producto::query(), $usuario, 'gestion.productos.consultar')->where('activo', true)->count();
        $pagina['filas'] = [
            ['Ventas registradas', 'Ventas', (string) $pagina['metricas'][0][1]],
            ['Ventas físicas', 'Ventas', (string) (clone $consultaVentas)->whereNull('pedido_id')->count()],
            ['Clientes registrados', 'Clientes', (string) $pagina['metricas'][2][1]],
            ['Productos activos', 'Catálogo', (string) $pagina['metricas'][3][1]],
            ['Categorías registradas', 'Catálogo', (string) $autorizacion->scopeRecordsFor(Categoria::query(), $usuario, 'gestion.categorias.consultar')->count()],
        ];
        $valoresGrafica = array_map(static fn (array $fila): float => (float) str_replace(',', '', (string) $fila[2]), $pagina['filas']);
        $maximo = max(1, ...$valoresGrafica);
        $pagina['barras'] = array_map(static fn (array $fila): array => [$fila[0], (int) round(((float) str_replace(',', '', (string) $fila[2]) / $maximo) * 100), $fila[2]], $pagina['filas']);
        $pagina['resumen_filas'] = array_map(static fn (array $fila): array => [$fila[0], $fila[2]], $pagina['filas']);
    }

    /** @param array<string, mixed> $pagina @param array<string, mixed> $filtros */
    private function cargarAuditoria(array &$pagina, string $tipo, User $usuario, ServicioAutorizacion $autorizacion, array $filtros): void
    {
        $consultaBase = $autorizacion->scopeRecordsFor(RegistroAuditoria::query(), $usuario, 'gestion.auditoria.consultar');
        $pagina['opcionesFiltros'] = [
            'usuario_id' => User::query()->whereIn('id', (clone $consultaBase)->whereNotNull('usuario_id')->distinct()->pluck('usuario_id'))->orderBy('nombre')->pluck('nombre', 'id'),
            'modulo' => (clone $consultaBase)->whereNotNull('modulo')->distinct()->orderBy('modulo')->pluck('modulo', 'modulo'),
            'accion' => (clone $consultaBase)->distinct()->orderBy('accion')->pluck('accion', 'accion'),
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
            ->when(isset($filtros['rol_codigo']), fn (Builder $q) => $q->where('rol_codigo', $filtros['rol_codigo']));

        if ($tipo === 'roles') {
            $consulta->where(function (Builder $q): void {
                $q->where('entidad', 'like', '%rol%')->orWhere('entidad', 'like', '%permiso%')->orWhere('modulo', 'like', '%seguridad%');
            });
        } elseif ($tipo === 'seguridad') {
            $consulta->where(function (Builder $q): void {
                $q->where('modulo', 'like', '%seguridad%')->orWhere('accion', 'like', '%login%')->orWhere('accion', 'like', '%acceso%');
            });
        } elseif ($tipo === 'exportaciones') {
            $consulta->where('accion', 'like', '%export%');
        }

        $registros = $consulta->latest('creado_en')->latest('id')->paginate(15)->withQueryString();
        $pagina['filas'] = $registros->map(fn (RegistroAuditoria $registro): array => [
            $registro->creado_en?->format('Y-m-d H:i:s') ?? '—',
            $registro->user?->nombre ?? '—',
            (string) ($registro->modulo ?? $registro->entidad),
            $registro->accion,
            $registro->observaciones ?? $registro->entidad,
            $registro->direccion_ip ?? '—',
            $registro->resultado ?? 'registrado',
        ])->all();
        $pagina['metricas'][0][1] = (string) $registros->total();
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
            'globales' => ['Reportes globales', 'bi-bar-chart-line', 'Indicadores calculados desde ventas, clientes, productos y categorías de MariaDB.'],
            'sedes' => ['Reportes por sede', 'bi-geo-alt', 'Ventas agregadas por sedes autorizadas.'],
            'operaciones' => ['Auditoría de operaciones', 'bi-shield-check', 'Registros de auditoría persistidos en MariaDB.'],
            'roles' => ['Historial de roles y permisos', 'bi-clock-history', 'Eventos de roles y permisos registrados en auditoría.'],
            'seguridad' => ['Eventos de seguridad', 'bi-shield-exclamation', 'Eventos de seguridad disponibles en el registro de auditoría.'],
            'exportaciones' => ['Exportaciones autorizadas', 'bi-download', 'Eventos de exportación que cuentan con registro de auditoría.'],
        ];
        [$titulo, $icono, $descripcion] = $titulos[$tipo];
        $reporte = in_array($tipo, ['globales', 'sedes'], true);
        $columnas = $reporte
            ? ($tipo === 'sedes' ? ['Sede', 'Ventas registradas', 'Cantidad'] : ['Indicador', 'Origen', 'Valor real'])
            : ['Fecha y hora', 'Usuario', 'Módulo', 'Acción', 'Detalle', 'Dirección IP', 'Resultado'];
        $metricas = match ($tipo) {
            'globales' => [['Ventas registradas', '0', 'bi-cart3', 'azul'], ['Transacciones', '0', 'bi-receipt', 'verde'], ['Clientes', '0', 'bi-people', 'violeta'], ['Productos activos', '0', 'bi-box-seam', 'ambar']],
            'sedes' => [['Sedes con ventas', '0', 'bi-building', 'azul'], ['Importe de ventas', '0', 'bi-currency-exchange', 'verde'], ['Ventas', '0', 'bi-receipt', 'violeta'], ['Clientes atendidos', '0', 'bi-people', 'ambar']],
            default => [['Registros encontrados', '0', 'bi-list-check', 'azul'], ['Exitosos', '0', 'bi-check-lg', 'verde'], ['Advertencias', '0', 'bi-exclamation-triangle', 'ambar'], ['Fallidos', '0', 'bi-shield-exclamation', 'rojo']],
        };

        $acciones = match ($tipo) {
            'globales' => [['Exportar', 'bi-download', 'exportar'], ['Generar reporte', 'bi-bar-chart', 'generar']],
            'sedes' => [['Actualizar', 'bi-arrow-clockwise', 'actualizar'], ['Exportar', 'bi-download', 'exportar'], ['Generar reporte', 'bi-bar-chart', 'generar']],
            'operaciones' => [['Exportar registros', 'bi-download', 'exportar'], ['Consultar historial', 'bi-clock-history', 'historial']],
            'roles' => [['Comparar cambios', 'bi-clock-history', 'comparar'], ['Exportar historial', 'bi-download', 'exportar']],
            'seguridad' => [['Configurar alertas', 'bi-bell', 'alertas'], ['Exportar eventos', 'bi-download', 'exportar']],
            default => [['Exportar historial', 'bi-file-earmark-arrow-down', 'exportar'], ['Solicitar exportación', 'bi-plus-lg', 'solicitar']],
        };
        $filtros = match ($tipo) {
            'globales' => [['Canal', 'canal', 'Todos los canales'], ['Estado', 'estado', 'Todos los estados'], ['Desde', 'desde', 'fecha'], ['Hasta', 'hasta', 'fecha']],
            'sedes' => [['Sede', 'sede', 'Todas las sedes autorizadas'], ['Canal', 'canal', 'Todos los canales'], ['Desde', 'desde', 'fecha'], ['Hasta', 'hasta', 'fecha']],
            'operaciones' => [['Usuario', 'usuario_id', 'Todos los usuarios'], ['Módulo', 'modulo', 'Todos los módulos'], ['Acción', 'accion', 'Todas las acciones'], ['Resultado', 'resultado', 'Todos los resultados']],
            'roles' => [['Usuario responsable', 'usuario_id', 'Todos los usuarios'], ['Rol afectado', 'rol_codigo', 'Todos los roles'], ['Tipo de cambio', 'accion', 'Todos los cambios'], ['Desde', 'desde', 'fecha']],
            'seguridad' => [['Tipo de evento', 'accion', 'Todos los eventos'], ['Resultado', 'resultado', 'Todos los resultados'], ['Usuario', 'usuario_id', 'Todos los usuarios'], ['Desde', 'desde', 'fecha']],
            default => [['Módulo', 'modulo', 'Todos los módulos'], ['Solicitante', 'usuario_id', 'Todos los usuarios'], ['Acción', 'accion', 'Todas las acciones'], ['Resultado', 'resultado', 'Todos los resultados']],
        };

        return [
            'titulo' => $titulo, 'icono' => $icono, 'descripcion' => $descripcion, 'acciones' => $acciones,
            'metricas' => $metricas, 'filtros' => $filtros,
            'filtrosBloqueados' => [],
            'tabla' => $titulo, 'columnas' => $columnas, 'filas' => [], 'grafico' => 'Registros por grupo',
            'barras' => [], 'resumen' => 'Resumen de registros', 'resumen_filas' => [], 'paginacion' => null,
            'opcionesFiltros' => [],
        ];
    }

    private function vista(string $tipo): string
    {
        return 'paneles.administrador.analisis-auditoria.'.$this->directorio($tipo).'.listar';
    }

    private function directorio(string $tipo): string
    {
        return ['globales' => 'reportes-globales', 'sedes' => 'reportes-sede', 'operaciones' => 'auditoria-operaciones', 'roles' => 'historial-roles', 'seguridad' => 'eventos-seguridad', 'exportaciones' => 'exportaciones'][$tipo];
    }
}

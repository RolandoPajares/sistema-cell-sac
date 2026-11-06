<?php

namespace App\Servicios;

use Illuminate\Support\Facades\DB;

/**
 * Construye las plantillas recomendadas a partir del catálogo RBAC vigente.
 *
 * Las plantillas solo sirven como propuesta para la interfaz. La autorización
 * efectiva continúa dependiendo de las asignaciones persistidas en la base de datos.
 */
final class ServicioPlantillasRbac
{
    /**
     * Permisos de módulo recomendados para cada rol del catálogo inicial.
     *
     * @var array<string, array<string, array<int, string>>>
     */
    private const ROLE_MODULES = [
        'compras_logistica' => [
            'inventario' => ['consultar', 'ajustar', 'transferir'],
            'productos' => ['consultar'],
            'proveedores' => ['consultar', 'crear', 'actualizar'],
            'compras' => ['consultar', 'crear', 'aprobar'],
            'reportes' => ['consultar', 'exportar'],
        ],
        'ventas_mayoristas' => [
            'productos' => ['consultar'],
            'catalogo' => ['consultar'],
            'cotizaciones' => ['consultar', 'crear', 'aprobar', 'exportar'],
            'pedidos' => ['consultar', 'actualizar'],
            'clientes' => ['consultar'],
        ],
        'ventas_minoristas' => [
            'productos' => ['consultar'],
            'categorias' => ['consultar'],
            'inventario' => ['consultar'],
            'ventas' => ['consultar', 'crear', 'actualizar', 'anular'],
            'pedidos' => ['consultar', 'actualizar'],
            'clientes' => ['consultar'],
            'reportes' => ['consultar', 'exportar'],
        ],
        'marketing' => [
            'campanias' => ['consultar', 'crear', 'actualizar', 'eliminar', 'configurar'],
            'cupones' => ['consultar', 'crear', 'actualizar', 'eliminar', 'exportar'],
            'segmentos' => ['consultar', 'crear', 'actualizar', 'eliminar'],
            'reportes' => ['consultar', 'exportar'],
            'analitica' => ['consultar', 'exportar'],
        ],
        'cliente_mayorista' => [
            'catalogo' => ['consultar'],
            'cotizaciones' => ['consultar', 'crear'],
            'pedidos' => ['consultar'],
        ],
        'cliente_minorista' => [
            'catalogo' => ['consultar'],
            'pedidos' => ['consultar'],
        ],
    ];

    /**
     * Pantallas de navegación recomendadas para cada rol no administrativo.
     *
     * @var array<string, array<int, string>>
     */
    private const ROLE_SCREENS = [
        'compras_logistica' => ['admin.inventory', 'admin.stock_movements', 'admin.suppliers', 'admin.purchases', 'admin.products'],
        'ventas_mayoristas' => ['admin.public_catalog', 'account.quotations', 'admin.online_orders', 'admin.customers'],
        'ventas_minoristas' => ['admin.products', 'admin.categories', 'admin.inventory', 'admin.retail_sales', 'admin.online_orders', 'admin.customers'],
        'marketing' => ['admin.campaigns', 'admin.promotions', 'admin.segments', 'admin.reports'],
        'cliente_mayorista' => ['admin.public_catalog', 'account.quotations', 'account.orders'],
        'cliente_minorista' => ['admin.public_catalog', 'account.orders'],
    ];

    /**
     * Devuelve los identificadores de la propuesta recomendada para un rol.
     *
     * La consulta utiliza únicamente permisos activos registrados en MariaDB;
     * por ello una operación inexistente nunca se convierte en una casilla.
     *
     * @param  string|null  $roleCode  Código interno del rol seleccionado.
     * @return array<int, int> Identificadores de permisos recomendados.
     */
    public function permissionIdsFor(?string $roleCode): array
    {
        if ($roleCode === 'administrador') {
            return DB::table('permisos')->where('activo', true)->pluck('id')->map(static fn (mixed $id): int => (int) $id)->all();
        }

        $codes = [];
        foreach (self::ROLE_MODULES[$roleCode] ?? [] as $module => $actions) {
            foreach ($actions as $action) {
                $codes[] = 'gestion.'.$module.'.'.$action;
            }
        }

        foreach (self::ROLE_SCREENS[$roleCode] ?? [] as $screen) {
            $codes[] = 'pantalla.'.$screen.'.consultar';
        }

        if ($codes === []) {
            return [];
        }

        return DB::table('permisos')
            ->where('activo', true)
            ->whereIn('codigo', array_values(array_unique($codes)))
            ->pluck('id')
            ->map(static fn (mixed $id): int => (int) $id)
            ->values()
            ->all();
    }
}

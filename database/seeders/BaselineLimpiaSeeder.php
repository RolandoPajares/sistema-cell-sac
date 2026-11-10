<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

/**
 * Registra el manifiesto de la línea base sin ejecutar migraciones históricas.
 */
class BaselineLimpiaSeeder extends Seeder
{
    /** @var list<string> */
    private const MIGRACIONES_CUBIERTAS = [
        '0001_01_01_000000_create_users_table',
        '0001_01_01_000001_create_cache_table',
        '0001_01_01_000002_create_jobs_table',
        '2026_10_10_171500_create_cell_sac_commerce_tables',
        '2026_10_10_172618_migrate_legacy_users_to_laravel_users',
        '2026_10_28_203424_create_devoluciones_garantias_table',
        '2026_11_01_000753_create_enterprise_rbac_tables',
        '2026_11_01_000754_add_admin_profile_security_fields',
        '2026_11_01_014019_add_business_operation_site_scope_columns',
        '2026_11_01_015103_reconcile_devoluciones_garantias_foreign_key_types',
        '2026_11_01_015104_create_ecommerce_extension_tables',
        '2026_11_01_015105_create_stock_sales_device_tables',
        '2026_11_01_015106_create_electronic_billing_tables',
        '2026_11_01_015107_create_finance_accounting_tables',
        '2026_11_01_015108_create_logistics_returns_tables',
        '2026_11_01_015109_create_support_legal_integration_tables',
        '2026_11_01_015110_add_commerce_integration_columns',
        '2026_11_01_015111_backfill_main_organization_and_stock',
        '2026_11_01_015112_link_commerce_enterprise_foreign_keys',
        '2026_11_01_015113_standardize_audit_events',
        '2026_11_03_192348_agregar_canal_y_observaciones_a_ventas',
        '2026_11_03_220504_add_cantidad_devuelta_to_detalles_transferencias_existencias',
        '2026_11_05_120000_enable_admin_responsibles_screen',
        '2026_11_05_120100_create_responsables_organizacionales_table',
        '2026_11_05_120200_enable_configuration_screens',
        '2026_11_05_120300_enable_billing_query_screen',
        '2026_11_05_120400_enable_inventory_read_screens',
        '2026_11_05_120500_enable_security_sessions_screen',
        '2026_11_05_121000_enable_refunds_export_permission',
        '2026_11_05_121100_enable_support_export_permission',
        '2026_11_05_121200_unificar_menu_operaciones_comerciales',
        '2026_11_05_130000_reduce_security_catalog_to_six_interfaces',
        '2026_11_05_140000_add_tipo_documento_to_clientes',
        '2026_11_05_140000_remove_organization_submenus',
        '2026_11_05_235900_reduce_analysis_audit_menu_to_two_interfaces',
        '2026_11_06_100000_create_product_reviews_favorites_tables',
        '2026_11_06_100100_normalize_catalog_table_names',
        '2026_11_10_144352_create_dynamic_profile_fields_tables',
        '2026_11_10_150656_add_guest_checkout_fields_to_pedidos_table',
    ];

    public function run(): void
    {
        if (DB::connection()->getDatabaseName() !== 'cell_sac_limpia') {
            throw new RuntimeException('El baseline solo puede registrarse en cell_sac_limpia.');
        }

        foreach (['migrations', 'empresas', 'usuarios', 'ventas', 'compras', 'productos', 'pedidos'] as $table) {
            if (! Schema::hasTable($table)) {
                throw new RuntimeException('Falta la tabla requerida: '.$table.'.');
            }
        }

        if (DB::table('migrations')->exists()) {
            throw new RuntimeException('El repositorio migrations ya contiene registros; no se modifica.');
        }

        foreach (['empresas', 'usuarios', 'ventas', 'compras', 'productos', 'pedidos'] as $table) {
            if (DB::table($table)->exists()) {
                throw new RuntimeException('La base destino no está vacía: '.$table.'.');
            }
        }

        DB::transaction(function (): void {
            DB::table('migrations')->insert(array_map(
                static fn (string $migration): array => ['migration' => $migration, 'batch' => 0],
                self::MIGRACIONES_CUBIERTAS,
            ));
        });
    }
}

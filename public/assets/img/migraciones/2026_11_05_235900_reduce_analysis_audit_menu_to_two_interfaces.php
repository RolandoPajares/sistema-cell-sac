<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Ejecuta la operación up definida por la clase 2026_11_05_235900_reduce_analysis_audit_menu_to_two_interfaces.
     *
     * @return void Resultado de la operación.
     */
    public function up(): void
    {
        if (! Schema::hasTable('elementos_menu')) {
            return;
        }

        $grupoId = DB::table('elementos_menu')
            ->where('codigo', 'grupo.analisis')
            ->whereNull('padre_id')
            ->value('id');

        if ($grupoId === null) {
            return;
        }

        DB::transaction(function () use ($grupoId): void {
            $descartados = ['admin.site_reports', 'admin.role_history', 'admin.security_events', 'admin.exports'];
            DB::table('elementos_menu')
                ->where('padre_id', $grupoId)
                ->whereIn('codigo', $descartados)
                ->update(['habilitado' => false, 'actualizado_en' => now()]);

            if (Schema::hasTable('pantallas')) {
                DB::table('pantallas')
                    ->whereIn('codigo', $descartados)
                    ->update(['habilitada' => false, 'actualizado_en' => now()]);
            }

            if (Schema::hasTable('registros_auditoria')
                && ! DB::table('registros_auditoria')->where('accion', 'catalogo_analisis_auditoria_reducido')->exists()) {
                DB::table('registros_auditoria')->insert([
                    'accion' => 'catalogo_analisis_auditoria_reducido',
                    'entidad' => 'elementos_menu',
                    'valores_nuevos' => json_encode([
                        'grupo' => 'grupo.analisis',
                        'habilitadas' => ['admin.reports', 'admin.audit'],
                        'deshabilitadas' => $descartados,
                    ], JSON_UNESCAPED_UNICODE),
                    'identificador_universal_evento' => (string) Str::uuid(),
                    'modulo' => 'seguridad',
                    'resultado' => 'exito',
                    'observaciones' => 'Se conservaron únicamente Reportes globales y Auditoría de operaciones.',
                    'ocurrido_en' => now(),
                ]);
            }
        });
    }

    /**
     * Ejecuta la operación down definida por la clase 2026_11_05_235900_reduce_analysis_audit_menu_to_two_interfaces.
     *
     * @return void Resultado de la operación.
     */
    public function down(): void
    {
        throw new LogicException('This menu reduction is forward-only to preserve the approved module simplification.');
    }
};

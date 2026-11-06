<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Mantiene los datos operativos, pero elimina del catálogo RBAC las
     * entradas de menú que ya no forman parte de Organización y sedes.
     */
    public function up(): void
    {
        if (! Schema::hasTable('elementos_menu')) {
            return;
        }

        $screenCodes = ['admin.sites', 'admin.warehouses', 'admin.staff', 'admin.responsibles'];

        DB::transaction(function () use ($screenCodes): void {
            $screenIds = DB::table('pantallas')
                ->whereIn('codigo', $screenCodes)
                ->pluck('id');

            DB::table('elementos_menu')
                ->whereIn('codigo', $screenCodes)
                ->delete();

            if ($screenIds->isNotEmpty()) {
                DB::table('pantallas')->whereIn('id', $screenIds)->update([
                    'ruta' => null,
                    'habilitada' => false,
                    'actualizado_en' => now(),
                ]);
            }

            if (Schema::hasTable('permisos')) {
                DB::table('permisos')
                    ->whereIn('pantalla_id', $screenIds)
                    ->update(['activo' => false, 'actualizado_en' => now()]);
            }
        });
    }

    public function down(): void
    {
        throw new LogicException('This RBAC menu cleanup is forward-only.');
    }
};

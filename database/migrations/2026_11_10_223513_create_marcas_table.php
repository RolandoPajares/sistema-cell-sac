<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('marcas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->string('nombre', 120);
            $table->string('descripcion', 500)->nullable();
            $table->string('logo_path', 500)->nullable();
            $table->boolean('activo')->default(true);
            $table->dateTime('creado_en', 6)->useCurrent();
            $table->dateTime('actualizado_en', 6)->useCurrent()->useCurrentOnUpdate();
            $table->unique(['empresa_id', 'nombre'], 'un_marcas_empresa_nombre');
            $table->index(['empresa_id', 'activo'], 'ind_marcas_empresa_activo');
            $table->foreign('empresa_id', 'cf_marcas_empresa')->references('id')->on('empresas');
        });

        $moduleId = DB::table('modulos')->where('codigo', 'productos')->value('id');
        $roleId = DB::table('roles')->where('codigo', 'administrador')->value('id');
        $menuId = DB::table('menus')->where('codigo', 'administrador.sidebar')->value('id');

        if ($moduleId !== null && $roleId !== null && $menuId !== null) {
            DB::table('pantallas')->insertOrIgnore([
                'codigo' => 'admin.brands', 'modulo_id' => $moduleId, 'nombre' => 'Marcas',
                'ruta' => 'management.brands', 'icono' => 'bi-tags', 'habilitada' => true,
                'actualizado_en' => now(),
            ]);
            $screenId = DB::table('pantallas')->where('codigo', 'admin.brands')->value('id');
            DB::table('permisos')->insertOrIgnore([
                'codigo' => 'pantalla.admin.brands.consultar', 'modulo_id' => $moduleId,
                'pantalla_id' => $screenId, 'accion' => 'consultar', 'nombre' => 'Consultar - Marcas',
                'es_sistema' => true, 'activo' => true, 'actualizado_en' => now(),
            ]);
            $permissionId = DB::table('permisos')->where('codigo', 'pantalla.admin.brands.consultar')->value('id');
            DB::table('roles_permisos')->insertOrIgnore([
                'rol_id' => $roleId, 'permiso_id' => $permissionId, 'alcance' => 'empresa', 'creado_en' => now(),
            ]);
            $parentId = DB::table('elementos_menu')->where('menu_id', $menuId)->where('codigo', 'grupo.inventario')->value('id');
            DB::table('elementos_menu')->insertOrIgnore([
                'menu_id' => $menuId, 'padre_id' => $parentId, 'pantalla_id' => $screenId,
                'permiso_id' => $permissionId, 'codigo' => 'admin.brands', 'etiqueta' => 'Marcas',
                'icono' => 'bi-tags', 'tipo' => 'pantalla', 'orden' => 3, 'habilitado' => true,
                'actualizado_en' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marcas');
    }
};

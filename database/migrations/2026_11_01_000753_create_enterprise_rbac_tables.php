<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create the organization and dynamic access-control schema without changing legacy business tables.
     */
    public function up(): void
    {
        $tables = [
            'empresas', 'sedes', 'almacenes', 'modulos', 'pantallas', 'roles', 'menus',
            'permisos', 'rol_permiso', 'roles_menus', 'elementos_menu', 'usuario_roles',
            'usuario_sede_roles',
        ];

        $existingTables = array_values(array_filter($tables, static fn (string $table): bool => Schema::hasTable($table)));

        if ($existingTables !== []) {
            throw new RuntimeException(
                'The RBAC migration stopped because some target tables already exist: '.implode(', ', $existingTables).
                '. Inspect and reconcile the schema before applying this migration.'
            );
        }

        Schema::create('empresas', function (Blueprint $table): void {
            $table->id();
            $table->string('codigo', 30)->unique();
            $table->string('razon_social', 180);
            $table->string('nombre_comercial', 180)->nullable();
            $table->char('ruc', 11)->nullable()->unique();
            $table->char('moneda_base', 3)->default('PEN');
            $table->boolean('activo')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('sedes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->restrictOnDelete();
            $table->string('codigo', 30);
            $table->string('nombre', 140);
            $table->string('direccion')->nullable();
            $table->string('telefono', 30)->nullable();
            $table->string('zona_horaria', 64)->default('America/Lima');
            $table->boolean('activa')->default(true);
            $table->timestamps();
            $table->unique(['empresa_id', 'codigo']);
            $table->index(['empresa_id', 'activa']);
        });

        Schema::create('almacenes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sede_id')->constrained('sedes')->restrictOnDelete();
            $table->string('codigo', 30);
            $table->string('nombre', 140);
            $table->string('tipo', 30)->default('general');
            $table->boolean('permite_venta')->default(true);
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->unique(['sede_id', 'codigo']);
            $table->index(['sede_id', 'activo']);
        });

        Schema::create('modulos', function (Blueprint $table): void {
            $table->id();
            $table->string('codigo', 80)->unique();
            $table->string('nombre', 140);
            $table->string('descripcion', 255)->nullable();
            $table->string('icono', 100)->nullable();
            $table->unsignedInteger('orden')->default(0);
            $table->boolean('habilitado')->default(true);
            $table->timestamps();
            $table->index(['habilitado', 'orden']);
        });

        Schema::create('pantallas', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('modulo_id')->constrained('modulos')->restrictOnDelete();
            $table->string('codigo', 120)->unique();
            $table->string('nombre', 160);
            $table->string('ruta', 160)->nullable();
            $table->string('icono', 100)->nullable();
            $table->boolean('habilitada')->default(true);
            $table->timestamps();
            $table->index(['modulo_id', 'habilitada']);
        });

        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('codigo', 40)->unique();
            $table->string('nombre', 120);
            $table->string('descripcion', 255)->nullable();
            $table->string('alcance_permitido', 20)->default('sede');
            $table->boolean('es_sistema')->default(false);
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->index(['activo', 'es_sistema']);
        });

        Schema::create('menus', function (Blueprint $table): void {
            $table->id();
            $table->string('codigo', 80)->unique();
            $table->string('nombre', 140);
            $table->string('contexto', 40)->default('sidebar');
            $table->boolean('habilitado')->default(true);
            $table->timestamps();
            $table->index(['contexto', 'habilitado']);
        });

        Schema::create('permisos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('modulo_id')->constrained('modulos')->restrictOnDelete();
            $table->foreignId('pantalla_id')->nullable()->constrained('pantallas')->nullOnDelete();
            $table->string('codigo', 160)->unique();
            $table->string('accion', 60);
            $table->string('nombre', 160);
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->index(['modulo_id', 'accion', 'activo']);
            $table->index('pantalla_id');
        });

        Schema::create('rol_permiso', function (Blueprint $table): void {
            $table->foreignId('rol_id')->constrained('roles')->restrictOnDelete();
            $table->foreignId('permiso_id')->constrained('permisos')->restrictOnDelete();
            $table->string('alcance', 20)->default('sede');
            $table->timestamp('created_at')->useCurrent();
            $table->primary(['rol_id', 'permiso_id', 'alcance']);
            $table->index(['permiso_id', 'rol_id']);
        });

        Schema::create('roles_menus', function (Blueprint $table): void {
            $table->foreignId('rol_id')->constrained('roles')->restrictOnDelete();
            $table->foreignId('menu_id')->constrained('menus')->restrictOnDelete();
            $table->timestamp('created_at')->useCurrent();
            $table->primary(['rol_id', 'menu_id']);
            $table->index(['menu_id', 'rol_id']);
        });

        Schema::create('elementos_menu', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('menu_id')->constrained('menus')->restrictOnDelete();
            $table->foreignId('padre_id')->nullable()->constrained('elementos_menu')->restrictOnDelete();
            $table->foreignId('pantalla_id')->nullable()->constrained('pantallas')->restrictOnDelete();
            $table->foreignId('permiso_id')->nullable()->constrained('permisos')->restrictOnDelete();
            $table->string('codigo', 100);
            $table->string('etiqueta', 160);
            $table->string('icono', 100)->nullable();
            $table->string('tipo', 20)->default('pantalla');
            $table->unsignedInteger('orden')->default(0);
            $table->boolean('habilitado')->default(true);
            $table->timestamps();
            $table->unique(['menu_id', 'codigo']);
            $table->index(['menu_id', 'padre_id', 'habilitado', 'orden']);
            $table->index('pantalla_id');
            $table->index('permiso_id');
        });

        Schema::create('usuario_roles', function (Blueprint $table): void {
            $table->foreignId('usuario_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('rol_id')->constrained('roles')->restrictOnDelete();
            $table->foreignId('asignado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('asignado_en')->useCurrent();
            $table->timestamp('vigente_hasta')->nullable();
            $table->boolean('activo')->default(true);
            $table->primary(['usuario_id', 'rol_id']);
            $table->index(['rol_id', 'activo', 'usuario_id']);
        });

        Schema::create('usuario_sede_roles', function (Blueprint $table): void {
            $table->foreignId('usuario_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('sede_id')->constrained('sedes')->restrictOnDelete();
            $table->foreignId('rol_id')->constrained('roles')->restrictOnDelete();
            $table->foreignId('asignado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('asignado_en')->useCurrent();
            $table->timestamp('vigente_hasta')->nullable();
            $table->boolean('activo')->default(true);
            $table->primary(['usuario_id', 'sede_id', 'rol_id']);
            $table->index(['sede_id', 'rol_id', 'activo']);
            $table->index(['usuario_id', 'activo']);
        });
    }

    /**
     * This schema contains access grants and organization records; rollback is intentionally forward-only.
     */
    public function down(): void
    {
        throw new LogicException('This access-control migration is forward-only. Use a reviewed recovery plan instead of dropping its data.');
    }
};

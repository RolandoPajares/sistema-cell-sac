<?php

namespace Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Define la responsabilidad de la clase RefreshSpanishTestDatabase dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
trait RefreshSpanishTestDatabase
{
    use LazilyRefreshDatabase {
        migrateDatabases as migrateLaravelDatabases;
    }

    /**
     * Ejecuta la operación migrateDatabases definida por la clase RefreshSpanishTestDatabase.
     *
     * @return mixed Resultado de la operación.
     */
    protected function migrateDatabases()
    {
        $this->migrateLaravelDatabases();

        if (DB::getDriverName() !== 'sqlite') {
            return;
        }

        if (! Schema::hasTable('modulos')) {
            Schema::create('modulos', function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->string('codigo', 80)->unique();
                $table->string('nombre', 140);
                $table->string('descripcion', 255)->nullable();
                $table->string('icono', 100)->nullable();
                $table->unsignedInteger('orden')->default(0);
                $table->boolean('habilitado')->default(true);
                $table->timestamp('creado_en')->nullable();
                $table->timestamp('actualizado_en')->nullable();
            });
        }
        if (! Schema::hasTable('pantallas')) {
            Schema::create('pantallas', function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('modulo_id');
                $table->string('codigo', 120)->unique();
                $table->string('nombre', 160);
                $table->string('ruta', 160)->nullable();
                $table->string('icono', 100)->nullable();
                $table->boolean('habilitada')->default(true);
                $table->timestamp('creado_en')->nullable();
                $table->timestamp('actualizado_en')->nullable();
            });
        }
        if (! Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->string('codigo', 40)->unique();
                $table->string('nombre', 120);
                $table->string('descripcion', 255)->nullable();
                $table->string('alcance_permitido', 20)->default('sede');
                $table->boolean('es_sistema')->default(false);
                $table->boolean('activo')->default(true);
                $table->timestamp('creado_en')->nullable();
                $table->timestamp('actualizado_en')->nullable();
            });
        }
        if (! Schema::hasTable('menus')) {
            Schema::create('menus', function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->string('codigo', 80)->unique();
                $table->string('nombre', 140);
                $table->string('contexto', 40)->default('sidebar');
                $table->boolean('habilitado')->default(true);
                $table->timestamp('creado_en')->nullable();
                $table->timestamp('actualizado_en')->nullable();
            });
        }
        if (! Schema::hasTable('permisos')) {
            Schema::create('permisos', function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('modulo_id');
                $table->unsignedBigInteger('pantalla_id')->nullable();
                $table->string('codigo', 160)->unique();
                $table->string('accion', 60);
                $table->string('nombre', 160);
                $table->boolean('es_sistema')->default(true);
                $table->boolean('activo')->default(true);
                $table->timestamp('creado_en')->nullable();
                $table->timestamp('actualizado_en')->nullable();
            });
        }
        if (! Schema::hasTable('elementos_menu')) {
            Schema::create('elementos_menu', function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('menu_id');
                $table->unsignedBigInteger('padre_id')->nullable();
                $table->unsignedBigInteger('pantalla_id')->nullable();
                $table->unsignedBigInteger('permiso_id')->nullable();
                $table->string('codigo', 100);
                $table->string('etiqueta', 160);
                $table->string('icono', 100)->nullable();
                $table->string('tipo', 20)->default('pantalla');
                $table->unsignedInteger('orden')->default(0);
                $table->boolean('habilitado')->default(true);
                $table->timestamp('creado_en')->nullable();
                $table->timestamp('actualizado_en')->nullable();
            });
        }
        if (! Schema::hasTable('roles_menus')) {
            Schema::create('roles_menus', function (Blueprint $table): void {
                $table->unsignedBigInteger('rol_id');
                $table->unsignedBigInteger('menu_id');
                $table->timestamp('creado_en')->useCurrent();
                $table->primary(['rol_id', 'menu_id']);
            });
        }

        if (! Schema::hasTable('segmentos_clientes')) {
            Schema::create('segmentos_clientes', function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->string('nombre', 120)->unique();
                $table->string('tipo', 30);
                $table->text('criterios');
                $table->boolean('activo')->default(true);
                $table->timestamp('creado_en')->nullable();
                $table->timestamp('actualizado_en')->nullable();
                $table->index('activo');
            });
        }

        if (! Schema::hasTable('registros_auditoria')) {
            Schema::create('registros_auditoria', function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('usuario_id')->nullable();
                $table->string('accion', 80);
                $table->string('entidad', 80);
                $table->unsignedBigInteger('entidad_id')->nullable();
                $table->text('valores_anteriores')->nullable();
                $table->text('valores_nuevos')->nullable();
                $table->string('direccion_ip', 45)->nullable();
                $table->timestamp('creado_en')->useCurrent();
                $table->char('identificador_universal_evento', 36)->nullable()->unique();
                $table->string('rol_codigo', 40)->nullable();
                $table->unsignedBigInteger('empresa_id')->nullable();
                $table->unsignedBigInteger('sede_id')->nullable();
                $table->string('modulo', 80)->nullable();
                $table->string('resultado', 20)->nullable();
                $table->text('agente_usuario')->nullable();
                $table->string('correlacion_id', 64)->nullable();
                $table->text('observaciones')->nullable();
                $table->dateTime('ocurrido_en')->nullable();
                $table->string('tabla_afectada', 100)->nullable();
                $table->string('identificador_entidad', 128)->nullable();
                $table->text('cambios')->nullable();
                $table->string('origen', 20)->default('laravel');
                $table->index(['usuario_id', 'creado_en']);
                $table->index(['entidad', 'entidad_id']);
                $table->index(['sede_id', 'creado_en']);
            });
        }

        if (! Schema::hasTable('pedidos')) {
            Schema::create('pedidos', function (Blueprint $table): void {
                $table->increments('id');
                $table->unsignedBigInteger('usuario_id')->nullable();
                $table->string('cliente_invitado_nombre', 160)->nullable();
                $table->string('cliente_invitado_correo', 160)->nullable();
                $table->string('cliente_invitado_telefono', 30)->nullable();
                $table->char('token_acceso_publico', 64)->nullable();
                $table->text('direccion_entrega_snapshot')->nullable();
                $table->decimal('total', 10, 2)->default(0);
                $table->decimal('subtotal', 10, 2)->default(0);
                $table->decimal('descuento', 10, 2)->default(0);
                $table->string('cupon_codigo', 50)->nullable();
                $table->string('estado', 40)->default('Pendiente');
                $table->timestamp('creado_en')->useCurrent();
                $table->unsignedInteger('cliente_id')->nullable();
                $table->unsignedInteger('cotizacion_id')->nullable();
                $table->string('canal', 30)->nullable();
                $table->string('tipo_venta', 30)->nullable();
                $table->string('observaciones', 500)->nullable();
                $table->unsignedBigInteger('cupon_id')->nullable();
                $table->unsignedBigInteger('direccion_entrega_id')->nullable();
                $table->unsignedBigInteger('direccion_facturacion_id')->nullable();
                $table->text('direccion_entrega_historica')->nullable();
                $table->text('direccion_facturacion_historica')->nullable();
                $table->unsignedBigInteger('empresa_id')->nullable();
                $table->unsignedBigInteger('sede_id')->nullable();
                $table->unsignedBigInteger('almacen_id')->nullable();
            });
        }

        if (! Schema::hasColumn('pedidos', 'tipo_venta')) {
            Schema::table('pedidos', function (Blueprint $table): void {
                $table->string('tipo_venta', 30)->nullable();
            });
        }
        if (! Schema::hasColumn('pedidos', 'observaciones')) {
            Schema::table('pedidos', function (Blueprint $table): void {
                $table->string('observaciones', 500)->nullable();
            });
        }

        if (! Schema::hasTable('usuarios')) {
            Schema::create('usuarios', function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->string('nombre', 120);
                $table->string('correo', 160)->unique();
                $table->string('resumen_contrasena', 255);
                $table->string('rol', 40)->nullable();
                $table->string('token_recordatorio', 100)->nullable();
                $table->string('foto_perfil', 500)->nullable();
                $table->text('preferencias')->nullable();
                $table->boolean('activo')->default(true);
                $table->dateTime('bloqueado_hasta')->nullable();
                $table->boolean('acceso_controlado')->default(true);
                $table->timestamp('creado_en')->nullable();
                $table->timestamp('actualizado_en')->nullable();
            });
        }

        if (! Schema::hasTable('usuarios_perfiles')) {
            Schema::create('usuarios_perfiles', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('usuario_id')->unique()->constrained('usuarios')->cascadeOnDelete();
                $table->string('telefono', 30)->nullable();
                $table->string('documento', 20)->nullable();
                $table->timestamp('creado_en')->useCurrent();
                $table->timestamp('actualizado_en')->useCurrent();
            });
        }

        if (! Schema::hasTable('empresas')) {
            Schema::create('empresas', function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->string('codigo', 30)->unique();
                $table->string('razon_social', 180);
                $table->string('nombre_comercial', 180)->nullable();
                $table->char('ruc', 11)->nullable()->unique();
                $table->char('moneda_base', 3)->default('PEN');
                $table->boolean('activo')->default(true);
                $table->timestamp('creado_en')->nullable();
                $table->timestamp('actualizado_en')->nullable();
            });
        }

        if (! Schema::hasTable('sedes')) {
            Schema::create('sedes', function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('empresa_id');
                $table->string('codigo', 30);
                $table->string('nombre', 140);
                $table->string('direccion', 255)->nullable();
                $table->string('telefono', 30)->nullable();
                $table->string('zona_horaria', 64)->default('America/Lima');
                $table->boolean('activa')->default(true);
                $table->timestamp('creado_en')->nullable();
                $table->timestamp('actualizado_en')->nullable();
                $table->unique(['empresa_id', 'codigo']);
            });
        }

        if (! Schema::hasTable('almacenes')) {
            Schema::create('almacenes', function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('sede_id');
                $table->string('codigo', 30);
                $table->string('nombre', 140);
                $table->string('tipo', 30)->default('general');
                $table->boolean('permite_venta')->default(true);
                $table->boolean('activo')->default(true);
                $table->timestamp('creado_en')->nullable();
                $table->timestamp('actualizado_en')->nullable();
                $table->unique(['sede_id', 'codigo']);
            });
        }

        if (! Schema::hasTable('categorias')) {
            Schema::create('categorias', function (Blueprint $table): void {
                $table->increments('id');
                $table->string('nombre', 120)->unique();
                $table->string('descripcion', 500)->nullable();
                $table->boolean('activo')->default(true);
                $table->timestamp('creado_en')->useCurrent();
                $table->unsignedBigInteger('empresa_id')->nullable();
                $table->index('empresa_id');
            });
        }

        if (! Schema::hasTable('clientes')) {
            Schema::create('clientes', function (Blueprint $table): void {
                $table->increments('id');
                $table->unsignedBigInteger('usuario_id')->nullable();
                $table->string('tipo', 20)->default('minorista');
                $table->string('tipo_documento', 3)->nullable();
                $table->string('documento', 20)->unique();
                $table->string('razon_social', 160)->nullable();
                $table->string('nombre_contacto', 160);
                $table->string('correo', 160);
                $table->string('telefono', 30)->nullable();
                $table->string('ciudad', 80)->nullable();
                $table->boolean('activo')->default(true);
                $table->timestamp('creado_en')->useCurrent();
                $table->unsignedBigInteger('empresa_id')->nullable();
                $table->index('empresa_id');
            });
        }

        if (! Schema::hasTable('proveedores')) {
            Schema::create('proveedores', function (Blueprint $table): void {
                $table->increments('id');
                $table->string('nombre', 160);
                $table->string('ruc', 20)->unique();
                $table->string('correo', 160);
                $table->string('telefono', 30)->nullable();
                $table->string('ciudad', 80)->nullable();
                $table->boolean('activo')->default(true);
                $table->timestamp('creado_en')->useCurrent();
                $table->unsignedBigInteger('empresa_id')->nullable();
                $table->index('empresa_id');
            });
        }

        if (! Schema::hasTable('productos')) {
            Schema::create('productos', function (Blueprint $table): void {
                $table->increments('id');
                $table->string('marca', 80);
                $table->string('nombre', 160);
                $table->unsignedInteger('categoria_id');
                $table->string('categoria', 120);
                $table->decimal('precio', 10, 2)->default(0);
                $table->decimal('precio_original', 10, 2)->nullable();
                $table->string('descuento', 20)->nullable();
                $table->decimal('precio_oferta', 10, 2)->nullable();
                $table->string('url_imagen', 255)->nullable();
                $table->unsignedInteger('existencias')->default(0);
                $table->string('almacenamiento', 80)->nullable();
                $table->string('color', 80)->nullable();
                $table->string('etiqueta', 80)->nullable();
                $table->text('descripcion')->nullable();
                $table->boolean('activo')->default(true);
                $table->timestamp('creado_en')->useCurrent();
                $table->unsignedBigInteger('empresa_id')->nullable();
                $table->index(['categoria_id', 'empresa_id']);
                $table->index('empresa_id');
            });
        }

        if (! Schema::hasTable('compras')) {
            Schema::create('compras', function (Blueprint $table): void {
                $table->increments('id');
                $table->unsignedInteger('proveedor_id');
                $table->unsignedBigInteger('creado_por_usuario_id')->nullable();
                $table->decimal('total', 12, 2)->default(0);
                $table->string('estado', 30)->default('Pendiente');
                $table->date('fecha_compra');
                $table->boolean('activo')->default(true);
                $table->timestamp('creado_en')->useCurrent();
                $table->string('numero_referencia', 100)->nullable();
                $table->char('moneda', 3)->default('PEN');
                $table->dateTime('actualizado_en')->nullable();
                $table->unsignedBigInteger('empresa_id')->nullable();
                $table->unsignedBigInteger('sede_id')->nullable();
                $table->unsignedBigInteger('almacen_id')->nullable();
                $table->index(['empresa_id', 'fecha_compra']);
                $table->index(['sede_id', 'fecha_compra']);
            });
        }

        if (! Schema::hasTable('cotizaciones')) {
            Schema::create('cotizaciones', function (Blueprint $table): void {
                $table->increments('id');
                $table->unsignedInteger('cliente_id')->nullable();
                $table->unsignedBigInteger('creado_por_usuario_id')->nullable();
                $table->decimal('total', 12, 2)->default(0);
                $table->string('estado', 30)->default('Borrador');
                $table->string('notas', 500)->nullable();
                $table->boolean('activo')->default(true);
                $table->timestamp('creado_en')->useCurrent();
                $table->char('moneda', 3)->default('PEN');
                $table->unsignedBigInteger('empresa_id')->nullable();
                $table->unsignedBigInteger('sede_id')->nullable();
                $table->index(['empresa_id', 'creado_en']);
                $table->index(['cliente_id', 'empresa_id']);
            });
        }

        if (! Schema::hasTable('cupones')) {
            Schema::create('cupones', function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('empresa_id');
                $table->string('codigo', 50);
                $table->string('tipo_descuento', 20);
                $table->decimal('valor', 10, 2);
                $table->decimal('minimo_compra', 10, 2)->default(0);
                $table->unsignedInteger('limite_usos')->nullable();
                $table->unsignedInteger('usos')->default(0);
                $table->dateTime('inicia_en');
                $table->dateTime('finaliza_en');
                $table->boolean('activo')->default(true);
                $table->timestamp('creado_en')->nullable();
                $table->timestamp('actualizado_en')->nullable();
                $table->unique(['empresa_id', 'codigo'], 'un_cupones_empresa_codigo');
                $table->unique(['id', 'empresa_id'], 'un_cupones_id_empresa');
                $table->index(['activo', 'inicia_en', 'finaliza_en'], 'ind_cupones_vigencia');
                $table->index('activo', 'ind_cupones_activo');
                $table->index(['empresa_id', 'activo', 'inicia_en', 'finaliza_en'], 'ind_cupones_empresa_vigencia');
            });
        }

        if (! Schema::hasTable('campanas_publicitarias')) {
            Schema::create('campanas_publicitarias', function (Blueprint $table): void {
                $table->increments('id');
                $table->string('nombre', 120);
                $table->string('ubicacion', 30)->default('emergente')->index();
                $table->string('titulo', 160);
                $table->string('descripcion', 500)->nullable();
                $table->string('texto_boton', 80)->default('Ver oferta');
                $table->string('url_boton', 255)->default('catalog');
                $table->string('url_imagen')->nullable();
                $table->decimal('precio_anterior', 10, 2)->nullable();
                $table->decimal('precio_oferta', 10, 2)->nullable();
                $table->dateTime('inicia_en')->index();
                $table->dateTime('finaliza_en')->index();
                $table->boolean('activo')->default(true)->index();
                $table->unsignedInteger('vistas')->default(0);
                $table->unsignedInteger('clics')->default(0);
                $table->timestamp('creado_en')->useCurrent();
                $table->timestamp('actualizado_en')->nullable();
                $table->unsignedBigInteger('empresa_id')->nullable();
            });
        }

        if (! Schema::hasTable('eventos_campana')) {
            Schema::create('eventos_campana', function (Blueprint $table): void {
                $table->id();
                $table->unsignedInteger('campana_id');
                $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();
                $table->string('tipo', 20)->index();
                $table->string('sesion_hash', 64)->nullable()->index();
                $table->timestamp('creado_en')->useCurrent();
                $table->index(['campana_id', 'tipo', 'creado_en']);
            });
        }

        if (! Schema::hasTable('campana_segmento')) {
            Schema::create('campana_segmento', function (Blueprint $table): void {
                $table->unsignedInteger('campana_id');
                $table->unsignedBigInteger('segmento_id');
                $table->primary(['campana_id', 'segmento_id']);
            });
        }

        if (! Schema::hasTable('mensajes_contacto')) {
            Schema::create('mensajes_contacto', function (Blueprint $table): void {
                $table->increments('id');
                $table->string('nombre', 120);
                $table->string('contacto', 160);
                $table->text('mensaje');
                $table->string('estado', 30)->default('Nuevo')->index();
                $table->timestamp('creado_en')->useCurrent();
            });
        }

        if (! Schema::hasTable('movimientos_inventario')) {
            Schema::create('movimientos_inventario', function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->unsignedInteger('producto_id');
                $table->unsignedBigInteger('usuario_id')->nullable();
                $table->string('tipo_movimiento', 30);
                $table->decimal('cantidad', 18, 6);
                $table->string('notas', 500);
                $table->dateTime('creado_en')->useCurrent();
                $table->unsignedBigInteger('empresa_id')->nullable();
                $table->unsignedBigInteger('sede_id')->nullable();
                $table->unsignedBigInteger('almacen_id')->nullable();
                $table->unsignedInteger('variante_id')->nullable();
                $table->string('documento_tipo', 60)->nullable();
                $table->string('documento_id', 80)->nullable();
                $table->index(['producto_id', 'creado_en'], 'ind_movimientos_producto_fecha');
                $table->index('usuario_id', 'ind_movimientos_usuario');
                $table->index(['sede_id', 'creado_en'], 'ind_movimientos_sede_fecha');
                $table->index(['almacen_id', 'variante_id', 'creado_en'], 'ind_movimientos_almacen_variante_fecha');
                $table->index(['documento_tipo', 'documento_id'], 'ind_movimientos_documento');
                $table->index('variante_id', 'ind_movimientos_variante');
            });
        }

        if (! Schema::hasTable('productos_favoritos')) {
            Schema::create('productos_favoritos', function (Blueprint $table): void {
                $table->id();
                $table->unsignedInteger('producto_id');
                $table->unsignedBigInteger('usuario_id');
                $table->timestamp('creado_en')->useCurrent();
                $table->unique(['producto_id', 'usuario_id']);
            });
        }

        if (! Schema::hasTable('productos_resenas')) {
            Schema::create('productos_resenas', function (Blueprint $table): void {
                $table->id();
                $table->unsignedInteger('producto_id');
                $table->unsignedBigInteger('usuario_id');
                $table->unsignedTinyInteger('valoracion');
                $table->text('comentario')->nullable();
                $table->timestamp('creado_en')->useCurrent();
                $table->timestamp('actualizado_en')->useCurrent();
                $table->unique(['producto_id', 'usuario_id']);
                $table->index(['producto_id', 'valoracion']);
            });
        }

        if (! Schema::hasTable('perfiles_inteligentes_productos')) {
            Schema::create('perfiles_inteligentes_productos', function (Blueprint $table): void {
                $table->unsignedInteger('producto_id')->primary();
                $table->unsignedTinyInteger('puntuacion_rendimiento')->default(70);
                $table->unsignedTinyInteger('puntuacion_camara')->default(70);
                $table->unsignedTinyInteger('puntuacion_bateria')->default(70);
                $table->unsignedTinyInteger('puntuacion_valor')->default(70);
                $table->timestamp('actualizado_en')->nullable();
            });
        }

        if (! Schema::hasTable('perfil_campos')) {
            Schema::create('perfil_campos', function (Blueprint $table): void {
                $table->id();
                $table->string('codigo', 80)->unique();
                $table->string('etiqueta', 120);
                $table->string('tipo', 30)->default('text');
                $table->json('opciones')->nullable();
                $table->unsignedInteger('orden')->default(0);
                $table->boolean('activo')->default(true);
                $table->dateTime('creado_en')->useCurrent();
                $table->dateTime('actualizado_en')->useCurrent();
                $table->index(['activo', 'orden']);
            });
        }

        if (! Schema::hasTable('roles_perfil_campos')) {
            Schema::create('roles_perfil_campos', function (Blueprint $table): void {
                $table->foreignId('rol_id')->constrained('roles')->cascadeOnDelete();
                $table->foreignId('perfil_campo_id')->constrained('perfil_campos')->cascadeOnDelete();
                $table->boolean('visible')->default(true);
                $table->boolean('editable')->default(false);
                $table->boolean('requerido')->default(false);
                $table->dateTime('creado_en')->useCurrent();
                $table->dateTime('actualizado_en')->useCurrent();
                $table->primary(['rol_id', 'perfil_campo_id']);
                $table->index(['perfil_campo_id', 'visible']);
            });
        }

        if (! Schema::hasTable('usuarios_perfil_campos')) {
            Schema::create('usuarios_perfil_campos', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
                $table->foreignId('perfil_campo_id')->constrained('perfil_campos')->cascadeOnDelete();
                $table->text('valor')->nullable();
                $table->dateTime('creado_en')->useCurrent();
                $table->dateTime('actualizado_en')->useCurrent();
                $table->unique(['usuario_id', 'perfil_campo_id']);
            });
        }

        foreach (['categorias', 'productos', 'clientes', 'proveedores'] as $table) {
            if (! Schema::hasColumn($table, 'empresa_id')) {
                Schema::table($table, function (Blueprint $blueprint): void {
                    $blueprint->unsignedBigInteger('empresa_id')->nullable();
                });
            }
        }

        foreach (['cotizaciones', 'compras'] as $table) {
            if (! Schema::hasColumn($table, 'creado_por_usuario_id')) {
                Schema::table($table, function (Blueprint $blueprint): void {
                    $blueprint->unsignedBigInteger('creado_por_usuario_id')->nullable();
                });
            }
        }

        if (! Schema::hasColumn('registros_auditoria', 'identificador_universal_evento')) {
            Schema::table('registros_auditoria', function (Blueprint $table): void {
                $table->char('identificador_universal_evento', 36)->nullable()->unique();
            });
        }

        foreach (['creado_en', 'actualizado_en'] as $column) {
            if (! Schema::hasColumn('segmentos_clientes', $column)) {
                Schema::table('segmentos_clientes', function (Blueprint $table) use ($column): void {
                    $table->timestamp($column)->nullable();
                });
            }
        }

        foreach (['empresas', 'sedes', 'almacenes', 'modulos', 'pantallas', 'roles', 'menus', 'permisos', 'elementos_menu'] as $table) {
            foreach (['creado_en', 'actualizado_en'] as $column) {
                if (! Schema::hasColumn($table, $column)) {
                    Schema::table($table, function (Blueprint $blueprint) use ($column): void {
                        $blueprint->timestamp($column)->nullable();
                    });
                }
            }
        }

        if (! Schema::hasColumn('permisos', 'es_sistema')) {
            Schema::table('permisos', function (Blueprint $table): void {
                $table->boolean('es_sistema')->default(true);
            });
        }

        if (! Schema::hasTable('roles_permisos')) {
            Schema::create('roles_permisos', function (Blueprint $table): void {
                $table->unsignedBigInteger('rol_id');
                $table->unsignedBigInteger('permiso_id');
                $table->string('alcance', 20)->default('sede');
                $table->timestamp('creado_en')->useCurrent();
                $table->primary(['rol_id', 'permiso_id', 'alcance']);
            });
        }
        if (! Schema::hasColumn('roles_menus', 'creado_en')) {
            Schema::table('roles_menus', function (Blueprint $table): void {
                $table->timestamp('creado_en')->nullable();
            });
        }
        if (! Schema::hasTable('usuarios_roles')) {
            Schema::create('usuarios_roles', function (Blueprint $table): void {
                $table->unsignedBigInteger('usuario_id');
                $table->unsignedBigInteger('rol_id');
                $table->unsignedBigInteger('asignado_por_usuario_id')->nullable();
                $table->timestamp('asignado_en')->useCurrent();
                $table->timestamp('vigente_hasta')->nullable();
                $table->boolean('activo')->default(true);
                $table->primary(['usuario_id', 'rol_id']);
            });
        }
        if (! Schema::hasTable('usuarios_sedes_roles')) {
            Schema::create('usuarios_sedes_roles', function (Blueprint $table): void {
                $table->unsignedBigInteger('usuario_id');
                $table->unsignedBigInteger('sede_id');
                $table->unsignedBigInteger('rol_id');
                $table->string('alcance_asignacion', 20)->default('sede');
                $table->unsignedBigInteger('asignado_por_usuario_id')->nullable();
                $table->timestamp('asignado_en')->useCurrent();
                $table->timestamp('vigente_hasta')->nullable();
                $table->boolean('activo')->default(true);
                $table->primary(['usuario_id', 'sede_id', 'rol_id']);
            });
        } elseif (! Schema::hasColumn('usuarios_sedes_roles', 'alcance_asignacion')) {
            Schema::table('usuarios_sedes_roles', function (Blueprint $table): void {
                $table->string('alcance_asignacion', 20)->default('sede');
            });
        }
        if (! Schema::hasTable('versiones_inicializacion_control_acceso')) {
            Schema::create('versiones_inicializacion_control_acceso', function (Blueprint $table): void {
                $table->string('codigo', 80)->primary();
                $table->timestamp('aplicado_en')->useCurrent();
            });
        }

        if (! Schema::hasColumn('cupones', 'creado_en')) {
            Schema::table('cupones', function (Blueprint $table): void {
                $table->timestamp('creado_en')->nullable();
            });
        }

        if (! Schema::hasColumn('cupones', 'empresa_id')) {
            Schema::table('cupones', function (Blueprint $table): void {
                $table->unsignedBigInteger('empresa_id')->nullable()->index();
            });
        }

        if (! Schema::hasColumn('cupones', 'actualizado_en')) {
            Schema::table('cupones', function (Blueprint $table): void {
                $table->timestamp('actualizado_en')->nullable();
            });
        }

        if (! Schema::hasTable('caracteristicas_productos')) {
            Schema::create('caracteristicas_productos', function (Blueprint $table): void {
                $table->increments('id');
                $table->unsignedInteger('producto_id');
                $table->string('nombre', 100);
                $table->string('valor', 255);
                $table->foreign('producto_id')->references('id')->on('productos')->cascadeOnDelete();
            });
        }
        Schema::dropIfExists('referencias_imagenes_productos');
        if (! Schema::hasTable('referencias_imagenes_productos')) {
            Schema::create('referencias_imagenes_productos', function (Blueprint $table): void {
                $table->increments('id');
                $table->unsignedInteger('producto_id');
                $table->string('ruta_imagen', 255);
                $table->integer('orden')->default(0);
                $table->dateTime('actualizado_en')->nullable();
                $table->unique(['producto_id', 'ruta_imagen'], 'un_producto_imagen_ruta');
                $table->index(['producto_id', 'orden', 'id'], 'ind_producto_imagen_orden');
                $table->foreign('producto_id')->references('id')->on('productos')->cascadeOnDelete();
            });
        }
        if (! Schema::hasTable('variantes_productos')) {
            Schema::create('variantes_productos', function (Blueprint $table): void {
                $table->increments('id');
                $table->unsignedInteger('producto_id');
                $table->string('codigo_sku', 80)->nullable();
                $table->string('codigo_barras', 80)->nullable();
                $table->string('nombre_color', 100)->default('Único');
                $table->string('codigo_color', 20)->nullable();
                $table->unsignedInteger('existencias')->default(0);
                $table->boolean('controla_serie')->default(false);
                $table->string('estado', 20)->default('activo');
                $table->timestamp('fecha_creacion')->useCurrent();
                $table->foreign('producto_id')->references('id')->on('productos')->cascadeOnDelete();
            });
        }
        if (! Schema::hasTable('imagenes_variantes')) {
            Schema::create('imagenes_variantes', function (Blueprint $table): void {
                $table->increments('id');
                $table->unsignedInteger('variante_id');
                $table->string('ruta_imagen', 255);
                $table->boolean('imagen_principal')->default(false);
                $table->integer('orden')->default(0);
                $table->timestamp('fecha_creacion')->useCurrent();
                $table->index('variante_id', 'ind_variante_imagenes_variante');
                $table->foreign('variante_id')->references('id')->on('variantes_productos')->cascadeOnDelete();
            });
        }
        if (! Schema::hasTable('dispositivos')) {
            Schema::create('dispositivos', function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->unsignedInteger('producto_id');
                $table->unsignedInteger('variante_id')->nullable();
                $table->string('imei', 20)->nullable();
                $table->string('numero_serie', 100)->nullable();
                $table->unsignedBigInteger('detalle_compra_id')->nullable();
                $table->unsignedBigInteger('almacen_actual_id')->nullable();
                $table->string('estado', 30)->default('disponible');
                $table->dateTime('fecha_ingreso')->nullable();
                $table->dateTime('fecha_salida')->nullable();
                $table->foreign('producto_id')->references('id')->on('productos');
            });
        }
        if (! Schema::hasTable('movimientos_dispositivos')) {
            Schema::create('movimientos_dispositivos', function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('dispositivo_id');
                $table->unsignedBigInteger('movimiento_inventario_id');
                $table->string('estado_anterior', 30)->nullable();
                $table->string('estado_nuevo', 30);
                $table->dateTime('creado_en')->useCurrent();
            });
        }
        if (! Schema::hasTable('detalles_ventas_dispositivos')) {
            Schema::create('detalles_ventas_dispositivos', function (Blueprint $table): void {
                $table->unsignedBigInteger('detalle_venta_id');
                $table->unsignedBigInteger('dispositivo_id')->unique();
                $table->primary(['detalle_venta_id', 'dispositivo_id']);
            });
        }
        if (! Schema::hasTable('existencias_almacen')) {
            Schema::create('existencias_almacen', function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('almacen_id');
                $table->unsignedInteger('variante_id')->nullable();
                $table->unsignedInteger('producto_id');
                $table->unsignedBigInteger('sede_id');
                $table->unsignedBigInteger('empresa_id');
                $table->decimal('cantidad_fisica', 18, 6)->default(0);
                $table->decimal('cantidad_reservada', 18, 6)->default(0);
                $table->dateTime('actualizado_en')->useCurrent();
                $table->foreign('almacen_id')->references('id')->on('almacenes');
                $table->foreign('producto_id')->references('id')->on('productos');
            });
        }
        if (! Schema::hasTable('detalles_compras')) {
            Schema::create('detalles_compras', function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->unsignedInteger('compra_id');
                $table->unsignedInteger('producto_id');
                $table->unsignedInteger('cantidad');
                $table->decimal('costo_unitario', 10, 2);
                $table->unsignedInteger('variante_id')->nullable();
                $table->decimal('descuento', 15, 2)->default(0);
                $table->decimal('impuesto', 15, 2)->default(0);
                $table->decimal('total_linea', 15, 2)->nullable();
                $table->foreign('compra_id')->references('id')->on('compras');
                $table->foreign('producto_id')->references('id')->on('productos');
            });
        }
        if (! Schema::hasTable('detalles_cotizaciones')) {
            Schema::create('detalles_cotizaciones', function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->unsignedInteger('cotizacion_id');
                $table->unsignedInteger('producto_id');
                $table->unsignedInteger('cantidad');
                $table->decimal('precio_unitario', 10, 2);
                $table->unsignedInteger('variante_id')->nullable();
                $table->string('descripcion_historica', 255)->nullable();
                $table->decimal('descuento', 15, 2)->default(0);
                $table->decimal('impuesto', 15, 2)->default(0);
                $table->decimal('total_linea', 15, 2)->nullable();
            });
        }
        if (! Schema::hasTable('carritos')) {
            Schema::create('carritos', function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('empresa_id');
                $table->unsignedBigInteger('sede_id')->nullable();
                $table->unsignedBigInteger('usuario_id')->nullable();
                $table->unsignedInteger('cliente_id')->nullable();
                $table->char('resumen_token', 64)->nullable()->unique();
                $table->unsignedInteger('pedido_id')->nullable();
                $table->string('estado', 20)->default('activo');
                $table->char('moneda', 3)->default('PEN');
                $table->dateTime('expira_en')->nullable();
                $table->dateTime('creado_en')->useCurrent();
                $table->dateTime('actualizado_en')->nullable();
            });
        }
        if (! Schema::hasTable('campanas_segmentos')) {
            Schema::create('campanas_segmentos', function (Blueprint $table): void {
                $table->unsignedInteger('campana_id');
                $table->unsignedBigInteger('segmento_id');
                $table->primary(['campana_id', 'segmento_id']);
            });
        }
        if (! Schema::hasTable('detalles_pedidos')) {
            Schema::create('detalles_pedidos', function (Blueprint $table): void {
                $table->increments('id');
                $table->unsignedInteger('pedido_id');
                $table->unsignedInteger('producto_id');
                $table->unsignedInteger('cantidad');
                $table->decimal('precio_unitario', 10, 2);
                $table->unsignedInteger('variante_id')->nullable();
                $table->string('descripcion_historica', 255)->nullable();
                $table->decimal('descuento', 15, 2)->default(0);
                $table->decimal('impuesto', 15, 2)->default(0);
                $table->foreign('pedido_id')->references('id')->on('pedidos')->cascadeOnDelete();
                $table->foreign('producto_id')->references('id')->on('productos');
            });
        }
        if (! Schema::hasTable('productos_proveedores')) {
            Schema::create('productos_proveedores', function (Blueprint $table): void {
                $table->unsignedInteger('producto_id');
                $table->unsignedInteger('proveedor_id');
                $table->primary(['producto_id', 'proveedor_id']);
                $table->foreign('producto_id')->references('id')->on('productos')->cascadeOnDelete();
                $table->foreign('proveedor_id')->references('id')->on('proveedores')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('ventas')) {
            Schema::create('ventas', function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('empresa_id');
                $table->unsignedBigInteger('sede_id');
                $table->unsignedBigInteger('almacen_id');
                $table->unsignedInteger('pedido_id')->nullable();
                $table->unsignedInteger('cliente_id')->nullable();
                $table->unsignedBigInteger('usuario_id')->nullable();
                $table->unsignedBigInteger('sesion_caja_id')->nullable();
                $table->string('codigo', 50);
                $table->string('estado', 30)->default('borrador');
                $table->char('moneda', 3)->default('PEN');
                $table->decimal('subtotal', 15, 2)->default(0);
                $table->decimal('descuento', 15, 2)->default(0);
                $table->decimal('impuestos', 15, 2)->default(0);
                $table->decimal('total', 15, 2)->default(0);
                $table->dateTime('fecha_venta')->useCurrent();
                $table->string('canal', 30)->nullable();
                $table->string('observaciones', 500)->nullable();
                $table->dateTime('creado_en')->useCurrent();
                $table->dateTime('actualizado_en')->useCurrent();
            });
        }

        if (! Schema::hasTable('detalles_ventas')) {
            Schema::create('detalles_ventas', function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('venta_id');
                $table->unsignedInteger('producto_id');
                $table->unsignedInteger('variante_id')->nullable();
                $table->unsignedInteger('detalle_pedido_id')->nullable();
                $table->string('descripcion_historica', 255);
                $table->string('sku_historico', 80)->nullable();
                $table->decimal('cantidad', 18, 6);
                $table->decimal('precio_unitario', 15, 2);
                $table->decimal('descuento', 15, 2)->default(0);
                $table->decimal('tasa_impuesto', 7, 4)->default(0);
                $table->decimal('impuesto', 15, 2)->default(0);
                $table->decimal('total_linea', 15, 2);
            });
        }

        if (! Schema::hasTable('pagos')) {
            Schema::create('pagos', function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('empresa_id');
                $table->unsignedBigInteger('sede_id');
                $table->string('codigo', 60);
                $table->string('tipo', 20);
                $table->string('estado', 30)->default('registrado');
                $table->unsignedBigInteger('medio_pago_id');
                $table->unsignedBigInteger('sesion_caja_id')->nullable();
                $table->unsignedBigInteger('cuenta_bancaria_id')->nullable();
                $table->unsignedBigInteger('usuario_id')->nullable();
                $table->unsignedInteger('pedido_id')->nullable();
                $table->unsignedBigInteger('venta_id')->nullable();
                $table->unsignedInteger('compra_id')->nullable();
                $table->char('moneda', 3)->default('PEN');
                $table->decimal('importe', 15, 2);
                $table->string('referencia', 120)->nullable();
                $table->dateTime('fecha_pago')->useCurrent();
                $table->dateTime('creado_en')->useCurrent();
                $table->unique(['empresa_id', 'codigo']);
            });
        }

        foreach (['documentos_electronicos', 'devoluciones_garantias'] as $tablaRelacionadaConVenta) {
            if (! Schema::hasTable($tablaRelacionadaConVenta)) {
                Schema::create($tablaRelacionadaConVenta, function (Blueprint $table): void {
                    $table->bigIncrements('id');
                    $table->unsignedBigInteger('venta_id')->nullable();
                });
            }
        }

        foreach (['empresa_id', 'sede_id'] as $columnaDeAlcance) {
            if (! Schema::hasColumn('devoluciones_garantias', $columnaDeAlcance)) {
                Schema::table('devoluciones_garantias', function (Blueprint $table) use ($columnaDeAlcance): void {
                    $table->unsignedBigInteger($columnaDeAlcance)->nullable();
                });
            }
        }

        if (! Schema::hasTable('medios_pago')) {
            Schema::create('medios_pago', function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->string('codigo', 40)->unique();
                $table->string('nombre', 100);
                $table->boolean('requiere_caja')->default(false);
                $table->boolean('requiere_banco')->default(false);
                $table->boolean('activo')->default(true);
            });
        }

        if (! Schema::hasTable('cajas')) {
            Schema::create('cajas', function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('sede_id');
                $table->string('codigo', 40);
                $table->string('nombre', 120);
                $table->boolean('activa')->default(true);
                $table->dateTime('creado_en')->useCurrent();
                $table->unique(['sede_id', 'codigo']);
            });
        }

        if (! Schema::hasTable('sesiones_caja')) {
            Schema::create('sesiones_caja', function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('caja_id');
                $table->unsignedBigInteger('usuario_apertura_id');
                $table->unsignedBigInteger('usuario_cierre_id')->nullable();
                $table->string('estado', 20)->default('abierta');
                $table->decimal('saldo_inicial', 15, 2)->default(0);
                $table->decimal('saldo_cierre_declarado', 15, 2)->nullable();
                $table->dateTime('abierta_en')->useCurrent();
                $table->dateTime('cerrada_en')->nullable();
            });
        }

        if (! Schema::hasTable('movimientos_caja')) {
            Schema::create('movimientos_caja', function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('sesion_caja_id');
                $table->unsignedBigInteger('pago_id')->nullable();
                $table->string('tipo', 40);
                $table->string('direccion', 10);
                $table->decimal('importe', 15, 2);
                $table->string('descripcion', 500)->nullable();
                $table->unsignedBigInteger('creado_por_usuario_id')->nullable();
                $table->dateTime('creado_en')->useCurrent();
            });
        }

        if (! Schema::hasTable('cuentas_bancarias')) {
            Schema::create('cuentas_bancarias', function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('empresa_id');
                $table->string('codigo', 40);
                $table->string('banco', 120);
                $table->string('tipo_cuenta', 40);
                $table->char('moneda', 3)->default('PEN');
                $table->string('cuenta_mascara', 40)->nullable();
                $table->string('referencia_secreto', 255)->nullable();
                $table->boolean('activa')->default(true);
                $table->unique(['empresa_id', 'codigo']);
            });
        }

        if (! Schema::hasTable('reembolsos')) {
            Schema::create('reembolsos', function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->unsignedInteger('devolucion_id');
                $table->unsignedBigInteger('pago_id')->unique();
                $table->decimal('importe', 15, 2);
                $table->string('estado', 30)->default('pendiente');
                $table->unsignedBigInteger('autorizado_por_usuario_id')->nullable();
                $table->dateTime('creado_en')->useCurrent();
            });
        }
    }
}

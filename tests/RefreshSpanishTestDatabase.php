<?php

namespace Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait RefreshSpanishTestDatabase
{
    use LazilyRefreshDatabase {
        migrateDatabases as migrateLaravelDatabases;
    }

    protected function migrateDatabases()
    {
        $this->migrateLaravelDatabases();

        if (DB::getDriverName() !== 'sqlite') {
            return;
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

        Schema::create('roles_permisos', function (Blueprint $table): void {
            $table->unsignedBigInteger('rol_id');
            $table->unsignedBigInteger('permiso_id');
            $table->string('alcance', 20)->default('sede');
            $table->timestamp('creado_en')->useCurrent();
            $table->primary(['rol_id', 'permiso_id', 'alcance']);
        });
        if (! Schema::hasColumn('roles_menus', 'creado_en')) {
            Schema::table('roles_menus', function (Blueprint $table): void {
                $table->timestamp('creado_en')->nullable();
            });
        }
        Schema::create('usuarios_roles', function (Blueprint $table): void {
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('rol_id');
            $table->unsignedBigInteger('asignado_por_usuario_id')->nullable();
            $table->timestamp('asignado_en')->useCurrent();
            $table->timestamp('vigente_hasta')->nullable();
            $table->boolean('activo')->default(true);
            $table->primary(['usuario_id', 'rol_id']);
        });
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
        Schema::create('versiones_inicializacion_control_acceso', function (Blueprint $table): void {
            $table->string('codigo', 80)->primary();
            $table->timestamp('aplicado_en')->useCurrent();
        });

        if (! Schema::hasColumn('cupones', 'creado_en')) {
            Schema::table('cupones', function (Blueprint $table): void {
                $table->timestamp('creado_en')->nullable();
            });
        }

        if (! Schema::hasColumn('cupones', 'actualizado_en')) {
            Schema::table('cupones', function (Blueprint $table): void {
                $table->timestamp('actualizado_en')->nullable();
            });
        }

        Schema::create('caracteristicas_productos', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('producto_id');
            $table->string('nombre', 100);
            $table->string('valor', 255);
            $table->foreign('producto_id')->references('id')->on('productos')->cascadeOnDelete();
        });
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
        Schema::create('campanas_segmentos', function (Blueprint $table): void {
            $table->unsignedInteger('campana_id');
            $table->unsignedBigInteger('segmento_id');
            $table->primary(['campana_id', 'segmento_id']);
        });
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
        Schema::create('productos_proveedores', function (Blueprint $table): void {
            $table->unsignedInteger('producto_id');
            $table->unsignedInteger('proveedor_id');
            $table->primary(['producto_id', 'proveedor_id']);
            $table->foreign('producto_id')->references('id')->on('productos')->cascadeOnDelete();
            $table->foreign('proveedor_id')->references('id')->on('proveedores')->cascadeOnDelete();
        });

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

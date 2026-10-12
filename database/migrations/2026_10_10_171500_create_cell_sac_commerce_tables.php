<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('categorias')) {
            $this->completeExistingSchema();

            return;
        }

        Schema::create('categorias', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('nombre', 120)->unique();
            $table->string('descripcion', 500)->nullable();
            $table->boolean('activo')->default(true)->index();
            $table->timestamp('creado_en')->useCurrent();
        });

        Schema::create('productos', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('marca', 80)->index();
            $table->string('nombre', 160)->index();
            $table->unsignedInteger('categoria_id');
            $table->string('categoria', 120);
            $table->decimal('precio', 10, 2)->default(0);
            $table->decimal('precio_original', 10, 2)->nullable();
            $table->string('descuento', 20)->nullable();
            $table->decimal('precio_oferta', 10, 2)->nullable();
            $table->string('url_imagen')->nullable();
            $table->unsignedInteger('existencias')->default(0);
            $table->string('almacenamiento', 80)->nullable();
            $table->string('color', 80)->nullable();
            $table->string('etiqueta', 80)->nullable();
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true)->index();
            $table->timestamp('creado_en')->useCurrent();
            $table->foreign('categoria_id')->references('id')->on('categorias')->restrictOnDelete();
            $table->index(['activo', 'categoria_id', 'precio']);
        });

        Schema::create('producto_caracteristicas', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('producto_id');
            $table->string('nombre', 100);
            $table->string('valor', 255);
            $table->foreign('producto_id')->references('id')->on('productos')->cascadeOnDelete();
            $table->index(['producto_id', 'nombre']);
        });

        Schema::create('producto_imagen_referencia', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('producto_id');
            $table->string('ruta_imagen');
            $table->integer('orden')->default(0);
            $table->timestamp('actualizado_en')->nullable();
            $table->foreign('producto_id')->references('id')->on('productos')->cascadeOnDelete();
            $table->index(['producto_id', 'orden']);
        });

        Schema::create('producto_variantes', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('producto_id');
            $table->string('nombre_color', 100)->default('Único');
            $table->string('codigo_color', 20)->nullable();
            $table->unsignedInteger('stock')->default(0);
            $table->string('estado', 20)->default('activo')->index();
            $table->timestamp('fecha_creacion')->useCurrent();
            $table->foreign('producto_id')->references('id')->on('productos')->cascadeOnDelete();
        });

        Schema::create('variante_imagenes', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('variante_id');
            $table->string('ruta_imagen');
            $table->boolean('imagen_principal')->default(false);
            $table->integer('orden')->default(0);
            $table->timestamp('fecha_creacion')->useCurrent();
            $table->foreign('variante_id')->references('id')->on('producto_variantes')->cascadeOnDelete();
        });

        Schema::create('perfiles_inteligentes_productos', function (Blueprint $table): void {
            $table->unsignedInteger('producto_id')->primary();
            $table->unsignedTinyInteger('puntuacion_rendimiento')->default(70);
            $table->unsignedTinyInteger('puntuacion_camara')->default(70);
            $table->unsignedTinyInteger('puntuacion_bateria')->default(70);
            $table->unsignedTinyInteger('puntuacion_valor')->default(70);
            $table->timestamp('actualizado_en')->nullable();
            $table->foreign('producto_id')->references('id')->on('productos')->cascadeOnDelete();
        });

        Schema::create('clientes', function (Blueprint $table): void {
            $table->increments('id');
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('tipo', 20)->default('minorista')->index();
            $table->string('documento', 20)->unique();
            $table->string('razon_social', 160)->nullable();
            $table->string('nombre_contacto', 160);
            $table->string('correo', 160)->index();
            $table->string('telefono', 30)->nullable();
            $table->string('ciudad', 80)->nullable();
            $table->boolean('activo')->default(true)->index();
            $table->timestamp('creado_en')->useCurrent();
        });

        Schema::create('proveedores', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('nombre', 160);
            $table->string('ruc', 20)->unique();
            $table->string('correo', 160);
            $table->string('telefono', 30)->nullable();
            $table->string('ciudad', 80)->nullable();
            $table->boolean('activo')->default(true)->index();
            $table->timestamp('creado_en')->useCurrent();
        });

        Schema::create('producto_proveedor', function (Blueprint $table): void {
            $table->unsignedInteger('producto_id');
            $table->unsignedInteger('proveedor_id');
            $table->primary(['producto_id', 'proveedor_id']);
            $table->foreign('producto_id')->references('id')->on('productos')->cascadeOnDelete();
            $table->foreign('proveedor_id')->references('id')->on('proveedores')->restrictOnDelete();
        });

        Schema::create('compras', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('proveedor_id');
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('total', 12, 2)->default(0);
            $table->string('estado', 30)->default('Pendiente')->index();
            $table->date('fecha_compra')->index();
            $table->boolean('activo')->default(true);
            $table->timestamp('creado_en')->useCurrent();
            $table->foreign('proveedor_id')->references('id')->on('proveedores')->restrictOnDelete();
        });

        Schema::create('compra_detalles', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('compra_id');
            $table->unsignedInteger('producto_id');
            $table->unsignedInteger('cantidad');
            $table->decimal('costo_unitario', 10, 2);
            $table->foreign('compra_id')->references('id')->on('compras')->cascadeOnDelete();
            $table->foreign('producto_id')->references('id')->on('productos')->restrictOnDelete();
        });

        Schema::create('cotizaciones', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('cliente_id');
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('total', 12, 2)->default(0);
            $table->string('estado', 30)->default('Borrador')->index();
            $table->string('notas', 500)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamp('creado_en')->useCurrent();
            $table->foreign('cliente_id')->references('id')->on('clientes')->restrictOnDelete();
        });

        Schema::create('cotizacion_detalles', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('cotizacion_id');
            $table->unsignedInteger('producto_id');
            $table->unsignedInteger('cantidad');
            $table->decimal('precio_unitario', 10, 2);
            $table->foreign('cotizacion_id')->references('id')->on('cotizaciones')->cascadeOnDelete();
            $table->foreign('producto_id')->references('id')->on('productos')->restrictOnDelete();
        });

        Schema::create('pedidos', function (Blueprint $table): void {
            $table->increments('id');
            $table->foreignId('usuario_id')->constrained('users')->restrictOnDelete();
            $table->decimal('total', 10, 2);
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('descuento', 10, 2)->default(0);
            $table->string('cupon_codigo', 50)->nullable();
            $table->string('estado', 40)->default('Pendiente')->index();
            $table->timestamp('creado_en')->useCurrent();
        });

        Schema::create('detalle_pedidos', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('pedido_id');
            $table->unsignedInteger('producto_id');
            $table->unsignedInteger('cantidad');
            $table->decimal('precio_unitario', 10, 2);
            $table->foreign('pedido_id')->references('id')->on('pedidos')->cascadeOnDelete();
            $table->foreign('producto_id')->references('id')->on('productos')->restrictOnDelete();
        });

        Schema::create('logistica_mayorista', function (Blueprint $table): void {
            $table->unsignedInteger('pedido_id')->primary();
            $table->string('exportador', 160)->nullable();
            $table->string('direccion_exportador')->nullable();
            $table->string('contacto_exportador', 160)->nullable();
            $table->string('direccion_consignatario')->nullable();
            $table->string('contacto_consignatario', 160)->nullable();
            $table->string('factura_numero', 80)->nullable();
            $table->date('factura_fecha')->nullable();
            $table->string('orden_compra', 80)->nullable();
            $table->string('carta_credito', 100)->nullable();
            $table->date('fecha_emision')->nullable();
            $table->string('pais_origen', 100)->nullable();
            $table->string('lugar_carga', 160)->nullable();
            $table->string('puerto_descarga', 160)->nullable();
            $table->string('direccion_entrega')->nullable();
            $table->text('descripcion_mercancia')->nullable();
            $table->string('codigo_hs', 40)->nullable();
            $table->unsignedInteger('cantidad_unidades')->nullable();
            $table->string('bultos', 160)->nullable();
            $table->string('tipo_embalaje', 100)->nullable();
            $table->string('dimensiones', 120)->nullable();
            $table->decimal('volumen_m3', 10, 3)->nullable();
            $table->decimal('peso_neto', 10, 2)->nullable();
            $table->decimal('peso_bruto', 10, 2)->nullable();
            $table->foreign('pedido_id')->references('id')->on('pedidos')->cascadeOnDelete();
        });

        Schema::create('movimientos_inventario', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('producto_id');
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('tipo_movimiento', 20)->index();
            $table->integer('cantidad');
            $table->string('notas', 500);
            $table->timestamp('creado_en')->useCurrent();
            $table->foreign('producto_id')->references('id')->on('productos')->restrictOnDelete();
        });

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
        });

        Schema::create('cupones', function (Blueprint $table): void {
            $table->id();
            $table->string('codigo', 50)->unique();
            $table->string('tipo_descuento', 20);
            $table->decimal('valor', 10, 2);
            $table->decimal('minimo_compra', 10, 2)->default(0);
            $table->unsignedInteger('limite_usos')->nullable();
            $table->unsignedInteger('usos')->default(0);
            $table->dateTime('inicia_en');
            $table->dateTime('finaliza_en');
            $table->boolean('activo')->default(true)->index();
            $table->timestamps();
            $table->index(['activo', 'inicia_en', 'finaliza_en']);
        });

        Schema::create('segmentos_clientes', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre', 120)->unique();
            $table->string('tipo', 30);
            $table->json('criterios');
            $table->boolean('activo')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('campana_segmento', function (Blueprint $table): void {
            $table->unsignedInteger('campana_id');
            $table->unsignedBigInteger('segmento_id');
            $table->primary(['campana_id', 'segmento_id']);
            $table->foreign('campana_id')->references('id')->on('campanas_publicitarias')->cascadeOnDelete();
            $table->foreign('segmento_id')->references('id')->on('segmentos_clientes')->cascadeOnDelete();
        });

        Schema::create('eventos_campana', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('campana_id');
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('tipo', 20)->index();
            $table->string('sesion_hash', 64)->nullable()->index();
            $table->timestamp('creado_en')->useCurrent();
            $table->foreign('campana_id')->references('id')->on('campanas_publicitarias')->cascadeOnDelete();
            $table->index(['campana_id', 'tipo', 'creado_en']);
        });

        Schema::create('registros_auditoria', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('accion', 80);
            $table->string('entidad', 80)->index();
            $table->unsignedInteger('entidad_id')->nullable();
            $table->json('valores_anteriores')->nullable();
            $table->json('valores_nuevos')->nullable();
            $table->string('direccion_ip', 45)->nullable();
            $table->timestamp('creado_en')->useCurrent();
            $table->index(['entidad', 'entidad_id']);
        });

        Schema::create('mensajes_contacto', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('nombre', 120);
            $table->string('contacto', 160);
            $table->text('mensaje');
            $table->string('estado', 30)->default('Nuevo')->index();
            $table->timestamp('creado_en')->useCurrent();
        });
    }

    private function completeExistingSchema(): void
    {
        if (Schema::hasTable('pedidos')) {
            Schema::table('pedidos', function (Blueprint $table): void {
                if (! Schema::hasColumn('pedidos', 'subtotal')) {
                    $table->decimal('subtotal', 10, 2)->default(0)->after('total');
                }
                if (! Schema::hasColumn('pedidos', 'descuento')) {
                    $table->decimal('descuento', 10, 2)->default(0)->after('subtotal');
                }
                if (! Schema::hasColumn('pedidos', 'cupon_codigo')) {
                    $table->string('cupon_codigo', 50)->nullable()->after('descuento');
                }
            });
        }

        if (! Schema::hasTable('compra_detalles')) {
            Schema::create('compra_detalles', function (Blueprint $table): void {
                $table->id();
                $table->integer('compra_id');
                $table->integer('producto_id');
                $table->unsignedInteger('cantidad');
                $table->decimal('costo_unitario', 10, 2);
                $table->foreign('compra_id')->references('id')->on('compras')->cascadeOnDelete();
                $table->foreign('producto_id')->references('id')->on('productos')->restrictOnDelete();
            });
        } else {
            DB::statement('ALTER TABLE `compra_detalles` MODIFY `compra_id` INT NOT NULL, MODIFY `producto_id` INT NOT NULL');
            Schema::table('compra_detalles', function (Blueprint $table): void {
                if (! $this->foreignKeyExists('compra_detalles', 'compra_detalles_compra_id_foreign')) {
                    $table->foreign('compra_id')->references('id')->on('compras')->cascadeOnDelete();
                }
                if (! $this->foreignKeyExists('compra_detalles', 'compra_detalles_producto_id_foreign')) {
                    $table->foreign('producto_id')->references('id')->on('productos')->restrictOnDelete();
                }
            });
        }

        if (! Schema::hasTable('cotizacion_detalles')) {
            Schema::create('cotizacion_detalles', function (Blueprint $table): void {
                $table->id();
                $table->integer('cotizacion_id');
                $table->integer('producto_id');
                $table->unsignedInteger('cantidad');
                $table->decimal('precio_unitario', 10, 2);
                $table->foreign('cotizacion_id')->references('id')->on('cotizaciones')->cascadeOnDelete();
                $table->foreign('producto_id')->references('id')->on('productos')->restrictOnDelete();
            });
        }

        if (! Schema::hasTable('cupones')) {
            Schema::create('cupones', function (Blueprint $table): void {
                $table->id();
                $table->string('codigo', 50)->unique();
                $table->string('tipo_descuento', 20);
                $table->decimal('valor', 10, 2);
                $table->decimal('minimo_compra', 10, 2)->default(0);
                $table->unsignedInteger('limite_usos')->nullable();
                $table->unsignedInteger('usos')->default(0);
                $table->dateTime('inicia_en');
                $table->dateTime('finaliza_en');
                $table->boolean('activo')->default(true)->index();
                $table->timestamps();
                $table->index(['activo', 'inicia_en', 'finaliza_en']);
            });
        }

        if (! Schema::hasTable('segmentos_clientes')) {
            Schema::create('segmentos_clientes', function (Blueprint $table): void {
                $table->id();
                $table->string('nombre', 120)->unique();
                $table->string('tipo', 30);
                $table->json('criterios');
                $table->boolean('activo')->default(true)->index();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('campana_segmento')) {
            Schema::create('campana_segmento', function (Blueprint $table): void {
                $table->unsignedInteger('campana_id');
                $table->unsignedBigInteger('segmento_id');
                $table->primary(['campana_id', 'segmento_id']);
                $table->foreign('campana_id')->references('id')->on('campanas_publicitarias')->cascadeOnDelete();
                $table->foreign('segmento_id')->references('id')->on('segmentos_clientes')->cascadeOnDelete();
            });
        } else {
            DB::statement('ALTER TABLE `campana_segmento` MODIFY `campana_id` INT UNSIGNED NOT NULL');
            Schema::table('campana_segmento', function (Blueprint $table): void {
                if (! $this->foreignKeyExists('campana_segmento', 'campana_segmento_campana_id_foreign')) {
                    $table->foreign('campana_id')->references('id')->on('campanas_publicitarias')->cascadeOnDelete();
                }
                if (! $this->foreignKeyExists('campana_segmento', 'campana_segmento_segmento_id_foreign')) {
                    $table->foreign('segmento_id')->references('id')->on('segmentos_clientes')->cascadeOnDelete();
                }
            });
        }

        if (! Schema::hasTable('eventos_campana')) {
            Schema::create('eventos_campana', function (Blueprint $table): void {
                $table->id();
                $table->unsignedInteger('campana_id');
                $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('tipo', 20)->index();
                $table->string('sesion_hash', 64)->nullable()->index();
                $table->timestamp('creado_en')->useCurrent();
                $table->foreign('campana_id')->references('id')->on('campanas_publicitarias')->cascadeOnDelete();
                $table->index(['campana_id', 'tipo', 'creado_en']);
            });
        }
    }

    private function foreignKeyExists(string $table, string $constraint): bool
    {
        return DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $table)
            ->where('CONSTRAINT_NAME', $constraint)
            ->exists();
    }

    public function down(): void
    {
        foreach ([
            'mensajes_contacto',
            'registros_auditoria',
            'eventos_campana',
            'campana_segmento',
            'segmentos_clientes',
            'cupones',
            'campanas_publicitarias',
            'movimientos_inventario',
            'logistica_mayorista',
            'detalle_pedidos',
            'pedidos',
            'cotizacion_detalles',
            'cotizaciones',
            'compra_detalles',
            'compras',
            'producto_proveedor',
            'proveedores',
            'clientes',
            'perfiles_inteligentes_productos',
            'variante_imagenes',
            'producto_variantes',
            'producto_imagen_referencia',
            'producto_caracteristicas',
            'productos',
            'categorias',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta la operación up definida por la clase 2026_11_01_015108_create_logistics_returns_tables.
     *
     * @return void Resultado de la operación.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        if (! Schema::hasIndex('detalle_pedidos', ['id', 'pedido_id'], 'unique')) {
            Schema::table('detalle_pedidos', function (Blueprint $table): void {
                $table->unique(['id', 'pedido_id'], 'detalle_pedidos_id_pedido_unique');
            });
        }

        foreach (['guias_remision', 'guia_remision_detalles', 'envios_pedido', 'envio_pedido_detalles', 'eventos_envio_pedido', 'devolucion_detalles', 'reembolsos'] as $table) {
            if (Schema::hasTable($table)) {
                throw new RuntimeException('The enterprise table already exists: '.$table.'. Restore a clean copy before retrying.');
            }
        }

        DB::unprepared(<<<'SQL'
CREATE TABLE guias_remision (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  empresa_id BIGINT UNSIGNED NOT NULL,
  sede_id BIGINT UNSIGNED NOT NULL,
  almacen_origen_id BIGINT UNSIGNED NOT NULL,
  venta_id BIGINT UNSIGNED NULL,
  serie VARCHAR(8) NOT NULL,
  correlativo BIGINT UNSIGNED NOT NULL,
  motivo_traslado VARCHAR(80) NOT NULL,
  modalidad_traslado VARCHAR(30) NOT NULL,
  transportista_ruc CHAR(11) NULL,
  transportista_nombre VARCHAR(180) NULL,
  direccion_origen VARCHAR(255) NOT NULL,
  direccion_destino VARCHAR(255) NOT NULL,
  estado VARCHAR(30) NOT NULL DEFAULT 'borrador',
  emitida_en DATETIME(6) NULL,
  creado_por BIGINT UNSIGNED NULL,
  created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  PRIMARY KEY (id),
  UNIQUE KEY uq_guias_empresa_sede_serie_numero (empresa_id, sede_id, serie, correlativo),
  KEY ix_guias_sede_estado_fecha (sede_id, estado, created_at),
  CONSTRAINT fk_guias_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE RESTRICT,
  CONSTRAINT fk_guias_sede FOREIGN KEY (sede_id) REFERENCES sedes(id) ON DELETE RESTRICT,
  CONSTRAINT fk_guias_almacen FOREIGN KEY (almacen_origen_id) REFERENCES almacenes(id) ON DELETE RESTRICT,
  CONSTRAINT fk_guias_venta FOREIGN KEY (venta_id) REFERENCES ventas(id) ON DELETE RESTRICT,
  CONSTRAINT fk_guias_usuario FOREIGN KEY (creado_por) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);

        DB::unprepared(<<<'SQL'
CREATE TABLE guia_remision_detalles (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  guia_id BIGINT UNSIGNED NOT NULL,
  venta_detalle_id BIGINT UNSIGNED NULL,
  producto_id INT NOT NULL,
  variante_id INT NOT NULL,
  descripcion VARCHAR(255) NOT NULL,
  cantidad DECIMAL(18,6) NOT NULL,
  unidad_medida VARCHAR(12) NOT NULL DEFAULT 'NIU',
  PRIMARY KEY (id),
  KEY ix_guia_detalles_guia (guia_id),
  CONSTRAINT fk_guia_detalles_guia FOREIGN KEY (guia_id) REFERENCES guias_remision(id) ON DELETE RESTRICT,
  CONSTRAINT fk_guia_detalles_venta FOREIGN KEY (venta_detalle_id) REFERENCES venta_detalles(id) ON DELETE SET NULL,
  CONSTRAINT fk_guia_detalles_producto FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE RESTRICT,
  CONSTRAINT fk_guia_detalles_variante FOREIGN KEY (variante_id) REFERENCES producto_variantes(id) ON DELETE RESTRICT,
  CONSTRAINT ck_guia_detalles_cantidad CHECK (cantidad > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);

        DB::unprepared(<<<'SQL'
CREATE TABLE envios_pedido (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  empresa_id BIGINT UNSIGNED NOT NULL,
  sede_id BIGINT UNSIGNED NOT NULL,
  pedido_id INT NOT NULL,
  almacen_id BIGINT UNSIGNED NOT NULL,
  guia_remision_id BIGINT UNSIGNED NULL,
  codigo VARCHAR(60) NOT NULL,
  modalidad VARCHAR(30) NOT NULL,
  transportista VARCHAR(120) NULL,
  servicio VARCHAR(120) NULL,
  numero_seguimiento VARCHAR(160) NULL,
  estado VARCHAR(30) NOT NULL DEFAULT 'pendiente',
  moneda CHAR(3) NOT NULL DEFAULT 'PEN',
  costo_envio DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  destino_snapshot JSON NOT NULL,
  estimado_entrega_en DATETIME(6) NULL,
  despachado_en DATETIME(6) NULL,
  entregado_en DATETIME(6) NULL,
  creado_por BIGINT UNSIGNED NULL,
  creado_en DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  PRIMARY KEY (id),
  UNIQUE KEY uq_envios_pedido_codigo (empresa_id, codigo),
  UNIQUE KEY uq_envios_pedido_id_pedido (id, pedido_id),
  UNIQUE KEY uq_envios_pedido_guia (guia_remision_id),
  KEY ix_envios_pedido_estado_fecha (sede_id, estado, creado_en),
  KEY ix_envios_pedido_tracking (transportista, numero_seguimiento),
  CONSTRAINT fk_envios_pedido_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE RESTRICT,
  CONSTRAINT fk_envios_pedido_sede FOREIGN KEY (sede_id) REFERENCES sedes(id) ON DELETE RESTRICT,
  CONSTRAINT fk_envios_pedido_pedido FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE RESTRICT,
  CONSTRAINT fk_envios_pedido_almacen FOREIGN KEY (almacen_id) REFERENCES almacenes(id) ON DELETE RESTRICT,
  CONSTRAINT fk_envios_pedido_guia FOREIGN KEY (guia_remision_id) REFERENCES guias_remision(id) ON DELETE RESTRICT,
  CONSTRAINT fk_envios_pedido_usuario FOREIGN KEY (creado_por) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT ck_envios_pedido_costos CHECK (costo_envio >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);

        DB::unprepared(<<<'SQL'
CREATE TABLE envio_pedido_detalles (
  envio_pedido_id BIGINT UNSIGNED NOT NULL,
  detalle_pedido_id INT NOT NULL,
  pedido_id INT NOT NULL,
  cantidad DECIMAL(18,6) NOT NULL,
  PRIMARY KEY (envio_pedido_id, detalle_pedido_id),
  KEY ix_envio_pedido_detalle_pedido (detalle_pedido_id, pedido_id),
  CONSTRAINT fk_envio_detalles_envio FOREIGN KEY (envio_pedido_id, pedido_id) REFERENCES envios_pedido(id, pedido_id) ON DELETE RESTRICT,
  CONSTRAINT fk_envio_detalles_pedido_detalle FOREIGN KEY (detalle_pedido_id, pedido_id) REFERENCES detalle_pedidos(id, pedido_id) ON DELETE RESTRICT,
  CONSTRAINT ck_envio_detalles_cantidad CHECK (cantidad > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);

        DB::unprepared(<<<'SQL'
CREATE TABLE eventos_envio_pedido (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  envio_pedido_id BIGINT UNSIGNED NOT NULL,
  estado VARCHAR(30) NOT NULL,
  descripcion VARCHAR(500) NULL,
  fuente VARCHAR(30) NOT NULL DEFAULT 'interno',
  identificador_externo VARCHAR(160) NULL,
  ocurrido_en DATETIME(6) NOT NULL,
  registrado_en DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  datos_seguros JSON NULL COMMENT 'Payload saneado del transportista; excluir secretos y datos personales innecesarios',
  PRIMARY KEY (id),
  UNIQUE KEY uq_eventos_envio_fuente_externo (envio_pedido_id, fuente, identificador_externo),
  KEY ix_eventos_envio_fecha (envio_pedido_id, ocurrido_en),
  CONSTRAINT fk_eventos_envio_pedido FOREIGN KEY (envio_pedido_id) REFERENCES envios_pedido(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);

        DB::unprepared(<<<'SQL'
CREATE TABLE devolucion_detalles (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  devolucion_id INT UNSIGNED NOT NULL,
  venta_detalle_id BIGINT UNSIGNED NULL,
  producto_id INT NOT NULL,
  variante_id INT NOT NULL,
  dispositivo_id BIGINT UNSIGNED NULL,
  cantidad DECIMAL(18,6) NOT NULL,
  estado_revision VARCHAR(40) NULL,
  PRIMARY KEY (id),
  KEY ix_devolucion_detalles_devolucion (devolucion_id),
  KEY ix_devolucion_detalles_dispositivo (dispositivo_id),
  CONSTRAINT fk_devolucion_detalles_devolucion FOREIGN KEY (devolucion_id) REFERENCES devoluciones_garantias(id) ON DELETE RESTRICT,
  CONSTRAINT fk_devolucion_detalles_venta FOREIGN KEY (venta_detalle_id) REFERENCES venta_detalles(id) ON DELETE RESTRICT,
  CONSTRAINT fk_devolucion_detalles_producto FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE RESTRICT,
  CONSTRAINT fk_devolucion_detalles_variante FOREIGN KEY (variante_id) REFERENCES producto_variantes(id) ON DELETE RESTRICT,
  CONSTRAINT fk_devolucion_detalles_dispositivo FOREIGN KEY (dispositivo_id) REFERENCES dispositivos(id) ON DELETE RESTRICT,
  CONSTRAINT ck_devolucion_detalles_cantidad CHECK (cantidad > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);

        DB::unprepared(<<<'SQL'
CREATE TABLE reembolsos (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  devolucion_id INT UNSIGNED NOT NULL,
  pago_id BIGINT UNSIGNED NOT NULL,
  importe DECIMAL(15,2) NOT NULL,
  estado VARCHAR(30) NOT NULL DEFAULT 'pendiente',
  autorizado_por BIGINT UNSIGNED NULL,
  creado_en DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  PRIMARY KEY (id),
  UNIQUE KEY uq_reembolsos_pago (pago_id),
  KEY ix_reembolsos_devolucion (devolucion_id, estado),
  CONSTRAINT fk_reembolsos_devolucion FOREIGN KEY (devolucion_id) REFERENCES devoluciones_garantias(id) ON DELETE RESTRICT,
  CONSTRAINT fk_reembolsos_pago FOREIGN KEY (pago_id) REFERENCES pagos(id) ON DELETE RESTRICT,
  CONSTRAINT fk_reembolsos_autorizador FOREIGN KEY (autorizado_por) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT ck_reembolsos_importe CHECK (importe > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);

    }

    /**
     * Ejecuta la operación down definida por la clase 2026_11_01_015108_create_logistics_returns_tables.
     *
     * @return void Resultado de la operación.
     */
    public function down(): void
    {
        throw new LogicException('Enterprise tables are forward-only to protect business data.');
    }
};

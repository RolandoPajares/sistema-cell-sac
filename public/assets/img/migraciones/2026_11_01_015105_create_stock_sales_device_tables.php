<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta la operación up definida por la clase 2026_11_01_015105_create_stock_sales_device_tables.
     *
     * @return void Resultado de la operación.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        foreach (['existencias_almacen', 'transferencias_stock', 'transferencia_stock_detalles', 'ventas', 'venta_detalles', 'dispositivos', 'compra_detalle_dispositivos', 'venta_detalle_dispositivos', 'dispositivo_movimientos'] as $table) {
            if (Schema::hasTable($table)) {
                throw new RuntimeException('The enterprise table already exists: '.$table.'. Restore a clean copy before retrying.');
            }
        }

        DB::unprepared(<<<'SQL'
CREATE TABLE existencias_almacen (
  almacen_id BIGINT UNSIGNED NOT NULL,
  variante_id INT NOT NULL,
  cantidad_fisica DECIMAL(18,6) NOT NULL DEFAULT 0.000000,
  cantidad_reservada DECIMAL(18,6) NOT NULL DEFAULT 0.000000,
  actualizado_en DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6),
  PRIMARY KEY (almacen_id, variante_id),
  KEY ix_existencias_variante_almacen (variante_id, almacen_id),
  CONSTRAINT fk_existencias_almacen FOREIGN KEY (almacen_id) REFERENCES almacenes(id) ON DELETE RESTRICT,
  CONSTRAINT fk_existencias_variante FOREIGN KEY (variante_id) REFERENCES producto_variantes(id) ON DELETE RESTRICT,
  CONSTRAINT ck_existencias_no_negativas CHECK (cantidad_fisica >= 0 AND cantidad_reservada >= 0 AND cantidad_reservada <= cantidad_fisica)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);

        DB::unprepared(<<<'SQL'
CREATE TABLE transferencias_stock (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  empresa_id BIGINT UNSIGNED NOT NULL,
  sede_origen_id BIGINT UNSIGNED NOT NULL,
  almacen_origen_id BIGINT UNSIGNED NOT NULL,
  sede_destino_id BIGINT UNSIGNED NOT NULL,
  almacen_destino_id BIGINT UNSIGNED NOT NULL,
  codigo VARCHAR(50) NOT NULL,
  estado VARCHAR(30) NOT NULL DEFAULT 'borrador',
  solicitado_por BIGINT UNSIGNED NULL,
  aprobado_por BIGINT UNSIGNED NULL,
  enviado_en DATETIME(6) NULL,
  recibido_en DATETIME(6) NULL,
  creado_en DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  PRIMARY KEY (id),
  UNIQUE KEY uq_transferencias_empresa_codigo (empresa_id, codigo),
  KEY ix_transferencias_origen_estado (sede_origen_id, estado, creado_en),
  KEY ix_transferencias_destino_estado (sede_destino_id, estado, creado_en),
  CONSTRAINT fk_transferencias_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE RESTRICT,
  CONSTRAINT fk_transferencias_sede_origen FOREIGN KEY (sede_origen_id) REFERENCES sedes(id) ON DELETE RESTRICT,
  CONSTRAINT fk_transferencias_almacen_origen FOREIGN KEY (almacen_origen_id) REFERENCES almacenes(id) ON DELETE RESTRICT,
  CONSTRAINT fk_transferencias_sede_destino FOREIGN KEY (sede_destino_id) REFERENCES sedes(id) ON DELETE RESTRICT,
  CONSTRAINT fk_transferencias_almacen_destino FOREIGN KEY (almacen_destino_id) REFERENCES almacenes(id) ON DELETE RESTRICT,
  CONSTRAINT fk_transferencias_solicitante FOREIGN KEY (solicitado_por) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT fk_transferencias_aprobador FOREIGN KEY (aprobado_por) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT ck_transferencias_almacenes_distintos CHECK (almacen_origen_id <> almacen_destino_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);

        DB::unprepared(<<<'SQL'
CREATE TABLE transferencia_stock_detalles (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  transferencia_id BIGINT UNSIGNED NOT NULL,
  producto_id INT NOT NULL,
  variante_id INT NOT NULL,
  cantidad_solicitada DECIMAL(18,6) NOT NULL,
  cantidad_enviada DECIMAL(18,6) NOT NULL DEFAULT 0.000000,
  cantidad_recibida DECIMAL(18,6) NOT NULL DEFAULT 0.000000,
  PRIMARY KEY (id),
  UNIQUE KEY uq_transferencia_detalle_variante (transferencia_id, variante_id),
  KEY ix_transferencia_detalle_producto (producto_id, variante_id),
  CONSTRAINT fk_transferencia_detalle_transferencia FOREIGN KEY (transferencia_id) REFERENCES transferencias_stock(id) ON DELETE RESTRICT,
  CONSTRAINT fk_transferencia_detalle_producto FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE RESTRICT,
  CONSTRAINT fk_transferencia_detalle_variante FOREIGN KEY (variante_id) REFERENCES producto_variantes(id) ON DELETE RESTRICT,
  CONSTRAINT ck_transferencia_detalle_cantidades CHECK (cantidad_solicitada > 0 AND cantidad_enviada >= 0 AND cantidad_recibida >= 0 AND cantidad_recibida <= cantidad_enviada)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);

        DB::unprepared(<<<'SQL'
CREATE TABLE ventas (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  empresa_id BIGINT UNSIGNED NOT NULL,
  sede_id BIGINT UNSIGNED NOT NULL,
  almacen_id BIGINT UNSIGNED NOT NULL,
  pedido_id INT NULL,
  cliente_id INT NULL,
  usuario_id BIGINT UNSIGNED NULL,
  sesion_caja_id BIGINT UNSIGNED NULL,
  codigo VARCHAR(50) NOT NULL,
  estado VARCHAR(30) NOT NULL DEFAULT 'borrador',
  moneda CHAR(3) NOT NULL DEFAULT 'PEN',
  subtotal DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  descuento DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  impuestos DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  total DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  fecha_venta DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  updated_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6),
  PRIMARY KEY (id),
  UNIQUE KEY uq_ventas_empresa_codigo (empresa_id, codigo),
  UNIQUE KEY uq_ventas_pedido (pedido_id),
  KEY ix_ventas_sede_estado_fecha (sede_id, estado, fecha_venta),
  KEY ix_ventas_cliente_fecha (cliente_id, fecha_venta),
  KEY ix_ventas_caja_fecha (sesion_caja_id, fecha_venta),
  CONSTRAINT fk_ventas_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE RESTRICT,
  CONSTRAINT fk_ventas_sede FOREIGN KEY (sede_id) REFERENCES sedes(id) ON DELETE RESTRICT,
  CONSTRAINT fk_ventas_almacen FOREIGN KEY (almacen_id) REFERENCES almacenes(id) ON DELETE RESTRICT,
  CONSTRAINT fk_ventas_pedido FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE RESTRICT,
  CONSTRAINT fk_ventas_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE RESTRICT,
  CONSTRAINT fk_ventas_usuario FOREIGN KEY (usuario_id) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT ck_ventas_totales CHECK (subtotal >= 0 AND descuento >= 0 AND impuestos >= 0 AND total >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);

        DB::unprepared(<<<'SQL'
CREATE TABLE venta_detalles (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  venta_id BIGINT UNSIGNED NOT NULL,
  producto_id INT NOT NULL,
  variante_id INT NOT NULL,
  detalle_pedido_id INT NULL,
  descripcion_snapshot VARCHAR(255) NOT NULL,
  sku_snapshot VARCHAR(80) NOT NULL,
  cantidad DECIMAL(18,6) NOT NULL,
  precio_unitario DECIMAL(15,2) NOT NULL,
  descuento DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  tasa_impuesto DECIMAL(7,4) NOT NULL DEFAULT 0.0000,
  impuesto DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  total_linea DECIMAL(15,2) NOT NULL,
  PRIMARY KEY (id),
  KEY ix_venta_detalles_venta (venta_id),
  KEY ix_venta_detalles_producto_variante (producto_id, variante_id),
  UNIQUE KEY uq_venta_detalles_pedido_linea (detalle_pedido_id),
  KEY ix_venta_detalles_pedido_detalle (detalle_pedido_id),
  CONSTRAINT fk_venta_detalles_venta FOREIGN KEY (venta_id) REFERENCES ventas(id) ON DELETE RESTRICT,
  CONSTRAINT fk_venta_detalles_producto FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE RESTRICT,
  CONSTRAINT fk_venta_detalles_variante FOREIGN KEY (variante_id) REFERENCES producto_variantes(id) ON DELETE RESTRICT,
  CONSTRAINT fk_venta_detalles_pedido_detalle FOREIGN KEY (detalle_pedido_id) REFERENCES detalle_pedidos(id) ON DELETE RESTRICT,
  CONSTRAINT ck_venta_detalles_importes CHECK (cantidad > 0 AND precio_unitario >= 0 AND descuento >= 0 AND tasa_impuesto >= 0 AND impuesto >= 0 AND total_linea >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);

        DB::unprepared(<<<'SQL'
CREATE TABLE dispositivos (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  variante_id INT NOT NULL,
  imei VARCHAR(20) NULL,
  numero_serie VARCHAR(100) NULL,
  compra_detalle_id BIGINT UNSIGNED NULL,
  almacen_actual_id BIGINT UNSIGNED NULL,
  estado VARCHAR(30) NOT NULL DEFAULT 'disponible',
  fecha_ingreso DATETIME(6) NULL,
  fecha_salida DATETIME(6) NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_dispositivos_imei (imei),
  UNIQUE KEY uq_dispositivos_serie (numero_serie),
  KEY ix_dispositivos_variante_estado (variante_id, estado),
  KEY ix_dispositivos_almacen_estado (almacen_actual_id, estado),
  CONSTRAINT fk_dispositivos_variante FOREIGN KEY (variante_id) REFERENCES producto_variantes(id) ON DELETE RESTRICT,
  CONSTRAINT fk_dispositivos_compra_detalle FOREIGN KEY (compra_detalle_id) REFERENCES compra_detalles(id) ON DELETE RESTRICT,
  CONSTRAINT fk_dispositivos_almacen FOREIGN KEY (almacen_actual_id) REFERENCES almacenes(id) ON DELETE RESTRICT,
  CONSTRAINT ck_dispositivos_identificador CHECK (imei IS NOT NULL OR numero_serie IS NOT NULL)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);

        DB::unprepared(<<<'SQL'
CREATE TABLE compra_detalle_dispositivos (
  compra_detalle_id BIGINT UNSIGNED NOT NULL,
  dispositivo_id BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (compra_detalle_id, dispositivo_id),
  UNIQUE KEY uq_compra_dispositivo (dispositivo_id),
  CONSTRAINT fk_compra_dispositivo_detalle FOREIGN KEY (compra_detalle_id) REFERENCES compra_detalles(id) ON DELETE RESTRICT,
  CONSTRAINT fk_compra_dispositivo_dispositivo FOREIGN KEY (dispositivo_id) REFERENCES dispositivos(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);

        DB::unprepared(<<<'SQL'
CREATE TABLE venta_detalle_dispositivos (
  venta_detalle_id BIGINT UNSIGNED NOT NULL,
  dispositivo_id BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (venta_detalle_id, dispositivo_id),
  UNIQUE KEY uq_venta_dispositivo (dispositivo_id),
  CONSTRAINT fk_venta_dispositivo_detalle FOREIGN KEY (venta_detalle_id) REFERENCES venta_detalles(id) ON DELETE RESTRICT,
  CONSTRAINT fk_venta_dispositivo_dispositivo FOREIGN KEY (dispositivo_id) REFERENCES dispositivos(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);

        DB::unprepared(<<<'SQL'
CREATE TABLE dispositivo_movimientos (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  dispositivo_id BIGINT UNSIGNED NOT NULL,
  movimiento_inventario_id BIGINT UNSIGNED NOT NULL,
  estado_anterior VARCHAR(30) NULL,
  estado_nuevo VARCHAR(30) NOT NULL,
  creado_en DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  PRIMARY KEY (id),
  KEY ix_dispositivo_movimientos_dispositivo_fecha (dispositivo_id, creado_en),
  UNIQUE KEY uq_dispositivo_movimiento_stock (movimiento_inventario_id, dispositivo_id),
  CONSTRAINT fk_dispositivo_movimientos_dispositivo FOREIGN KEY (dispositivo_id) REFERENCES dispositivos(id) ON DELETE RESTRICT,
  CONSTRAINT fk_dispositivo_movimientos_stock FOREIGN KEY (movimiento_inventario_id) REFERENCES movimientos_inventario(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);

    }

    /**
     * Ejecuta la operación down definida por la clase 2026_11_01_015105_create_stock_sales_device_tables.
     *
     * @return void Resultado de la operación.
     */
    public function down(): void
    {
        throw new LogicException('Enterprise tables are forward-only to protect business data.');
    }
};

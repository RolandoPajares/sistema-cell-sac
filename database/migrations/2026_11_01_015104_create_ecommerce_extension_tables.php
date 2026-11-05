<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        foreach (['direcciones_cliente', 'carritos', 'carrito_detalles'] as $table) {
            if (Schema::hasTable($table)) {
                throw new RuntimeException('The enterprise table already exists: '.$table.'. Restore a clean copy before retrying.');
            }
        }

        DB::unprepared(<<<'SQL'
CREATE TABLE direcciones_cliente (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  cliente_id INT NOT NULL,
  alias VARCHAR(80) NOT NULL DEFAULT 'Principal',
  destinatario VARCHAR(160) NOT NULL,
  telefono VARCHAR(30) NULL,
  pais CHAR(2) NOT NULL DEFAULT 'PE',
  departamento VARCHAR(100) NULL,
  provincia VARCHAR(100) NULL,
  distrito VARCHAR(100) NULL,
  codigo_postal VARCHAR(20) NULL,
  direccion_linea1 VARCHAR(255) NOT NULL,
  direccion_linea2 VARCHAR(255) NULL,
  referencia VARCHAR(500) NULL,
  es_predeterminada BOOLEAN NOT NULL DEFAULT FALSE,
  activa BOOLEAN NOT NULL DEFAULT TRUE,
  creado_en DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  actualizado_en DATETIME(6) NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_direcciones_cliente_id (cliente_id, id),
  KEY ix_direcciones_cliente_activa (cliente_id, activa, es_predeterminada),
  CONSTRAINT fk_direcciones_cliente_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);

        DB::unprepared(<<<'SQL'
CREATE TABLE carritos (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  empresa_id BIGINT UNSIGNED NOT NULL,
  sede_id BIGINT UNSIGNED NULL,
  usuario_id BIGINT UNSIGNED NULL,
  cliente_id INT NULL,
  token_hash CHAR(64) NULL,
  pedido_id INT NULL,
  estado VARCHAR(20) NOT NULL DEFAULT 'activo',
  moneda CHAR(3) NOT NULL DEFAULT 'PEN',
  expira_en DATETIME(6) NULL,
  creado_en DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  actualizado_en DATETIME(6) NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_carritos_token_hash (token_hash),
  UNIQUE KEY uq_carritos_pedido (pedido_id),
  KEY ix_carritos_usuario_estado (usuario_id, estado, actualizado_en),
  KEY ix_carritos_cliente_estado (cliente_id, estado, actualizado_en),
  CONSTRAINT fk_carritos_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE RESTRICT,
  CONSTRAINT fk_carritos_sede FOREIGN KEY (sede_id) REFERENCES sedes(id) ON DELETE RESTRICT,
  CONSTRAINT fk_carritos_usuario FOREIGN KEY (usuario_id) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT fk_carritos_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL,
  CONSTRAINT fk_carritos_pedido FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE RESTRICT,
  CONSTRAINT ck_carritos_propietario CHECK (usuario_id IS NOT NULL OR cliente_id IS NOT NULL OR token_hash IS NOT NULL)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);

        DB::unprepared(<<<'SQL'
CREATE TABLE carrito_detalles (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  carrito_id BIGINT UNSIGNED NOT NULL,
  variante_id INT NOT NULL,
  cantidad DECIMAL(18,6) NOT NULL,
  precio_mostrado_snapshot DECIMAL(15,2) NULL COMMENT 'Informativo; el precio se vuelve a validar al confirmar el pedido',
  agregado_en DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  actualizado_en DATETIME(6) NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_carrito_detalles_carrito_variante (carrito_id, variante_id),
  CONSTRAINT fk_carrito_detalles_carrito FOREIGN KEY (carrito_id) REFERENCES carritos(id) ON DELETE CASCADE,
  CONSTRAINT fk_carrito_detalles_variante FOREIGN KEY (variante_id) REFERENCES producto_variantes(id) ON DELETE RESTRICT,
  CONSTRAINT ck_carrito_detalles_cantidad CHECK (cantidad > 0 AND (precio_mostrado_snapshot IS NULL OR precio_mostrado_snapshot >= 0))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);

    }

    public function down(): void
    {
        throw new LogicException('Enterprise tables are forward-only to protect business data.');
    }
};

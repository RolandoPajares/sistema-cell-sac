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

        foreach (['tipos_documento_electronico', 'series_documentos', 'documentos_electronicos', 'documento_electronico_detalles', 'envios_sunat'] as $table) {
            if (Schema::hasTable($table)) {
                throw new RuntimeException('The enterprise table already exists: '.$table.'. Restore a clean copy before retrying.');
            }
        }

        DB::unprepared(<<<'SQL'
CREATE TABLE tipos_documento_electronico (
  codigo CHAR(2) NOT NULL,
  nombre VARCHAR(120) NOT NULL,
  requiere_documento_referencia BOOLEAN NOT NULL DEFAULT FALSE,
  activo BOOLEAN NOT NULL DEFAULT TRUE,
  PRIMARY KEY (codigo),
  KEY ix_tipos_documento_activo (activo, nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);

        DB::unprepared(<<<'SQL'
CREATE TABLE series_documentos (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  empresa_id BIGINT UNSIGNED NOT NULL,
  sede_id BIGINT UNSIGNED NOT NULL,
  tipo_documento CHAR(2) NOT NULL,
  serie VARCHAR(8) NOT NULL,
  siguiente_correlativo BIGINT UNSIGNED NOT NULL DEFAULT 1,
  activa BOOLEAN NOT NULL DEFAULT TRUE,
  created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  updated_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6),
  PRIMARY KEY (id),
  UNIQUE KEY uq_series_doc (empresa_id, sede_id, tipo_documento, serie),
  KEY ix_series_doc_activa (sede_id, activa, tipo_documento),
  CONSTRAINT fk_series_documentos_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE RESTRICT,
  CONSTRAINT fk_series_documentos_sede FOREIGN KEY (sede_id) REFERENCES sedes(id) ON DELETE RESTRICT,
  CONSTRAINT fk_series_documentos_tipo FOREIGN KEY (tipo_documento) REFERENCES tipos_documento_electronico(codigo) ON DELETE RESTRICT,
  CONSTRAINT ck_series_correlativo CHECK (siguiente_correlativo > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);

        DB::unprepared(<<<'SQL'
CREATE TABLE documentos_electronicos (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  empresa_id BIGINT UNSIGNED NOT NULL,
  sede_id BIGINT UNSIGNED NOT NULL,
  serie_documento_id BIGINT UNSIGNED NOT NULL,
  venta_id BIGINT UNSIGNED NULL,
  documento_referencia_id BIGINT UNSIGNED NULL,
  tipo_documento CHAR(2) NOT NULL,
  serie VARCHAR(8) NOT NULL,
  correlativo BIGINT UNSIGNED NOT NULL,
  estado VARCHAR(30) NOT NULL DEFAULT 'borrador',
  moneda CHAR(3) NOT NULL DEFAULT 'PEN',
  emisor_snapshot JSON NOT NULL,
  receptor_snapshot JSON NOT NULL,
  subtotal DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  impuestos DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  total DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  fecha_emision DATETIME(6) NOT NULL,
  creado_por BIGINT UNSIGNED NULL,
  created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  PRIMARY KEY (id),
  UNIQUE KEY uq_documento_electronico_numero (empresa_id, tipo_documento, serie, correlativo),
  KEY ix_documentos_electronicos_sede_estado_fecha (sede_id, estado, fecha_emision),
  KEY ix_documentos_electronicos_venta (venta_id),
  KEY ix_documentos_electronicos_referencia (documento_referencia_id),
  CONSTRAINT fk_documentos_electronicos_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE RESTRICT,
  CONSTRAINT fk_documentos_electronicos_sede FOREIGN KEY (sede_id) REFERENCES sedes(id) ON DELETE RESTRICT,
  CONSTRAINT fk_documentos_electronicos_serie FOREIGN KEY (serie_documento_id) REFERENCES series_documentos(id) ON DELETE RESTRICT,
  CONSTRAINT fk_documentos_electronicos_tipo FOREIGN KEY (tipo_documento) REFERENCES tipos_documento_electronico(codigo) ON DELETE RESTRICT,
  CONSTRAINT fk_documentos_electronicos_venta FOREIGN KEY (venta_id) REFERENCES ventas(id) ON DELETE RESTRICT,
  CONSTRAINT fk_documentos_electronicos_referencia FOREIGN KEY (documento_referencia_id) REFERENCES documentos_electronicos(id) ON DELETE RESTRICT,
  CONSTRAINT fk_documentos_electronicos_usuario FOREIGN KEY (creado_por) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT ck_documentos_electronicos_importes CHECK (subtotal >= 0 AND impuestos >= 0 AND total >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);

        DB::unprepared(<<<'SQL'
CREATE TABLE documento_electronico_detalles (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  documento_id BIGINT UNSIGNED NOT NULL,
  venta_detalle_id BIGINT UNSIGNED NULL,
  codigo_producto VARCHAR(100) NULL,
  descripcion VARCHAR(255) NOT NULL,
  cantidad DECIMAL(18,6) NOT NULL,
  unidad_medida VARCHAR(12) NOT NULL DEFAULT 'NIU',
  valor_unitario DECIMAL(15,6) NOT NULL,
  precio_unitario DECIMAL(15,6) NOT NULL,
  tasa_impuesto DECIMAL(7,4) NOT NULL DEFAULT 0.0000,
  impuesto DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  total_linea DECIMAL(15,2) NOT NULL,
  PRIMARY KEY (id),
  KEY ix_documento_detalles_documento (documento_id),
  KEY ix_documento_detalles_venta_detalle (venta_detalle_id),
  CONSTRAINT fk_documento_detalles_documento FOREIGN KEY (documento_id) REFERENCES documentos_electronicos(id) ON DELETE RESTRICT,
  CONSTRAINT fk_documento_detalles_venta_detalle FOREIGN KEY (venta_detalle_id) REFERENCES venta_detalles(id) ON DELETE SET NULL,
  CONSTRAINT ck_documento_detalles_importes CHECK (cantidad > 0 AND valor_unitario >= 0 AND precio_unitario >= 0 AND tasa_impuesto >= 0 AND impuesto >= 0 AND total_linea >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);

        DB::unprepared(<<<'SQL'
CREATE TABLE envios_sunat (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  documento_id BIGINT UNSIGNED NOT NULL,
  numero_intento SMALLINT UNSIGNED NOT NULL,
  estado VARCHAR(30) NOT NULL,
  codigo_respuesta VARCHAR(40) NULL,
  descripcion_respuesta VARCHAR(2000) NULL,
  hash_solicitud CHAR(64) NULL,
  hash_respuesta CHAR(64) NULL,
  enviado_en DATETIME(6) NULL,
  respondido_en DATETIME(6) NULL,
  creado_en DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  PRIMARY KEY (id),
  UNIQUE KEY uq_envios_sunat_intento (documento_id, numero_intento),
  KEY ix_envios_sunat_estado_fecha (estado, creado_en),
  CONSTRAINT fk_envios_sunat_documento FOREIGN KEY (documento_id) REFERENCES documentos_electronicos(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);

    }

    public function down(): void
    {
        throw new LogicException('Enterprise tables are forward-only to protect business data.');
    }
};

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

        foreach (['tickets_soporte', 'ticket_eventos', 'contratos', 'archivos_documentales', 'integraciones', 'eventos_integracion'] as $table) {
            if (Schema::hasTable($table)) {
                throw new RuntimeException('The enterprise table already exists: '.$table.'. Restore a clean copy before retrying.');
            }
        }

        DB::unprepared(<<<'SQL'
CREATE TABLE tickets_soporte (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  empresa_id BIGINT UNSIGNED NOT NULL,
  sede_id BIGINT UNSIGNED NULL,
  cliente_id INT NULL,
  asignado_a BIGINT UNSIGNED NULL,
  codigo VARCHAR(50) NOT NULL,
  asunto VARCHAR(180) NOT NULL,
  estado VARCHAR(30) NOT NULL DEFAULT 'abierto',
  prioridad VARCHAR(20) NOT NULL DEFAULT 'normal',
  creado_en DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  actualizado_en DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6),
  PRIMARY KEY (id),
  UNIQUE KEY uq_tickets_empresa_codigo (empresa_id, codigo),
  KEY ix_tickets_sede_estado_prioridad (sede_id, estado, prioridad),
  KEY ix_tickets_asignado_estado (asignado_a, estado),
  CONSTRAINT fk_tickets_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE RESTRICT,
  CONSTRAINT fk_tickets_sede FOREIGN KEY (sede_id) REFERENCES sedes(id) ON DELETE RESTRICT,
  CONSTRAINT fk_tickets_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL,
  CONSTRAINT fk_tickets_asignado FOREIGN KEY (asignado_a) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);

        DB::unprepared(<<<'SQL'
CREATE TABLE ticket_eventos (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  ticket_id BIGINT UNSIGNED NOT NULL,
  usuario_id BIGINT UNSIGNED NULL,
  tipo VARCHAR(40) NOT NULL,
  contenido TEXT NULL,
  creado_en DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  PRIMARY KEY (id),
  KEY ix_ticket_eventos_ticket_fecha (ticket_id, creado_en),
  CONSTRAINT fk_ticket_eventos_ticket FOREIGN KEY (ticket_id) REFERENCES tickets_soporte(id) ON DELETE RESTRICT,
  CONSTRAINT fk_ticket_eventos_usuario FOREIGN KEY (usuario_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);

        DB::unprepared(<<<'SQL'
CREATE TABLE contratos (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  empresa_id BIGINT UNSIGNED NOT NULL,
  sede_id BIGINT UNSIGNED NULL,
  cliente_id INT NULL,
  proveedor_id INT NULL,
  codigo VARCHAR(60) NOT NULL,
  tipo VARCHAR(60) NOT NULL,
  estado VARCHAR(30) NOT NULL DEFAULT 'borrador',
  inicia_en DATE NULL,
  finaliza_en DATE NULL,
  creado_por BIGINT UNSIGNED NULL,
  creado_en DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  PRIMARY KEY (id),
  UNIQUE KEY uq_contratos_empresa_codigo (empresa_id, codigo),
  KEY ix_contratos_empresa_estado_vigencia (empresa_id, estado, finaliza_en),
  CONSTRAINT fk_contratos_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE RESTRICT,
  CONSTRAINT fk_contratos_sede FOREIGN KEY (sede_id) REFERENCES sedes(id) ON DELETE RESTRICT,
  CONSTRAINT fk_contratos_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE RESTRICT,
  CONSTRAINT fk_contratos_proveedor FOREIGN KEY (proveedor_id) REFERENCES proveedores(id) ON DELETE RESTRICT,
  CONSTRAINT fk_contratos_usuario FOREIGN KEY (creado_por) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT ck_contratos_contraparte CHECK ((cliente_id IS NOT NULL) <> (proveedor_id IS NOT NULL)),
  CONSTRAINT ck_contratos_fechas CHECK (finaliza_en IS NULL OR inicia_en IS NULL OR finaliza_en >= inicia_en)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);

        DB::unprepared(<<<'SQL'
CREATE TABLE archivos_documentales (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  documento_electronico_id BIGINT UNSIGNED NULL,
  tipo_archivo VARCHAR(50) NOT NULL DEFAULT 'adjunto' COMMENT 'sunat_xml_firmado, sunat_cdr, representacion_impresa, contrato, guia, evidencia, adjunto',
  contrato_id BIGINT UNSIGNED NULL,
  devolucion_id INT UNSIGNED NULL,
  guia_id BIGINT UNSIGNED NULL,
  nombre_original VARCHAR(255) NOT NULL,
  ruta_storage VARCHAR(700) NOT NULL,
  mime_type VARCHAR(150) NULL,
  tamano_bytes BIGINT UNSIGNED NULL,
  sha256 CHAR(64) NULL,
  cargado_por BIGINT UNSIGNED NULL,
  creado_en DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  PRIMARY KEY (id),
  KEY ix_archivos_documentales_documento (documento_electronico_id),
  KEY ix_archivos_documentales_contrato (contrato_id),
  KEY ix_archivos_documentales_devolucion (devolucion_id),
  KEY ix_archivos_documentales_guia (guia_id),
  CONSTRAINT fk_archivos_doc_electronico FOREIGN KEY (documento_electronico_id) REFERENCES documentos_electronicos(id) ON DELETE RESTRICT,
  CONSTRAINT fk_archivos_doc_contrato FOREIGN KEY (contrato_id) REFERENCES contratos(id) ON DELETE RESTRICT,
  CONSTRAINT fk_archivos_doc_devolucion FOREIGN KEY (devolucion_id) REFERENCES devoluciones_garantias(id) ON DELETE RESTRICT,
  CONSTRAINT fk_archivos_doc_guia FOREIGN KEY (guia_id) REFERENCES guias_remision(id) ON DELETE RESTRICT,
  CONSTRAINT fk_archivos_doc_usuario FOREIGN KEY (cargado_por) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT ck_archivos_doc_un_propietario CHECK ((documento_electronico_id IS NOT NULL) + (contrato_id IS NOT NULL) + (devolucion_id IS NOT NULL) + (guia_id IS NOT NULL) = 1)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);

        DB::unprepared(<<<'SQL'
CREATE TABLE integraciones (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  empresa_id BIGINT UNSIGNED NOT NULL,
  codigo VARCHAR(80) NOT NULL,
  nombre VARCHAR(140) NOT NULL,
  proveedor VARCHAR(100) NOT NULL,
  activa BOOLEAN NOT NULL DEFAULT FALSE,
  referencia_secreto VARCHAR(255) NULL COMMENT 'Identificador en gestor de secretos; no guardar tokens ni credenciales',
  configuracion_publica JSON NULL,
  created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  updated_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6),
  PRIMARY KEY (id),
  UNIQUE KEY uq_integraciones_empresa_codigo (empresa_id, codigo),
  KEY ix_integraciones_empresa_activa (empresa_id, activa),
  CONSTRAINT fk_integraciones_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);

        DB::unprepared(<<<'SQL'
CREATE TABLE eventos_integracion (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  integracion_id BIGINT UNSIGNED NOT NULL,
  correlacion_id VARCHAR(100) NULL,
  direccion ENUM('entrada','salida') NOT NULL,
  estado VARCHAR(30) NOT NULL,
  codigo_http SMALLINT UNSIGNED NULL,
  hash_solicitud CHAR(64) NULL,
  hash_respuesta CHAR(64) NULL,
  detalle_seguro JSON NULL,
  intentos SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  iniciado_en DATETIME(6) NOT NULL,
  finalizado_en DATETIME(6) NULL,
  PRIMARY KEY (id),
  KEY ix_eventos_integracion_estado_fecha (integracion_id, estado, iniciado_en),
  KEY ix_eventos_integracion_correlacion (correlacion_id),
  CONSTRAINT fk_eventos_integracion_integracion FOREIGN KEY (integracion_id) REFERENCES integraciones(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);

    }

    public function down(): void
    {
        throw new LogicException('Enterprise tables are forward-only to protect business data.');
    }
};

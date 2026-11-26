-- =========================================================
-- SAFE PIXEL — Base de datos MySQL / MariaDB
-- Importar desde Hostinger: hPanel > Bases de datos > phpMyAdmin > Importar
-- =========================================================

SET NAMES utf8mb4;

-- Prospectos que llegan desde el formulario de contacto
CREATE TABLE IF NOT EXISTS prospectos (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre        VARCHAR(100)  NOT NULL,
  telefono      VARCHAR(20)   NOT NULL,
  email         VARCHAR(150)  NULL,
  servicio      VARCHAR(60)   NOT NULL,
  mensaje       TEXT          NULL,
  origen        VARCHAR(60)   NULL COMMENT 'utm_source / referer: facebook, google, etc.',
  ip            VARCHAR(45)   NULL,
  estatus       ENUM('nuevo','contactado','cotizado','ganado','perdido') NOT NULL DEFAULT 'nuevo',
  creado_en     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en DATETIME     NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_estatus (estatus),
  INDEX idx_creado (creado_en),
  INDEX idx_ip_fecha (ip, creado_en)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Clientes / proyectos (portafolio y seguimiento)
CREATE TABLE IF NOT EXISTS clientes (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  empresa     VARCHAR(120) NOT NULL,
  giro        VARCHAR(80)  NULL,
  sitio_web   VARCHAR(200) NULL,
  empresa_padre_id INT UNSIGNED NULL COMMENT 'Ej. Mareka y Transportes ISL pertenecen a Corporativo Mateka',
  activo      TINYINT(1)   NOT NULL DEFAULT 1,
  creado_en   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (empresa_padre_id) REFERENCES clientes(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO clientes (id, empresa, giro) VALUES
  (1, 'Transportes Narvaza', 'Transporte y logística'),
  (2, 'Corporativo Mateka', 'Corporativo'),
  (3, 'Mareka', 'Corporativo Mateka'),
  (4, 'Transportes ISL', 'Transporte'),
  (5, 'Grasas y Madrina JM', 'Industria / comercio'),
  (6, 'Compushop', 'Tecnología'),
  (7, 'CATEM Tultitlán', 'Organización')
ON DUPLICATE KEY UPDATE empresa = VALUES(empresa);

UPDATE clientes SET empresa_padre_id = 2 WHERE id IN (3, 4);

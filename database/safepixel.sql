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
  notas         TEXT          NULL COMMENT 'Notas internas del panel',
  monto         DECIMAL(10,2) NULL COMMENT 'Monto cotizado / cerrado',
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
  empresa_padre_id INT UNSIGNED NULL COMMENT 'Ej. Mareka y Transportes ISL pertenecen a Corporativo Mareka',
  activo      TINYINT(1)   NOT NULL DEFAULT 1,
  creado_en   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (empresa_padre_id) REFERENCES clientes(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO clientes (id, empresa, giro, sitio_web) VALUES
  (1, 'Narvaza', 'Transporte y logística', 'https://narvaza.com.mx'),
  (2, 'Corporativo Mareka', 'Grupo empresarial', 'https://corporativomareka.com.mx'),
  (3, 'Mareka', 'Empresa del Corporativo Mareka', NULL),
  (4, 'Transportes ISL', 'Transporte', NULL),
  (5, 'Grúas y Madrinas JM', 'Grúas y arrastre', NULL),
  (6, 'Compushop', 'Tecnología', NULL),
  (7, 'CATEM Tultitlán', 'Organización', 'https://catemtultitlam.com.mx')
ON DUPLICATE KEY UPDATE empresa = VALUES(empresa), giro = VALUES(giro), sitio_web = VALUES(sitio_web);

UPDATE clientes SET empresa_padre_id = 2 WHERE id IN (3, 4);

-- Usuarios del panel privado (/admin). El primero se crea con /admin/instalar.php
CREATE TABLE IF NOT EXISTS usuarios (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario     VARCHAR(50)  NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  creado_en   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  ultimo_acceso DATETIME   NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Intentos fallidos de inicio de sesión (bloqueo por fuerza bruta)
CREATE TABLE IF NOT EXISTS login_intentos (
  id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ip        VARCHAR(45) NOT NULL,
  creado_en DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_ip_fecha (ip, creado_en)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

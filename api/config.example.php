<?php
/**
 * SAFE PIXEL — Configuración
 * 1. Copia este archivo como "config.php" (en la misma carpeta /api).
 * 2. Llena los datos que te da Hostinger en hPanel > Bases de datos > MySQL.
 * 3. NUNCA subas config.php a GitHub (ya está en .gitignore).
 */
return [
    'db_host' => 'localhost',            // En Hostinger casi siempre es localhost
    'db_name' => 'u123456789_safepixel', // Nombre de la base de datos
    'db_user' => 'u123456789_admin',     // Usuario de la base de datos
    'db_pass' => 'CAMBIA_ESTA_CONTRASEÑA',

    // Correo donde quieres recibir aviso de cada prospecto nuevo
    'notify_email' => 'contacto@safepixels.com.mx',
    // Remitente: debe ser un correo creado en Hostinger con tu dominio
    'from_email'   => 'no-reply@safepixels.com.mx',
];

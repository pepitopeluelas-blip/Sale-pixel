# Safe Pixel — Sitio web

Sitio de **Safe Pixel**: diseño de páginas web y sistemas para negocios (puntos de venta, menú digital en WhatsApp y web, inventarios, citas).

## Estructura

```
index.html               Página principal (una sola página con secciones)
assets/css/styles.css    Estilos (tema oscuro neón, responsivo)
assets/js/main.js        Interacciones + animación 3D con Three.js
assets/img/              Favicon e imágenes (agrega aquí og-safepixel.jpg 1200x630)
api/contacto.php         Recibe el formulario y lo guarda en MySQL
api/config.example.php   Plantilla de configuración (copiar a config.php)
database/safepixel.sql   Tablas: prospectos y clientes
.htaccess                HTTPS, caché, seguridad
robots.txt / sitemap.xml SEO
MARKETING.md             Plan para conseguir clientes
```

## Antes de publicar (cambia esto)

1. **WhatsApp:** en `assets/js/main.js`, objeto `CONFIG` al inicio (`whatsapp`, `whatsappVisible`).
2. **Dominio y correo:** busca `safepixel.com.mx` en `index.html`, `robots.txt` y `sitemap.xml` y pon tu dominio real.
3. **Teléfono en datos de Google:** `"telephone"` dentro del bloque `application/ld+json` de `index.html`.
4. **Redes sociales:** enlaces del footer (`Facebook`, `Instagram`, `TikTok`) y la lista `"sameAs"`.
5. **Imagen para compartir:** sube `assets/img/og-safepixel.jpg` (1200×630 px).
6. **Capturas reales de proyectos:** en la sección Portafolio, reemplaza las iniciales (`TN`, `CM`, …) por capturas de cada sitio y enlázalas.

## Publicar en Hostinger

1. **hPanel → Bases de datos → MySQL:** crea una base de datos y un usuario. Anota nombre, usuario y contraseña.
2. **phpMyAdmin → Importar:** sube `database/safepixel.sql`.
3. **Administrador de archivos → `public_html`:** sube todo el contenido del proyecto (menos la carpeta `.git`).
4. En `public_html/api/` copia `config.example.php` como `config.php` y llena los datos de la base de datos.
5. **Correos:** crea `contacto@tudominio` y `no-reply@tudominio` en hPanel → Correos.
6. **SSL:** hPanel → Seguridad → SSL → Activar (gratis). El `.htaccess` ya fuerza HTTPS.
7. Prueba el formulario y revisa la tabla `prospectos` en phpMyAdmin.

> Tip: también puedes conectar este repositorio en hPanel → Avanzado → Git para que cada `git push` actualice el sitio.

## Ver prospectos

En phpMyAdmin:

```sql
SELECT nombre, telefono, servicio, estatus, creado_en
FROM prospectos ORDER BY creado_en DESC;
```

Cambia el `estatus` (`nuevo → contactado → cotizado → ganado/perdido`) para llevar tu embudo de ventas.

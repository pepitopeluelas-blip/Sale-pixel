# Safe Pixel — Sitio web

Sitio de **Safe Pixel**: diseño de páginas web y sistemas para negocios (puntos de venta, menú digital en WhatsApp y web, inventarios, citas).

## Estructura

```
index.html               Página principal (una sola página con secciones)
assets/css/styles.css    Estilos (negro, grises y blanco; responsivo)
assets/js/main.js        Interacciones + animación 3D con Three.js
assets/img/              Favicon e imágenes (agrega aquí og-safepixel.jpg 1200x630)
api/contacto.php         Recibe el formulario y lo guarda en MySQL
api/db.php               Conexión compartida a MySQL
admin/                   Panel privado: prospectos, estatus, montos, notas, exportar a Excel
marketing/video/         Videos publicitarios (MP4) y su fuente HTML
api/config.example.php   Plantilla de configuración (copiar a config.php)
database/safepixel.sql   Tablas: prospectos y clientes
.htaccess                HTTPS, caché, seguridad
robots.txt / sitemap.xml SEO
MARKETING.md             Plan para conseguir clientes
```

## Antes de publicar (cambia esto)

1. **WhatsApp:** ya configurado (55 6780 7245) en `assets/js/main.js`, objeto `CONFIG`.
2. **Dominio y correo:** busca `safepixel.com.mx` en `index.html`, `robots.txt` y `sitemap.xml` y pon tu dominio real.
3. **Precios:** sección `id="precios"` de `index.html`.
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
7. **Panel privado:** abre `https://tudominio/admin/instalar.php`, crea tu usuario (contraseña de 10+ caracteres) y **borra `admin/instalar.php`** del servidor.
8. Entra a `https://tudominio/admin/` y prueba enviando el formulario del sitio.

> Tip: también puedes conectar este repositorio en hPanel → Avanzado → Git para que cada `git push` actualice el sitio.

## Panel privado (`/admin`)

- Métricas: prospectos del mes, sin contactar, cotizados, ganados (% de conversión) y total vendido.
- Filtros por estatus, buscador, y botón de WhatsApp directo a cada prospecto.
- Cambia estatus (`nuevo → contactado → cotizado → ganado/perdido`), registra monto y notas internas.
- Exporta a Excel (CSV).
- Seguridad: contraseñas con `password_hash`, protección CSRF, bloqueo tras 5 intentos fallidos en 15 min, sesión que expira a las 2 h de inactividad, `noindex`.

Si ya importaste la versión anterior de la base de datos, ejecuta en phpMyAdmin:

```sql
ALTER TABLE prospectos ADD COLUMN notas TEXT NULL AFTER estatus, ADD COLUMN monto DECIMAL(10,2) NULL AFTER notas;
```
y luego vuelve a importar `database/safepixel.sql` (crea las tablas `usuarios` y `login_intentos`).

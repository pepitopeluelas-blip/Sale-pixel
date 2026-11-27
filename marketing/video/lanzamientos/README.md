# Videos estilo "lanzamiento"

Inspirados en 5 referencias de animaciones de producto, adaptados a Safe Pixel. 1080x1920, música sin voz.

| Archivo | Estilo | Servicio | Duración |
|---|---|---|---|
| v1-espacio | Azul cósmico, logo con estelas | Marca + servicios, desde $1,999 | 14 s |
| v2-pastel | Pastel 3D, palabras que rebotan, tarjetas flotantes | Publica · Vende · Crece | 15 s |
| v3-interfaz | Blanco/verde, "Stop.", zoom a la pantalla real del punto de venta | Punto de venta $5,999 | 18 s |
| v4-morado | Morado brillante, ícono de app, botón y chat | Chatbot de WhatsApp $2,499 | 16 s |
| v5-cielo | Cielo y menta, celular con pedido | Menú digital $1,499 | 16 s |

Para hacer uno nuevo: copia un .html, cambia textos/tiempos (`--s` inicio y `--d` duración de cada escena), y:
```
node ../herramientas/render.js <ruta absoluta del html> <segundos> full <carpeta_frames>
python3 ../herramientas/musica.py musica.wav <segundos> '[cambios de escena]'
ffmpeg -framerate 30 -i <carpeta_frames>/f%04d.jpg -i musica.wav -c:v libx264 -crf 20 -pix_fmt yuv420p -c:a aac -shortest -movflags +faststart salida.mp4
```

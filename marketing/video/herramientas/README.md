# Herramientas para videos

| Archivo | Uso |
|---|---|
| `render.js` | `node render.js <ruta absoluta del .html> <segundos> full <carpeta_salida>`: graba el HTML cuadro por cuadro (30 fps). El HTML debe definir `window.seek(t)` (ver `../historia.html`). Con `preview` y una lista de segundos (`... preview <carpeta> 2,5,9`) saca solo esos cuadros. |
| `musica.py` | `python3 musica.py salida.wav <segundos> '[cambios de escena en s]'`: música ambiental libre de derechos. |
| `colocar_voz.py` | `python3 colocar_voz.py voz.mp3 '[[ini,fin,colocar_en],...]' <segundos> salida.wav`: corta frases de la voz y las coloca en cada escena. |
| `mezclar.sh` | `mezclar.sh <frames> musica.wav voz.wav salida.mp4`: video final con música que baja cuando habla la voz. |

Para encontrar dónde cortar la voz: `ffmpeg -i voz.mp3 -af silencedetect=noise=-38dB:d=0.25 -f null -`

Voz de ElevenLabs: **Arturo** (`svrHd8NySjbCTALnWLoS`), modelo `eleven_v4`.

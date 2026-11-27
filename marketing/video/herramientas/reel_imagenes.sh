#!/usr/bin/env bash
# Convierte 1 o más imágenes (4:5, 1080x1350) en un Reel vertical 1080x1920 con zoom suave, transiciones y música.
# Uso: reel_imagenes.sh salida.mp4 <segundos_por_imagen> img1.png [img2.png ...]
# No necesita voz ni créditos: la música se genera con musica.py.
set -euo pipefail
OUT="$1"; SEG="$2"; shift 2
HERR="$(cd "$(dirname "$0")" && pwd)"
TMP="$(mktemp -d)"
N=$#; FPS=30; XF=0.6
i=0
for img in "$@"; do
  # zoom lento centrado y fondo de marca
  ffmpeg -y -loglevel error -loop 1 -t "$SEG" -i "$img" -filter_complex \
    "[0]scale=1080:1350,zoompan=z='min(1+0.0009*on,1.08)':x='iw/2-(iw/zoom/2)':y='ih/2-(ih/zoom/2)':d=$((SEG*FPS)):s=1080x1350:fps=$FPS,pad=1080:1920:0:285:color=0x070707,format=yuv420p" \
    -t "$SEG" -r $FPS "$TMP/c$i.mp4"
  i=$((i+1))
done
if [ "$N" -eq 1 ]; then
  cp "$TMP/c0.mp4" "$TMP/v.mp4"; DUR="$SEG"; CAMBIOS="[]"
else
  INPUTS=""; FILT=""; PREV="[0:v]"; OFF=0; CAMBIOS="["
  for j in $(seq 0 $((N-1))); do INPUTS="$INPUTS -i $TMP/c$j.mp4"; done
  for j in $(seq 1 $((N-1))); do
    OFF=$(python3 -c "print(round($OFF + $SEG - $XF, 2))")
    FILT="$FILT${PREV}[$j:v]xfade=transition=fade:duration=$XF:offset=$OFF[x$j];"
    PREV="[x$j]"; CAMBIOS="$CAMBIOS$OFF,"
  done
  CAMBIOS="${CAMBIOS%,}]"
  DUR=$(python3 -c "print(round($N*$SEG - ($N-1)*$XF, 2))")
  ffmpeg -y -loglevel error $INPUTS -filter_complex "${FILT%;}" -map "$PREV" -r $FPS "$TMP/v.mp4"
fi
python3 "$HERR/musica.py" "$TMP/m.wav" "$DUR" "$CAMBIOS" >/dev/null
ffmpeg -y -loglevel error -i "$TMP/v.mp4" -i "$TMP/m.wav" \
  -vf "fade=t=in:st=0:d=0.4,fade=t=out:st=$(python3 -c "print($DUR-0.5)"):d=0.5" \
  -af "volume=1.6,alimiter=limit=0.95" -c:v libx264 -preset medium -crf 20 -pix_fmt yuv420p \
  -c:a aac -b:a 160k -shortest -movflags +faststart "$OUT"
rm -r "$TMP"
echo "$OUT ($DUR s)"

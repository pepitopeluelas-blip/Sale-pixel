#!/usr/bin/env bash
# Uso: mezclar.sh <carpeta_frames> <musica.wav> <voz.wav> <salida.mp4>
# Une los cuadros (f0000.jpg...) a 30 fps con música que baja cuando habla la voz.
set -euo pipefail
ffmpeg -y -loglevel error -framerate 30 -i "$1/f%04d.jpg" -i "$2" -i "$3" -filter_complex \
 "[2:a]aresample=44100,highpass=f=70,acompressor=threshold=-20dB:ratio=2.5:attack=5:release=80,volume=1.5,asplit=2[v][sc];[1:a]volume=0.5[m];[m][sc]sidechaincompress=threshold=0.03:ratio=8:attack=20:release=350[md];[md][v]amix=inputs=2:normalize=0,volume=3.5dB,alimiter=limit=0.95[a]" \
 -map 0:v -map "[a]" -c:v libx264 -preset slow -crf 20 -pix_fmt yuv420p -c:a aac -b:a 192k -shortest -movflags +faststart "$4"

#!/usr/bin/env bash
# Rebuild the self-hosted Archivo subset (needs: pip install fonttools brotli).
# Source: Google Fonts Archivo variable (latin). Output keeps wdth 100–125 and wght 400–800.
set -euo pipefail
SRC="${1:?path to Archivo variable woff2/ttf}"
OUT="wp-content/themes/top-appliance/assets/fonts/archivo-var.woff2"
fonttools varLib.instancer "$SRC" wdth=100:125 wght=400:800 -o /tmp/archivo-inst.ttf
pyftsubset /tmp/archivo-inst.ttf \
  --unicodes="U+0020-007E,U+00A0,U+00A9,U+00AE,U+00B7,U+00D7,U+00E9,U+00F1,U+2013-2014,U+2018-201D,U+2022,U+2026,U+2122" \
  --layout-features="kern" --no-hinting --desubroutinize --flavor=woff2 --output-file="$OUT"
ls -l "$OUT"

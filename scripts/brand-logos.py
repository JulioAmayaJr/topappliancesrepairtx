"""Normalise brand logos: trim white margins, fit to the same visual box, centre on a 200x80
white canvas, save WebP. Usage: python scripts/brand-logos.py <dir-with-jpgs>"""
import sys
from pathlib import Path
from PIL import Image, ImageChops

SRC = Path(sys.argv[1]); OUT = Path(__file__).resolve().parent.parent / "seed/brands"
W, H, BOX_W, BOX_H = 200, 80, 168, 50
NAMES = {"sub-zero": "sub-zero", "whirlpool": "whirlpool", "admiral": "admiral", "ge-appliances": "ge", "bosch": "bosch",
         "kenmore": "kenmore", "thermador": "thermador", "lg": "lg", "samsung": "samsung", "kitchen-aid": "kitchenaid",
         "jenn-air": "jenn-air", "maytag": "maytag", "amana": "amana", "viking": "viking", "thor": "thor"}
OUT.mkdir(parents=True, exist_ok=True)
for src, slug in NAMES.items():
    im = Image.open(SRC / f"{src}.jpg").convert("RGB")
    bg = Image.new("RGB", im.size, (255, 255, 255))
    diff = ImageChops.difference(im, bg).convert("L").point(lambda v: 255 if v > 18 else 0)
    box = diff.getbbox() or (0, 0, *im.size)
    logo = im.crop(box)
    scale = min(BOX_W / logo.width, BOX_H / logo.height)
    logo = logo.resize((max(1, round(logo.width * scale)), max(1, round(logo.height * scale))), Image.LANCZOS)
    canvas = Image.new("RGB", (W, H), (255, 255, 255))
    canvas.paste(logo, ((W - logo.width) // 2, (H - logo.height) // 2))
    canvas.save(OUT / f"{slug}.webp", "WEBP", quality=90, method=6)
    print(slug, logo.size)

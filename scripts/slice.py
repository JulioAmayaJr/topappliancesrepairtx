"""Slice tall screenshots into readable parts. Usage: python scripts/slice.py <png> <outprefix> <step> <scale>"""
import sys
from PIL import Image
src, prefix, step, scale = sys.argv[1], sys.argv[2], int(sys.argv[3]), float(sys.argv[4])
im = Image.open(src); W, H = im.size; n = 0
for y in range(0, H, step):
    c = im.crop((0, y, W, min(H, y + step)))
    if scale != 1:
        c = c.resize((int(c.width * scale), int(c.height * scale)))
    c.save(f"{prefix}-{n}.png"); n += 1
print(n, "parts", W, H)

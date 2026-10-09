"""Brand image blocks used until real photos arrive (no text): the band gradient from docs/brand.md
(#0652A0 -> #013469) with the logo wheel centred. Same aspect ratios as the final photos so swapping
in the real photo causes no layout shift. Also writes the 1200x630 social image.
Usage: python scripts/placeholders.py"""
from pathlib import Path
from PIL import Image, ImageDraw, ImageFont

ROOT = Path(__file__).resolve().parent.parent
OUT = ROOT / "seed/images"
BRAND = ROOT / "wp-content/themes/top-appliance/assets/images/brand"
FONT = str(ROOT / "seed/archivo-instance.ttf")
TOP, BOTTOM, CYAN = (6, 82, 160), (1, 52, 105), (41, 171, 226)

def gradient(w, h):
    """Diagonal band gradient (top-left #0652A0 -> bottom-right #013469)."""
    small = Image.new("RGB", (64, 64))
    px = small.load()
    for y in range(64):
        for x in range(64):
            t = (x + y) / 126
            px[x, y] = tuple(round(a + (b - a) * t) for a, b in zip(TOP, BOTTOM))
    return small.resize((w, h), Image.BICUBIC)

def wheel_mask(size):
    """Logo wheel alpha, upscaled and re-sharpened so the edge stays crisp."""
    a = Image.open(BRAND / "logo-wheel.png").convert("RGBA").split()[3]
    a = a.resize((size, size), Image.LANCZOS)
    return a.point(lambda v: 0 if v < 70 else (255 if v > 185 else int((v - 70) * 255 / 115)))

def power(d, cx, cy, r, width):
    d.arc([cx - r, cy - r, cx + r, cy + r], start=-50, end=230, fill=CYAN, width=width)
    d.line([cx, cy - r * 1.15, cx, cy - r * 0.15], fill=CYAN, width=width)
    for x, y in ((cx, cy - r * 1.15), (cx, cy - r * 0.15)):
        d.ellipse([x - width / 2, y - width / 2, x + width / 2, y + width / 2], fill=CYAN)

def brand_block(name, w, h):
    im = gradient(w, h).convert("RGBA")
    # Large faint wheel for depth.
    big = int(h * 1.25)
    ghost = Image.new("RGBA", (big, big), (255, 255, 255, 0))
    ghost.putalpha(wheel_mask(big).point(lambda v: v * 14 // 255))
    im.alpha_composite(ghost, (int(w - big * 0.62), int(h - big * 0.70)))
    # Logo wheel, white, centred.
    s = int(min(w, h) * 0.42)
    mark = Image.new("RGBA", (s, s), (255, 255, 255, 0))
    mark.putalpha(wheel_mask(s))
    x, y = (w - s) // 2, (h - s) // 2
    im.alpha_composite(mark, (x, y))
    # Cyan power symbol in the hub (drawn at 4x, downsampled for clean edges).
    k = 4
    layer = Image.new("RGBA", (s * k, s * k), (0, 0, 0, 0))
    power(ImageDraw.Draw(layer), s * k / 2, s * k / 2 + s * k * 0.03, s * k * 0.17, int(s * k * 0.055))
    im.alpha_composite(layer.resize((s, s), Image.LANCZOS), (x, y))
    im.convert("RGB").save(OUT / f"{name}.webp", "WEBP", quality=86, method=6)
    print("wrote", name)

def font(size, wght=800, wdth=125):
    f = ImageFont.truetype(FONT, size)
    f.set_variation_by_axes([wght, wdth])
    return f

def og_image():
    w, h = 1200, 630
    im = gradient(w, h).convert("RGBA")
    logo = Image.open(BRAND / "footer-logo.png").convert("RGBA")
    logo = logo.resize((int(logo.width * 1.25), int(logo.height * 1.25)), Image.LANCZOS)
    im.alpha_composite(logo, (80, 70))
    d = ImageDraw.Draw(im)
    y = 290
    for line in ["Same-Day Appliance", "Repair in Houston, TX"]:
        d.text((80, y), line, font=font(66), fill="#FFFFFF")
        y += 80
    d.text((80, y + 24), "Free estimate · Warranty on parts and labor · All major brands", font=font(30, 600, 100), fill="#7EBEC5")
    im.convert("RGB").save(OUT / "og-default.png", "PNG", optimize=True)
    print("wrote og-default")

if __name__ == "__main__":
    OUT.mkdir(parents=True, exist_ok=True)
    for old in OUT.glob("*.webp"):
        old.unlink()
    brand_block("brand-4x3", 1600, 1200)
    brand_block("brand-4x5", 1200, 1500)
    brand_block("brand-16x9", 1200, 675)
    og_image()

"""Generate clearly labelled photo placeholders + the default social image (Pillow).
Usage: python scripts/placeholders.py   (writes seed/images/*.webp|png)"""
import sys, textwrap
from pathlib import Path
from PIL import Image, ImageDraw, ImageFont

ROOT = Path(__file__).resolve().parent.parent
OUT = ROOT / "seed/images"
FONT = str(ROOT / "seed/archivo-instance.ttf")
LOGO_MARK = ROOT / "wp-content/themes/top-appliance/assets/images/brand/cropped-topappliance-favicon-270x270.png"
FOOTER_LOGO = ROOT / "wp-content/themes/top-appliance/assets/images/brand/footer-logo.png"
TINT, LINE, SLATE, GEAR, INK, NAVY, BAND_TOP = "#E6EDF5", "#CDDCEB", "#39618B", "#054199", "#00172F", "#013469", "#0652A0"

def font(size, wght=600, wdth=100):
    f = ImageFont.truetype(FONT, size)
    try:
        f.set_variation_by_axes([wght, wdth])
    except Exception:
        pass
    return f

def wrap(d, text, f, maxw):
    words, lines, cur = text.split(), [], ""
    for w in words:
        t = (cur + " " + w).strip()
        if d.textlength(t, font=f) <= maxw:
            cur = t
        else:
            lines.append(cur); cur = w
    return lines + ([cur] if cur else [])

def camera(d, cx, cy, s, color):
    w, h = 2.2 * s, 1.5 * s
    d.rounded_rectangle([cx - w / 2, cy - h / 2, cx + w / 2, cy + h / 2], radius=s * 0.25, outline=color, width=max(3, int(s * 0.09)))
    d.ellipse([cx - s * 0.45, cy - s * 0.45, cx + s * 0.45, cy + s * 0.45], outline=color, width=max(3, int(s * 0.09)))
    d.rectangle([cx - s * 0.5, cy - h / 2 - s * 0.22, cx + s * 0.05, cy - h / 2], fill=color)

def placeholder(name, w, h, title, brief):
    im = Image.new("RGB", (w, h), TINT)
    d = ImageDraw.Draw(im)
    step = max(28, w // 40)
    for x in range(-h, w, step):
        d.line([(x, 0), (x + h, h)], fill="#DDE6F0", width=2)
    pad = int(w * 0.07)
    d.rounded_rectangle([pad // 2, pad // 2, w - pad // 2, h - pad // 2], radius=int(w * 0.02), outline=LINE, width=4)
    s = int(min(w, h) * 0.08)
    camera(d, w / 2, h * 0.30, s, SLATE)
    y = h * 0.30 + s * 1.4
    f1 = font(int(w * 0.022), 800, 125)
    tag = "PHOTO PLACEHOLDER"
    tw = d.textlength(tag, font=f1)
    d.text(((w - tw) / 2, y), tag, font=f1, fill=SLATE)
    y += int(w * 0.04)
    f2 = font(int(w * 0.038), 800, 112)
    for line in wrap(d, title, f2, w * 0.78):
        tw = d.textlength(line, font=f2)
        d.text(((w - tw) / 2, y), line, font=f2, fill=GEAR)
        y += int(w * 0.048)
    y += int(w * 0.012)
    f3 = font(int(w * 0.021), 500)
    for line in wrap(d, brief, f3, w * 0.74):
        tw = d.textlength(line, font=f3)
        d.text(((w - tw) / 2, y), line, font=f3, fill=INK)
        y += int(w * 0.03)
    f4 = font(int(w * 0.017), 600)
    dim = f"Replace with a real photo, at least {w} x {h} px"
    tw = d.textlength(dim, font=f4)
    d.text(((w - tw) / 2, h - pad - int(w * 0.02)), dim, font=f4, fill=SLATE)
    im.save(OUT / f"{name}.webp", "WEBP", quality=82, method=6)
    print("wrote", name)

def og_image():
    w, h = 1200, 630
    im = Image.new("RGB", (w, h), NAVY)
    d = ImageDraw.Draw(im)
    for y in range(h):  # brand band gradient #0652A0 -> #013469
        t = y / h
        c = tuple(int(a + (b - a) * t) for a, b in zip((6, 82, 160), (1, 52, 105)))
        d.line([(0, y), (w, y)], fill=c)
    logo = Image.open(FOOTER_LOGO).convert("RGBA")
    logo = logo.resize((int(logo.width * 1.25), int(logo.height * 1.25)))
    im.paste(logo, (80, 70), logo)
    f = font(66, 800, 125)
    y = 290
    for line in ["Same-Day Appliance", "Repair in Houston, TX"]:
        d.text((80, y), line, font=f, fill="#FFFFFF")
        y += 80
    f2 = font(30, 600)
    d.text((80, y + 24), "Free estimate · Warranty on parts and labor · All major brands", font=f2, fill="#7EBEC5")
    im.save(OUT / "og-default.png", "PNG", optimize=True)
    print("wrote og-default")

if __name__ == "__main__":
    OUT.mkdir(parents=True, exist_ok=True)
    placeholder("hero-technician", 1600, 1200, "Hero: technician repairing an appliance in a Houston home",
                "Real photo: Top Appliances Repair technician in uniform working on a refrigerator or washer, natural light, landscape 4:3.")
    placeholder("why-technician", 1200, 1500, "Technician portrait",
                "Real photo: one of the company's technicians (or the owner) with tools or the service van, portrait 4:5.")
    posts = {
        "blog-washer-not-draining": "Blog: washing machine not draining",
        "blog-dishwasher-not-cleaning": "Blog: dishwasher not cleaning dishes",
        "blog-refrigerator-not-cooling": "Blog: refrigerator not cooling",
        "blog-types-of-stoves": "Blog: types of stoves",
        "blog-smelly-garbage-disposal": "Blog: smelly garbage disposal",
        "blog-how-long-refrigerators-last": "Blog: how long refrigerators last",
        "blog-refrigerator-leak": "Blog: refrigerator leak warning signs",
        "blog-refrigerator-compressor": "Blog: refrigerator compressor failure",
        "blog-speed-queen-washer": "Blog: Speed Queen washer repair",
    }
    for slug, title in posts.items():
        placeholder(slug, 1200, 675, title, "Real photo: featured image for this article (the appliance or the repair it describes), landscape 16:9.")
    og_image()

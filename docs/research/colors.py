import sys,re,collections
from PIL import Image
dl=sys.argv[1]
print("== LOGOS ==")
for f in ["logo-desktop.png","logo-wheel.png","footer-logo.png","cropped-topappliance-favicon-270x270.png"]:
    im=Image.open(f"{dl}/logos/{f}").convert("RGBA")
    print(f, im.size)
    c=collections.Counter()
    for r,g,b,a in im.getdata():
        if a<200: continue
        # quantize lightly to merge antialias
        c[(r//8*8,g//8*8,b//8*8)]+=1
    tot=sum(c.values())
    # also exact counts
    ex=collections.Counter((r,g,b) for r,g,b,a in im.getdata() if a>=250)
    for (rgb,n) in ex.most_common(8):
        print("   exact #%02x%02x%02x"%rgb, f"{n/sum(ex.values())*100:.1f}%")
def norm(h):
    h=h.lower()
    if len(h)==4: h="#"+"".join(ch*2 for ch in h[1:])
    return h
for name in ["inline.css","child-style.css","divi-style.css"]:
    t=open(f"{dl}/css/{name}",encoding="utf-8",errors="ignore").read()
    c=collections.Counter()
    for h in re.findall(r'#[0-9a-fA-F]{6}\b|#[0-9a-fA-F]{3}\b',t): c[norm(h)]+=1
    for m in re.findall(r'rgba?\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*(?:,\s*([\d.]+))?\)',t):
        k="#%02x%02x%02x"%tuple(map(int,m[:3]))+(f" a{m[3]}" if m[3] else "")
        c[k]+=1
    print(f"== {name} ==", sum(c.values()))
    for k,n in c.most_common(25): print("  ",k,n)

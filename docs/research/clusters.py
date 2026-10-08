import sys,collections,colorsys
from PIL import Image
dl=sys.argv[1]
im=Image.open(f"{dl}/logos/logo-desktop.png").convert("RGBA")
W,H=im.size
px=im.load()
groups=collections.defaultdict(collections.Counter)
for y in range(H):
  for x in range(W):
    r,g,b,a=px[x,y]
    if a<250: continue
    h,s,v=colorsys.rgb_to_hsv(r/255,g/255,b/255)
    region="text" if y>115 else "mark"
    if s<0.15: grp="neutral"
    elif v>0.75 and h<0.58: grp="cyan"
    else: grp="blue"
    groups[(region,grp)][(r,g,b)]+=1
for k,c in sorted(groups.items()):
    tot=sum(c.values())
    print(k,tot)
    for rgb,n in c.most_common(6): print("   #%02x%02x%02x"%rgb,n)
    # darkest & lightest within group by luminance
    lum=lambda t:0.2126*t[0]+0.7152*t[1]+0.0722*t[2]
    ks=[t for t,n in c.items() if n>=3]
    if ks:
        print("   darkest #%02x%02x%02x"%min(ks,key=lum),"lightest #%02x%02x%02x"%max(ks,key=lum))

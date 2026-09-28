"""Generate simple brand-style test images with PIL (no AI model needed)."""

from PIL import Image, ImageDraw, ImageFont
import os

OUT = os.path.join(os.path.dirname(os.path.abspath(__file__)), "assets")
W, H = 1920, 1080
os.makedirs(OUT, exist_ok=True)


def rounded_rect(draw, xy, r, fill):
    draw.rounded_rectangle(xy, radius=r, fill=fill)


def make(name, bg_top, bg_bottom, accent, title, subtitle, y_title=500):
    img = Image.new("RGB", (W, H))
    px = img.load()
    for y in range(H):
        t = y / H
        r = int(bg_top[0] + (bg_bottom[0] - bg_top[0]) * t)
        g = int(bg_top[1] + (bg_bottom[1] - bg_top[1]) * t)
        b = int(bg_top[2] + (bg_bottom[2] - bg_top[2]) * t)
        for x in range(W):
            px[x, y] = (r, g, b)
    draw = ImageDraw.Draw(img)

    # soft accent glow blob
    glow = Image.new("RGBA", (W, H), (0, 0, 0, 0))
    gd = ImageDraw.Draw(glow)
    for i in range(60, 0, -4):
        a = max(0, 40 - i)
        gd.ellipse((W // 2 - i * 8, y_title - 250 - i * 6, W // 2 + i * 8, y_title + 150 + i * 6), fill=accent + (a,))
    img.paste(glow, (0, 0), glow)

    draw = ImageDraw.Draw(img)
    try:
        font = ImageFont.truetype("C:/Windows/Fonts/arialbd.ttf", 96)
        sub_font = ImageFont.truetype("C:/Windows/Fonts/arial.ttf", 44)
    except Exception:
        font = ImageFont.load_default()
        sub_font = ImageFont.load_default()

    bbox = draw.textbbox((0, 0), title, font=font)
    tw = bbox[2] - bbox[0]
    draw.text(((W - tw) / 2, y_title), title, font=font, fill=(255, 255, 255))
    bbox = draw.textbbox((0, 0), subtitle, font=sub_font)
    tw = bbox[2] - bbox[0]
    draw.text(((W - tw) / 2, y_title + 130), subtitle, font=sub_font, fill=(200, 210, 250))
    img.save(os.path.join(OUT, name))
    print("saved", name)


make("slide1.png", (30, 41, 59), (71, 85, 151), (129, 140, 248),
     "Elevate Media Productions", "Cinematic brand videos that convert")
make("slide2.png", (49, 46, 129), (190, 24, 93), (244, 114, 182),
     "Your Story, Cinematically", "Web series, promos & social content")
make("slide3.png", (15, 23, 42), (14, 116, 144), (45, 212, 191),
     "Unlimited Local Workflow", "Powered by your own machine")
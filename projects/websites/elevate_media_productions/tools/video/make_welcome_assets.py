"""Design 10 branded, cinematic slides + a contact/outro slide for the welcome
video, and build scenes.json timed to the measured narration clips. Pure PIL.

Slide design language:
- Dark cinematic gradient base with a moody diagonal light sweep
- Thin accent rule + section kicker + big headline + supporting line
- REAL device artwork: phone / browser mockups rendered by make_mockups.py
- Consistent brand palette (indigo / pink / teal on slate)
"""

import json
import math
import os
from PIL import Image, ImageDraw, ImageFont, ImageFilter

BASE = os.path.dirname(os.path.abspath(__file__))
OUT = os.path.join(BASE, "assets_welcome")
os.makedirs(OUT, exist_ok=True)
W, H = 1920, 1080

INDIGO = (99, 102, 241)
PINK = (236, 72, 153)
TEAL = (45, 212, 191)
SLATE_mid = (30, 41, 59)

FONT_BOLD = "C:/Windows/Fonts/arialbd.ttf"
FONT_REG = "C:/Windows/Fonts/arial.ttf"

METAS = None
with open(os.path.join(BASE, "narration_meta.json"), "r", encoding="utf-8") as f:
    METAS = json.load(f)


def font(path, size):
    return ImageFont.truetype(path, size)


def vgradient(bg_top, bg_bottom):
    img = Image.new("RGB", (W, H))
    px = img.load()
    for y in range(H):
        t = y / H
        r = int(bg_top[0] + (bg_bottom[0] - bg_top[0]) * t)
        g = int(bg_top[1] + (bg_bottom[1] - bg_top[1]) * t)
        b = int(bg_top[2] + (bg_bottom[2] - bg_top[2]) * t)
        for x in range(W):
            px[x, y] = (r, g, b)
    return img


def diagonal_sweep(img, color, alpha, angle_deg, width=520, xoff=0):
    ov = Image.new("RGBA", (W, H), (0, 0, 0, 0))
    d = ImageDraw.Draw(ov)
    pts = []
    cx = W * 0.5 + xoff
    a = math.radians(angle_deg)
    dx = math.cos(a) * (H * 1.5)
    dy = math.sin(a) * (H * 1.5)
    for s in (-0.5, 0.5):
        perp = math.radians(angle_deg + 90)
        px = math.cos(perp) * width * s
        py = math.sin(perp) * width * s
        pts += [(cx + i * dx + px, H + i * dy + py) for i in (0.6, -0.6)]
    d.polygon(pts, fill=color + (alpha,))
    ov = ov.filter(ImageFilter.GaussianBlur(60)).resize((W, H), Image.BILINEAR)
    img.paste(ov, (0, 0), ov)


def add_glow(img, cx, cy, rad, color, alpha):
    ov = Image.new("RGBA", (W, H), (0, 0, 0, 0))
    d = ImageDraw.Draw(ov)
    for rr in range(rad, 0, -6):
        a = max(0, int(alpha * (rr / rad)))
        d.ellipse((cx - rr, cy - rr, cx + rr, cy + rr), fill=color + (a,))
    ov = ov.filter(ImageFilter.GaussianBlur(40))
    img.paste(ov, (0, 0), ov)


def paste_mockup(img, file, x, y, max_w):
    """Paste a device artwork scaled to fit max_w, vertically centered in slide."""
    art = Image.open(os.path.join(OUT, file)).convert("RGB")
    scale = max_w / art.width
    nw, nh = int(art.width * scale), int(art.height * scale)
    art = art.resize((nw, nh), Image.LANCZOS)
    # subtle soft shadow
    sh = Image.new("RGBA", (W, H), (0, 0, 0, 0))
    sd = ImageDraw.Draw(sh)
    sh_r = 40
    for rr in range(sh_r, 0, -3):
        a = int(120 * (rr / sh_r))
        sd.rounded_rectangle((x + nw // 3 + 8 - rr, y + nh // 2 + 6 - rr,
                              x + nw + nw // 2 + 8 + rr, y + nh + nh // 2 + 6 + rr),
                             radius=40, fill=(0, 0, 0, a))
    sh = sh.filter(ImageFilter.GaussianBlur(18))
    img.paste(sh, (0, 0), sh)
    img.paste(art, (x, y))


def make_slide(idx, kicker, title, sub, accent, mockup=None,
               bg_top=SLATE_mid, bg_bottom=(5, 10, 22),
               devices=None):
    """devices: list of (file, right_x, top_y, max_w) — allows layered phone+browser."""
    img = vgradient(bg_top, bg_bottom)
    diagonal_sweep(img, accent, 26, -22, width=460, xoff=90)
    add_glow(img, W * 0.82, H * 0.18, 320, accent, 18)
    draw = ImageDraw.Draw(img)

    kf = font(FONT_BOLD, 40)
    if kicker:
        draw.text((120, 250), kicker.upper(), font=kf, fill=accent + (255,))
        draw.rectangle((124, 310, 148, 316), fill=accent + (255,))

    max_w = W - 240 - (640 if devices or mockup else 200)
    tf = font(FONT_BOLD, 92)
    words = title.split()
    lines, cur = [], ""
    for wd in words:
        trial = (cur + " " + wd).strip()
        if draw.textlength(trial, font=tf) <= max_w:
            cur = trial
        else:
            if cur:
                lines.append(cur)
            cur = wd
    if cur:
        lines.append(cur)
    ypos = 380
    for ln in lines:
        draw.text((120, ypos), ln, font=tf, fill=(246, 250, 255))
        ypos += 110

    sf = font(FONT_REG, 40)
    draw.text((122, ypos + 30), sub, font=sf, fill=(180, 196, 222))

    if mockup:
        paste_mockup(img, mockup, W - 640 - (70 if "phone" in mockup else 20), 250, 560 if "phone" in mockup else 660)
    for fpath, rx, ty, mw in (devices or []):
        img2 = Image.open(os.path.join(OUT, fpath)).convert("RGB")
        scale = mw / img2.width
        nw, nh = int(img2.width * scale), int(img2.height * scale)
        img2 = img2.resize((nw, nh), Image.LANCZOS)
        img.paste(img2, (rx, ty))

    path = os.path.join(OUT, f"slide_{idx:02d}.png")
    img.save(path)
    return path


def contact_slide():
    """Final call-to-action: website, socials, WhatsApp, email, GitHub."""
    img = vgradient((23, 33, 58), (5, 8, 20))
    diagonal_sweep(img, INDIGO, 30, -22, width=520, xoff=-60)
    add_glow(img, W * 0.18, H * 0.8, 360, PINK, 16)
    draw = ImageDraw.Draw(img)

    draw.text((W // 2 - draw.textlength("ELEVATE MEDIA PRODUCTIONS", font=font(FONT_BOLD, 66)) / 2, 120),
              "ELEVATE MEDIA PRODUCTIONS", font=font(FONT_BOLD, 66), fill=(248, 250, 255))
    draw.text((W // 2 - draw.textlength("Let's build something unforgettable", font=font(FONT_REG, 40)) / 2, 230),
              "Let's build something unforgettable", font=font(FONT_REG, 40), fill=(180, 196, 222))
    draw.line((W // 2 - 90, 320, W // 2 + 90, 320), fill=INDIGO + (255,), width=5)

    rows = [
        ("Website", "elevate-media-productions.vercel.app", INDIGO),
        ("Email", "elevatemediaproductions1@gmail.com", PINK),
        ("WhatsApp", "+254 111 275 630", TEAL),
        ("Socials", "Instagram  •  X / Twitter  •  TikTok  •  Threads", INDIGO),
        ("GitHub", "github.com/emmanuel-tech-resources", PINK),
    ]
    y = 400
    for label, value, col in rows:
        draw.text((W // 2 - 820, y), label.upper(), font=font(FONT_BOLD, 30), fill=col + (255,))
        draw.text((W // 2 - 240, y + 2), value, font=font(FONT_REG, 34), fill=(240, 244, 252))
        y += 96

    draw.text((W // 2 - draw.textlength("Follow us everywhere, and tell a friend.", font=font(FONT_REG, 32)) / 2, 930),
              "Follow us everywhere, and tell a friend.", font=font(FONT_REG, 32), fill=(150, 165, 195))
    path = os.path.join(OUT, "slide_11.png")
    img.save(path)
    return path


def build_scenes():
    """Timed to narration: scene = narration + small pause (natural flow)."""
    # pause after each line ~0.9-1.5s keeps speech flowing without dead air
    paddings = [1.5, 1.3, 1.5, 1.4, 1.4, 1.3, 1.5, 1.4, 1.6, 2.2]
    scenes, total = [], 0.0
    for i, m in enumerate(METAS):
        dur = m["duration"] + paddings[i]
        total += dur
        n = i + 1
        out = n % 2 == 1
        scenes.append({
            "image": f"assets_welcome/slide_{n:02d}.png",
            "narrate": f"narration/scene{n:02d}.mp3",
            "duration": round(dur, 1),
            "zoom": 1.0 if out else 1.07,
            "zoom_end": 1.07 if out else 1.0,
        })
    # outro slide: no narration, held on screen while voice finishes
    scenes.append({
        "image": "assets_welcome/slide_11.png",
        "narrate": None,
        "duration": 6.0,
        "zoom": 1.0,
        "zoom_end": 1.06,
    })
    total += 6.0
    return scenes, round(total, 1)


def main():
    layout = [
        dict(kicker="Welcome", title="Elevate Media Productions", sub="Where world-class apps and websites are born", accent=INDIGO, mockup=None),
        dict(kicker="More than a website", title="A brand people remember, trust and return to", sub="We craft experiences that stay with you", accent=PINK, mockup="web_home.png"),
        dict(kicker="What we build", title="Apps that feel effortless", sub="Websites that turn visitors into loyal fans", accent=TEAL, mockup="music_phone.png"),
        dict(kicker="Our craft", title="Every pixel has purpose", sub="We obsess over details, so you stand out", accent=INDIGO, mockup=None),
        dict(kicker="Who we serve", title="Startups to established brands", sub="Teams that are ready to level up", accent=TEAL, mockup="web_dash.png"),
        dict(kicker="The people", title="Clean code. Beautiful design.", sub="Fast, secure, built to be loved", accent=PINK, mockup="bank_phone.png"),
        dict(kicker="Why us", title="The best brand in app and website development", sub="We deliver results, not just projects", accent=INDIGO, mockup=None),
        dict(kicker="Your brand", title="You deserve a team that cares", sub="Your vision becomes our mission", accent=PINK, mockup="chat_phone.png"),
        dict(kicker="Let's talk", title="Build something unforgettable", sub="Visit the website and start your journey today", accent=TEAL, mockup="food_phone.png"),
        dict(kicker="Thank you", title="Where your digital future comes to life", sub="Elevate Media Productions", accent=INDIGO, mockup=None),
    ]
    for i, lx in enumerate(layout, start=1):
        p = make_slide(i, lx["kicker"], lx["title"], lx["sub"], lx["accent"], mockup=lx["mockup"])
        print("slide", i, os.path.basename(p))

    p = contact_slide()
    print("slide 11 (contact)", os.path.basename(p))

    scenes, total = build_scenes()
    cfg = {
        "width": 1920,
        "height": 1080,
        "fps": 30,
        "output_name": "elevate_welcome.mp4",
        "scenes": scenes,
    }
    with open(os.path.join(BASE, "welcome_scenes.json"), "w", encoding="utf-8") as f:
        json.dump(cfg, f, indent=2, ensure_ascii=False)
    print("=" * 50)
    print("Total video length (pre-fade):", total, "seconds")


if __name__ == "__main__":
    main()
"""Render realistic-looking phone & browser mockups with real UI content.
Pure PIL. Produces full-frame artworks that slides paste on the right side.

Outputs (into assets_welcome/):
  music_phone.png   - dark music streaming app
  bank_phone.png    - fintech banking app
  chat_phone.png    - team chat app
  food_phone.png    - food delivery app
  web_home.png      - browser with Elevate-style marketing homepage
  web_dash.png      - browser with analytics dashboard
"""

import math
import os
import random
from PIL import Image, ImageDraw, ImageFont, ImageFilter

A = os.path.join(os.path.dirname(os.path.abspath(__file__)), "assets_welcome")
os.makedirs(A, exist_ok=True)

FB = "C:/Windows/Fonts/arialbd.ttf"
FR = "C:/Windows/Fonts/arial.ttf"

INDIGO = (99, 102, 241)
PINK = (236, 72, 153)
TEAL = (45, 212, 191)
AMBER = (251, 191, 36)
GREEN = (52, 211, 153)
SLATE_D = (13, 19, 34)
SLATE_L = (51, 65, 85)
INK = (20, 24, 40)
WHITE = (248, 250, 255)
GREY = (148, 163, 184)


def f(p, s):
    return ImageFont.truetype(p, s)


def noise_photo(w, h, c1, c2, seed=None):
    """Create a believable abstract 'photo': gradient + soft blobs + noise + slight blur."""
    rnd = random.Random(seed)
    img = Image.new("RGB", (w, h))
    d = ImageDraw.Draw(img)
    for y in range(h):
        t = y / h
        dc = tuple(int(c1[i] + (c2[i] - c1[i]) * t) for i in range(3))
        for x in range(0, w, 8):
            d.rectangle((x, y, x + 8, y), fill=dc)
    ov = Image.new("RGBA", (w, h), (0, 0, 0, 0))
    od = ImageDraw.Draw(ov)
    for _ in range(14):
        cx = rnd.randint(-30, w - 30)
        cy = rnd.randint(-30, h - 30)
        r = rnd.randint(30, 110)
        col = tuple(rnd.randint(0, 255) for _ in range(3))
        a = rnd.randint(40, 150)
        od.ellipse((cx - r, cy - r, cx + r, cy + r), fill=col + (a,))
    ov = ov.filter(ImageFilter.GaussianBlur(18))
    img.paste(ov, (0, 0), ov)
    img = img.filter(ImageFilter.GaussianBlur(1.2))
    # sun / moon glow
    glow = Image.new("RGBA", (w, h), (0, 0, 0, 0))
    gd = ImageDraw.Draw(glow)
    glx = max(1, w - 60)
    gly = max(1, h - 60)
    gx, gy = rnd.randint(1, glx), rnd.randint(1, gly)
    gr = rnd.randint(18, 34)
    gcol = (255, 255, 240) if rnd.random() < 0.5 else (255, 214, 140)
    gd.ellipse((gx - gr, gy - gr, gx + gr, gy + gr), fill=(gcol + (200,)))
    glow = glow.filter(ImageFilter.GaussianBlur(8))
    img.paste(glow, (0, 0), glow)
    return img


def rounded(d, box, r, **kw):
    d.rounded_rectangle(box, radius=r, **kw)


# --------------------------------------------------------------------------
# PHONE helper
# --------------------------------------------------------------------------
PW, PH = 440, 940   # screen area


def phone_art(draw_screen, bg=SLATE_D):
    """Draw_screen receives a draw on a PW x PH canvas. Wrap in bezel."""
    scr = Image.new("RGB", (PW, PH), bg)
    draw_screen(ImageDraw.Draw(scr))
    frame = Image.new("RGB", (PW + 36, PH + 36), (8, 12, 22))
    fd = ImageDraw.Draw(frame)
    fd.rounded_rectangle((9, 9, PW + 27, PH + 27), radius=34, outline=(52, 64, 88), width=3)
    frame.paste(scr, (18, 18))
    # notch
    fd.rounded_rectangle((PW // 2 - 70 + 18, 26, PW // 2 + 70 + 18, 44), radius=14, fill=(8, 12, 22))
    return frame


def status_bar(d, dark=True):
    c = WHITE if dark else INK
    d.text((24, 18), "9:41", font=f(FB, 24), fill=c)
    d.text((PW - 130, 20), "••••", font=f(FB, 18), fill=c)
    for i, x in enumerate(range(PW - 88, PW - 34, 18)):
        d.rounded_rectangle((x, 22, x + 3, 26), radius=1, fill=c)


def tab_bar(d, items, active=0):
    y = PH - 96
    d.rounded_rectangle((18, y, PW - 18, PH - 18), radius=28, fill=(16, 24, 42))
    for i, it in enumerate(items):
        cx = 18 + 34 + (PW - 36) * i / len(items)
        col = (255, 255, 255) if i == active else (110, 124, 148)
        d.ellipse((cx - 6, y + 22, cx + 6, y + 34), fill=col)
        d.text((cx - len(it) * 7, y + 46), it, font=f(FR, 19), fill=col)


def app_bar(d, title, right=True):
    d.text((26, 70), title, font=f(FB, 30), fill=WHITE)
    if right:
        d.rounded_rectangle((PW - 96, 66, PW - 40, 96), radius=15, fill=(30, 42, 64))


# --------------------------------------------------------------------------
# MUSIC APP
# --------------------------------------------------------------------------
def music_screen(d):
    status_bar(d)
    d.text((26, 220), "Good evening", font=f(FB, 34), fill=WHITE)
    hero = noise_photo(PW - 40, 250, (66, 60, 120), (26, 36, 70), seed=7)
    d.rounded_rectangle((20, 268, PW - 20, 518), radius=22, fill=SLATE_L)
    d.rounded_rectangle((20, 268, PW - 20, 518), radius=22, outline=(70, 84, 106), width=2)
    d.rounded_rectangle((22, 270, PW - 22, 518), radius=20)
    d.rounded_rectangle((22, 270, PW - 22, 518), radius=20, fill=(255, 255, 255, 0))
    d._image.paste(hero, (22, 270))
    # overlay
    ov = Image.new("RGBA", (PW - 44, 248), (0, 0, 0, 0))
    od = ImageDraw.Draw(ov)
    od.rectangle((0, 0, PW - 44, 248), fill=(0, 0, 0, 110))
    spect = [(x, 150 - random.Random(x).randint(10, 140)) for x in range(8, PW - 60, 26)]
    for sx, sh in spect:
        od.rectangle((sx, sh, sx + 14, 248), fill=(255, 255, 255, 120))
    ov = ov.filter(ImageFilter.GaussianBlur(1))
    d._image.paste(ov, (22, 270), ov)
    d.text((40, 300), "NEW RELEASE", font=f(FB, 18), fill=WHITE)
    d.text((40, 330), "Afterglow", font=f(FB, 40), fill=WHITE)
    d.text((40, 378), "Aurora Nights", font=f(FR, 22), fill=(196, 208, 230))
    d.rounded_rectangle((40, 430, 108, 486), radius=26, fill=PINK)
    d.ellipse((52, 446, 66, 462), fill=WHITE)
    d.polygon((58, 442, 58, 466, 76, 454), fill=WHITE)
    d.text((30, 545), "Made for you", font=f(FB, 28), fill=WHITE)
    tile = 96
    for i, (name, art) in enumerate([
        ("Lo-Fi Focus", (70, 92, 140)),
        ("Morning Run", (150, 80, 90)),
        ("Deep Work", (46, 110, 100)),
    ]):
        x = 26 + i * (tile + 14)
        d.rounded_rectangle((x, 590, x + tile, 686), radius=14, fill=SLATE_L)
        ph = noise_photo(tile, tile, art, tuple(int(v * 0.5) for v in art), seed=20 + i)
        d._image.paste(ph, (x, 590))
        d.text((x, 696), name, font=f(FB, 22), fill=WHITE)
        d.text((x, 724), "Aurora Nights", font=f(FR, 19), fill=GREY)
    d.text((30, 770), "Recently played", font=f(FB, 26), fill=WHITE)
    app_bar(d, "Library")

    # player bar
    d.rounded_rectangle((20, PH - 210, PW - 20, PH - 104), radius=20, fill=(24, 34, 56))
    ph = noise_photo(56, 56, (90, 70, 130), (30, 40, 70), seed=3)
    d._image.paste(ph, (38, PH - 194))
    d.rounded_rectangle((30, PH - 202, 102, PH - 130), radius=12)
    d.text((118, PH - 192), "Midnight City", font=f(FB, 22), fill=WHITE)
    d.text((118, PH - 162), "Neon Skyline", font=f(FR, 19), fill=GREY)
    d.rounded_rectangle((40, PH - 160, PW - 100, PH - 156), radius=2, fill=(60, 74, 98))
    d.rounded_rectangle((40, PH - 160, PW - 190, PH - 156), radius=2, fill=PINK)
    d.ellipse((PW - 110, PH - 186, PW - 60, PH - 136), fill=PINK)


# --------------------------------------------------------------------------
# BANK APP (light)
# --------------------------------------------------------------------------
def bank_screen(d):
    d.rectangle((0, 0, PW, PH), fill=(244, 246, 252))
    status_bar(d, dark=False)
    d.text((26, 70), "Good morning,", font=f(FR, 24), fill=GREY)
    d.text((26, 102), "Emmanuel", font=f(FB, 34), fill=INK)
    d.ellipse((PW - 92, 66, PW - 32, 126), fill=(216, 222, 244))
    d.text((PW - 82, 88), "EK", font=f(FB, 24), fill=INDIGO)
    # balance card
    card = noise_photo(PW - 40, 210, (90, 94, 240), (30, 36, 100), seed=11)
    d.rounded_rectangle((20, 150, PW - 20, 360), radius=24, fill=SLATE_D)
    d._image.paste(card, (22, 152))
    d.text((40, 178), "TOTAL BALANCE", font=f(FR, 18), fill=(203, 210, 235))
    d.text((40, 210), "$24,580.60", font=f(FB, 40), fill=WHITE)
    d.rounded_rectangle((40, 285, 130, 322), radius=16, fill=(255, 255, 255, 40))
    d.text((54, 295), "Top up", font=f(FB, 20), fill=WHITE)
    d.rounded_rectangle((148, 285, 238, 322), radius=16, fill=(255, 255, 255, 40))
    d.text((162, 295), "Send", font=f(FB, 20), fill=WHITE)
    # chart
    d.text((26, 392), "Spending this week", font=f(FB, 26), fill=INK)
    days = ["M", "T", "W", "T", "F", "S", "S"]
    vals = [52, 78, 40, 90, 62, 30, 44]
    for i in range(7):
        hgh = int(110 * vals[i] / 100)
        x = 40 + i * 58
        col = INDIGO if i == 5 else (203, 213, 240)
        d.rounded_rectangle((x, 470 - hgh, x + 30, 470), radius=8, fill=col)
        d.text((x + 6, 482), days[i], font=f(FR, 18), fill=GREY)
    # transactions
    d.text((26, 520), "Recent activity", font=f(FB, 26), fill=INK)
    txs = [
        ("Spotify Premium", "$9.99", PINK, 0),
        ("Salary Deposit", "+$1,850.00", GREEN, 1),
        ("M-Pesa Send", "−$120.00", INDIGO, 0),
        ("Umbrella Café", "−$14.50", AMBER, 0),
    ]
    y = 560
    for name, amt, col, up in txs:
        d.ellipse((30, y, 78, y + 48), fill=col + (40,))
        ch = PINK if up else (54, 76, 110)
        d.text((34, y + 8), name[0], font=f(FB, 22), fill=WHITE)
        d.text((94, y + 4), name, font=f(FR, 22), fill=INK)
        d.text((PW - 170, y + 4), amt, font=f(FB, 22), fill=INK)
        y += 66
    tab_bar(d, ["Home", "Cards", "Send"], active=0)


# --------------------------------------------------------------------------
# CHAT APP
# --------------------------------------------------------------------------
def chat_screen(d):
    status_bar(d)
    d.text((26, 70), "Team Elevate", font=f(FB, 30), fill=WHITE)
    d.text((26, 112), "12 members - 3 online", font=f(FR, 20), fill=GREY)
    msgs = [
        ("ny", "Shipping the new landing page today, final QA passed.", 90),
        ("me", "Amazing. Can you drop the design tokens in #tokens?", 190, True),
        ("ko", "On it now. Preview link on staging.", 290),
        ("ny", "Also resolved the auth flow bug from this morning.", 390),
        ("me", "Great work everyone. Let's wrap the review at 5pm.", 490, True),
    ]
    y = 150
    for m in msgs:
        who, txt, _, *me = m
        is_me = len(me) == 1
        d.ellipse((30, y, 66, y + 36), fill=PINK if not is_me else TEAL)
        d.text((36, y + 4), who[0].upper(), font=f(FB, 18), fill=SLATE_D)
        bx = 80 if not is_me else 150
        bw = PW - bx - 24
        col = (31, 44, 68) if not is_me else (58, 62, 200)
        lines, cur = [], ""
        for wd in txt.split():
            if d.textlength((cur + " " + wd).strip(), font=f(FR, 21)) <= bw - 28:
                cur = (cur + " " + wd).strip()
            else:
                lines.append(cur)
                cur = wd
        lines.append(cur)
        hh = len(lines) * 30 + 20
        d.rounded_rectangle((bx, y, bx + bw, y + hh), radius=18, fill=col)
        ly = y + 12
        for ln in lines:
            d.text((bx + 14, ly), ln, font=f(FR, 21), fill=WHITE)
            ly += 30
        y += hh + 16
    app_bar(d, "Chats")
    # input
    d.rounded_rectangle((20, PH - 96, PW - 20, PH - 26), radius=22, fill=(24, 34, 56))
    d.text((44, PH - 72), "+  Send a message...", font=f(FR, 22), fill=GREY)


# --------------------------------------------------------------------------
# FOOD APP
# --------------------------------------------------------------------------
def food_screen(d):
    status_bar(d)
    d.text((26, 70), "Deliver to", font=f(FR, 20), fill=GREY)
    d.text((26, 100), "Cedar Heights, Nairobi", font=f(FB, 26), fill=WHITE)
    d.text((PW - 90, 100), "⌄", font=f(FB, 24), fill=AMBER)
    d.rounded_rectangle((20, 138, PW - 20, 190), radius=18, fill=(24, 34, 56))
    d.line((60, 166, PW - 44, 166), fill=(84, 100, 128), width=3)
    d.ellipse((36, 160, 48, 172), fill=GREY)
    d.text((76, 156), "Search dishes, restaurants...", font=f(FR, 22), fill=GREY)
    banner = noise_photo(PW - 40, 170, (220, 150, 90), (140, 60, 50), seed=13)
    d.rounded_rectangle((20, 208, PW - 20, 378), radius=22, fill=SLATE_L)
    d.rounded_rectangle((22, 210, PW - 22, 378), radius=20)
    d._image.paste(banner, (22, 210))
    ov = Image.new("RGBA", (PW - 44, 168), (0, 0, 0, 0))
    od = ImageDraw.Draw(ov)
    od.rectangle((0, 0, PW - 44, 168), fill=(0, 0, 0, 100))
    ov = ov.filter(ImageFilter.GaussianBlur(1))
    d._image.paste(ov, (22, 210), ov)
    d.text((42, 238), "20% OFF your first order", font=f(FB, 30), fill=WHITE)
    d.text((42, 286), "Use code ELEVATE20", font=f(FR, 22), fill=(255, 222, 190))
    d.rounded_rectangle((42, 318, 176, 352), radius=16, fill=AMBER)
    d.text((58, 327), "Order now", font=f(FB, 20), fill=SLATE_D)
    cat = [(("Pizza", 12, (200, 120, 80)), ("Burgers", 22, (140, 90, 60)),
            ("Sushi", 32, (80, 130, 120)), ("Salads", 42, (80, 140, 80)))]
    d.text((26, 400), "Popular near you", font=f(FB, 28), fill=WHITE)
    rest = [
        ("Umbrella Café", "$10-25", "4.8 ★", (30, 40, 90), 14),
        ("Savanna Grill", "$15-40", "4.9 ★", (120, 60, 40), 15),
        ("Coast Tavern", "$8-20", "4.7 ★", (30, 90, 100), 16),
    ]
    for i, (name, price, stars, base, seed) in enumerate(rest):
        y = 450 + i * 152
        d.rounded_rectangle((20, y, PW - 20, y + 138), radius=20, fill=(24, 34, 56))
        ph = noise_photo(120, 118, base, tuple(int(v * 0.4) for v in base), seed=seed)
        d.rounded_rectangle((28, y + 10, 148, y + 128), radius=14, fill=SLATE_L)
        d._image.paste(ph, (30, y + 12))
        d.text((164, y + 18), name, font=f(FB, 26), fill=WHITE)
        d.text((164, y + 54), f"{price}  •  {stars}", font=f(FR, 21), fill=GREY)
        d.rounded_rectangle((164, y + 90, 248, y + 124), radius=14, fill=TEAL)
        d.text((176, y + 100), "Order", font=f(FB, 20), fill=SLATE_D)
        d.ellipse((PW - 120, y + 40, PW - 40, y + 120), fill=SLATE_L)
        d.line((PW - 92, y + 60, PW - 68, y + 100), fill=GREEN, width=6)
        d.line((PW - 68, y + 100, PW - 42, y + 60), fill=GREEN, width=6)
    tab_bar(d, ["Home", "Search", "Orders", "Cart"], active=0)


# --------------------------------------------------------------------------
# BROWSER helper
# --------------------------------------------------------------------------
BW, BH = 920, 640


def browser_art(draw_content, bg=WHITE):
    """Full browser window incl. chrome."""
    win = Image.new("RGB", (BW, BH), bg)
    d = ImageDraw.Draw(win)
    # chrome
    d.rounded_rectangle((0, 0, BW, 46), radius=12, fill=(30, 38, 56))
    d.rounded_rectangle((0, 0, BW, 46), radius=12)
    for i, c in enumerate([(236, 90, 90), (242, 177, 77), (90, 200, 120)]):
        d.ellipse((16 + i * 26, 15, 34 + i * 26, 33), fill=c)
    d.rounded_rectangle((120, 12, BW - 20, 36), radius=12, fill=(52, 63, 88))
    d.text((BW - 146, 16), "https://elevate-media-productions.vercel.app", font=f(FR, 16), fill=(200, 208, 226))

    content = Image.new("RGB", (BW, BH - 46), bg)
    draw_content(ImageDraw.Draw(content), content)
    win.paste(content, (0, 46))
    return win


def hero_section(d, img):
    # navbar
    d.rounded_rectangle((20, 20, BW - 20, 86), radius=18, fill=(250, 251, 255))
    d.text((40, 34), "ELEVATE", font=f(FB, 26), fill=INK)
    d.text((120, 44), "(logo)", font=f(FR, 18), fill=GREY)
    for i, lnk in enumerate(("About", "Services", "Work", "Contact")):
        d.text((BW - 420 + i * 92, 44), lnk, font=f(FR, 20), fill=(100, 110, 130))
    d.rounded_rectangle((BW - 160, 30, BW - 44, 76), radius=14, fill=INK)
    d.text((BW - 146, 40), "Let's talk", font=f(FB, 22), fill=WHITE)
    # left copy
    d.text((40, 150), "DIGITAL PRODUCT STUDIO", font=f(FB, 20), fill=INDIGO)
    d.text((40, 190), "We craft digital\nexperiences that", font=f(FB, 56), fill=INK)
    d.text((368, 190), "elevate brands.", font=f(FB, 56), fill=INDIGO)
    d.text((40, 330), "Web applications, mobile apps and brand platforms", font=f(FR, 24), fill=(110, 120, 140))
    d.text((40, 360), "that help organizations launch, grow and engage.", font=f(FR, 24), fill=(110, 120, 140))
    d.rounded_rectangle((40, 410, 210, 464), radius=16, fill=INK)
    d.text((62, 425), "Start a project", font=f(FB, 22), fill=WHITE)
    d.rounded_rectangle((230, 410, 400, 464), radius=16, outline=(200, 208, 228), width=2)
    d.text((258, 425), "Browse work", font=f(FB, 22), fill=INK)


def feature_row(d):
    for i, (t, col) in enumerate((("Web apps", INDIGO), ("Mobile", PINK), ("Branding", TEAL))):
        x = 40 + i * 300
        d.rounded_rectangle((x, 500, x + 270, 340 + 210), radius=18, fill=(23, 33, 58))
        d.ellipse((x + 24, 528, x + 84, 588), fill=col)
        d.text((x + 40, 548), t[0], font=f(FB, 26), fill=WHITE)
        d.text((x + 24, 606), t, font=f(FB, 30), fill=WHITE)
        d.text((x + 24, 650), "Full-stack build, designed to", font=f(FR, 22), fill=(170, 182, 204))
        d.text((x + 24, 680), "scale from idea to launch.", font=f(FR, 22), fill=(170, 182, 204))


# --------------------------------------------------------------------------
# WEB HOME
# --------------------------------------------------------------------------
def web_home(d, img):
    img.paste(noise_photo(BW, BH - 46, (18, 26, 52), (8, 12, 26)), (0, 0))
    d.rectangle((0, 512, BW, BH - 46), fill=(23, 33, 58))
    hero_section(d, img)
    feature_row(d)


def web_dash(d, img):
    img.paste(noise_photo(BW, BH - 46, (246, 248, 253), (230, 236, 247)), (0, 0))
    d.rectangle((0, 0, 92, BH - 46), fill=(23, 33, 58))
    d.text((24, 60), "◆", font=f(FB, 26), fill=INDIGO)
    for i in range(8):
        d.rounded_rectangle((20, 100 + i * 54, 72, 136 + i * 54), radius=10, fill=(36, 48, 76))
    d.text((28, 128 + 3 * 54), "📊", font=f(FB, 22), fill=WHITE)  # noqa: E501
    x0 = 120
    d.text((x0, 22), "Overview", font=f(FB, 30), fill=INK)
    d.text((x0, 60), "Good day, Emmanuel", font=f(FR, 21), fill=GREY)
    kpis = [("Revenue", "$48,290", "+12.4%", GREEN), ("Users", "12,480", "+8.1%", GREEN),
            ("Conversion", "3.4%", "+0.6%", GREEN)]
    for i, (lbl, val, delta, col) in enumerate(kpis):
        x = x0 + i * 270
        d.rounded_rectangle((x, 100, x + 240, 180), radius=16, fill=WHITE)
        d.ellipse((x + 16, 158, x + 40, 182), fill=col)
        d.text((x + 18, 112), lbl.upper(), font=f(FB, 18), fill=GREY)
        d.text((x + 18, 136), val, font=f(FB, 34), fill=INK)
    d.rounded_rectangle((x0, 200, x0 + 500, 400), radius=16, fill=WHITE)
    d.text((x0 + 20, 216), "Weekly growth", font=f(FB, 24), fill=INK)
    pts = [180, 120, 160, 90, 150, 200, 255]
    for i in range(len(pts) - 1):
        d.line((x0 + 60 + i * 80, 380 - pts[i], x0 + 60 + (i + 1) * 80, 380 - pts[i + 1]),
               fill=INDIGO, width=6)
    for i, v in enumerate(pts):
        d.ellipse((x0 + 52 + i * 80, 372 - v, x0 + 68 + i * 80, 388 - v), fill=PINK)
    d.rounded_rectangle((x0 + 520, 200, x0 + 760, 400), radius=16, fill=WHITE)
    d.text((x0 + 540, 216), "Top projects", font=f(FB, 24), fill=INK)
    proj = [("Elevate University", 92), ("Civic CMS", 78), ("TrackSpend", 64)]
    for i, (nm, pct) in enumerate(proj):
        yy = 270 + i * 52
        d.text((x0 + 540, yy), nm, font=f(FR, 21), fill=INK)
        d.rounded_rectangle((x0 + 540, yy + 26, x0 + 760, yy + 38), radius=6, fill=(228, 233, 244))
        d.rounded_rectangle((x0 + 540, yy + 26, x0 + 540 + int(220 * pct / 100), yy + 38), radius=6, fill=INDIGO)


# --------------------------------------------------------------------------
def main():
    def run(name, fn, kind="phone"):
        if kind == "phone":
            art = phone_art(fn)
        else:
            art = browser_art(fn)
        art.save(os.path.join(A, name))
        print("saved", name, art.size)

    run("music_phone.png", music_screen)
    run("bank_phone.png", bank_screen)
    run("chat_phone.png", chat_screen)
    run("food_phone.png", food_screen)
    run("web_home.png", web_home, "browser")
    run("web_dash.png", web_dash, "browser")


if __name__ == "__main__":
    main()
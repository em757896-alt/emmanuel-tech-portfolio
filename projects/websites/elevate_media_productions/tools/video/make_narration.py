"""Generate per-scene voiceover narration with edge-tts (neural, authentic).
Each clip's duration is measured so the video scenes can be timed to the voice.
No background music anywhere.
"""

import asyncio
import json
import os
import subprocess

VOICE = "en-US-AndrewMultilingualNeural"
RATE = "-4%"  # slightly slower = confident, calm
BASE = os.path.dirname(os.path.abspath(__file__))
NARR_DIR = os.path.join(BASE, "narration")
os.makedirs(NARR_DIR, exist_ok=True)

# Scene script: one line per scene, timed to flow naturally.
SCRIPT = [
    "Welcome to Elevate Media Productions, where world-class apps and websites are born.",
    "If you're watching this, you're looking for more than just a website. You want a brand that people remember, trust, and return to.",
    "We build mobile apps that feel effortless, and websites that turn visitors into customers, and customers into loyal fans.",
    "Every pixel is intentional. Every animation has purpose. We obsess over the details, because quality is what truly sets you apart.",
    "From startups taking their first steps, to established businesses ready to level up, we do it all, with speed and precision.",
    "Our developers craft clean, secure, lightning-fast code. Our designers shape experiences people genuinely love to use.",
    "And that's what makes Elevate Media Productions the best brand in app and website development. We don't just deliver projects. We deliver results.",
    "Your brand deserves to stand out. Your customers deserve the best. And you deserve a team that treats your vision like it's their own.",
    "So let's build something unforgettable, together. Visit our website, follow Elevate Media Productions, and start your journey today.",
    "Elevate Media Productions. Where your digital future comes to life.",
]


def get_duration(path: str) -> float:
    ff = "C:/Users/Emmanuel/AppData/Local/Programs/Python/Python311/Lib/site-packages/imageio_ffmpeg/binaries/ffmpeg-win-x86_64-v7.1.exe"
    # drop -i duplicate; probe with ffprobe not available, use ffmpeg decode
    out = subprocess.run(
        [ff, "-i", path, "-f", "null", "-"],
        capture_output=True, text=True,
    ).stderr
    for line in out.splitlines():
        if "time=" in line:
            parts = [p for p in line.split() if p.startswith("time=")]
            if parts:
                t = parts[-1].split("=")[1]
                hh, mm, ss = t.split(":")
                return int(hh) * 3600 + int(mm) * 60 + float(ss)
    return 0.0


async def make_voice(text: str, out: str):
    import edge_tts

    tts = edge_tts.Communicate(text, VOICE, rate=RATE)
    await tts.save(out)


async def main():
    metas = []
    for i, line in enumerate(SCRIPT):
        out = os.path.join(NARR_DIR, f"scene{i + 1:02d}.mp3")
        await make_voice(line, out)
        dur = get_duration(out)
        metas.append({"index": i, "line": line, "file": out, "duration": round(dur, 2)})
        print(f"scene{i + 1:02d}: {dur:.1f}s  {line[:60]}")

    total_speech = sum(m["duration"] for m in metas)
    print("=" * 50)
    print("Total speech seconds:", round(total_speech, 1))
    with open(os.path.join(BASE, "narration_meta.json"), "w", encoding="utf-8") as f:
        json.dump(metas, f, indent=2, ensure_ascii=False)


if __name__ == "__main__":
    asyncio.run(main())
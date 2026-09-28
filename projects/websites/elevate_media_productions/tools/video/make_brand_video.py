"""
Elevate Media Productions — Local Brand Video Generator
Builds cinematic promo videos (up to 1080p, 30fps) from images + text using
ffmpeg zoompan/drawtext/xfade filters, with optional per-scene voiceover
narration synced to each scene. Runs entirely offline, low RAM usage.

Requirements: python 3.11 + imageio-ffmpeg (bundles ffmpeg).
Safety: no Python frame processing (ffmpeg does all the work) — safe on 4GB RAM.

Usage:
  python make_brand_video.py --config scenes.json --out promo.mp4
"""

import argparse
import json
import os
import subprocess
import sys

try:
    import imageio_ffmpeg

    FFMPEG = imageio_ffmpeg.get_ffmpeg_exe()
except Exception:
    FFMPEG = "ffmpeg"


def esc(s: str) -> str:
    """Escape text for a drawtext filter value."""
    return (
        s.replace("\\", "\\\\")
        .replace(":", "\\:")
        .replace("'", "\\'")
        .replace(",", "\\,")
        .replace(";", "\\;")
        .replace("[", "\\[")
        .replace("]", "\\]")
        .replace("(", "\\(")
        .replace(")", "\\)")
        .replace("%", "\\%")
    )


def resolve(path: str, base: str) -> str:
    return path if os.path.isabs(path) else os.path.join(base, path)


def scene_filter(sc: dict, w: int, h: int, fps: int) -> str:
    """One single-frame image input -> Ken Burns clip labelled [v{i}]."""
    zoom = max(float(sc.get("zoom", 1.0)), 1.0)
    zoom_end = max(float(sc.get("zoom_end", zoom)), 1.0)
    px = float(sc.get("pan_x", 0.0))
    py = float(sc.get("pan_y", 0.0))
    dur = max(float(sc.get("duration", 4)), 1.0)
    frames = max(int(round(dur * fps)), 1)
    step = (zoom_end - zoom) / max(frames - 1, 1)
    fs = int(sc.get("font_size", 64))
    sub_fs = max(int(fs * 0.42), 20)
    text = sc.get("text", "") or ""
    sub = sc.get("subtext", "") or ""
    tcolor = sc.get("text_color", "#ffffff")
    scolor = sc.get("sub_color", "#c7d2fe")
    i = sc["_i"]

    zexpr = f"min({zoom}+{step:g}*on,{zoom_end})"
    x = "(iw-iw/zoom)*" + str(px)
    y = "(ih-ih/zoom)*" + str(py)

    f = (
        f"scale={w}:{h}:force_original_aspect_ratio=increase,"
        f"crop={w}:{h},"
        f"zoompan=z='{zexpr}':x='{x}':y='{y}':d={frames}:fps={fps}:s={w}x{h},"
        f"format=yuv420p"
    )
    if text:
        f += (
            f",drawtext=text='{esc(text)}':fontsize={fs}:fontcolor={esc(tcolor)}"
            f":x=(w-text_w)/2:y=(h-text_h)/2:box=1:boxcolor=black@0.3:boxborderw=18"
        )
        if sub:
            f += (
                f",drawtext=text='{esc(sub)}':fontsize={sub_fs}:fontcolor={esc(scolor)}"
                f":x=(w-text_w)/2:y=(h-text_h)/2+{fs}*0.85:box=1:boxcolor=black@0.22:boxborderw=12"
            )
    return f"[{i}:v]{f}[v{i}]"


def main():
    parser = argparse.ArgumentParser(description="Local brand video generator (ffmpeg zoompan)")
    parser.add_argument("--config", required=True, help="Path to JSON scene config")
    parser.add_argument("--out", help="Output file (overrides config output_name)")
    parser.add_argument("--ffmpeg", default=FFMPEG, help="ffmpeg binary path")
    args = parser.parse_args()

    base = os.path.dirname(os.path.abspath(args.config))
    with open(args.config, "r", encoding="utf-8") as f:
        cfg = json.load(f)

    w = int(cfg.get("width", 1920))
    h = int(cfg.get("height", 1080))
    fps = int(cfg.get("fps", 30))
    out_name = args.out or cfg.get("output_name", "promo.mp4")
    out = resolve(out_name, base)
    music = resolve(cfg["music"], base) if cfg.get("music") else None
    music_vol = float(cfg.get("music_volume", 0.15))
    scenes = cfg["scenes"]
    n = len(scenes)
    durations = [max(float(s.get("duration", 4)), 1.0) for s in scenes]
    total = sum(durations)

    cmd = [args.ffmpeg, "-y"]
    for i, sc in enumerate(scenes):
        sc["_i"] = i
        img = resolve(sc.get("image", ""), base)
        if not os.path.exists(img):
            print(f"ERROR: image not found: {img}")
            sys.exit(1)
        cmd += ["-i", img]

    # Collect narration audio inputs (after image inputs)
    narration_files = []
    for i, sc in enumerate(scenes):
        nfile = sc.get("narrate")
        if nfile:
            p = resolve(nfile, base)
            if not os.path.exists(p):
                print(f"ERROR: narration not found: {p}")
                sys.exit(1)
            narration_files.append((i, p))
    if music:
        if not os.path.exists(music):
            print(f"ERROR: music not found: {music}")
            sys.exit(1)
    # Inputs: images first, then narrations, then music
    for _, p in narration_files:
        cmd += ["-i", p]
    if music:
        cmd += ["-i", music]

    chain = [scene_filter(sc, w, h, fps) for sc in scenes]
    filtergraph = ";".join(chain)

    # Crossfade transitions between scenes (offsets must accumulate)
    prev = "v0"
    xfade_dur = min(0.6, min(durations) * 0.15)
    running = durations[0]
    for i in range(1, n):
        label_out = f"vxc{i}" if i < n - 1 else "vout"
        offset = max(running - i * xfade_dur, 0)
        filtergraph += (
            f";[{prev}][v{i}]xfade=transition=fade:duration={xfade_dur}"
            f":offset={offset}[{label_out}]"
        )
        running += durations[i]
        prev = label_out

    # Audio: place each narration at its scene's start time
    audio_inputs = []
    start_time = [0.0] * n
    acc = 0.0
    for i in range(n):
        start_time[i] = acc
        acc += durations[i]

    # For a given scene, its narration should appear near `start_time[i]`.
    # Account for xfade shrink: actual scene start = cum_sum - i*xfade_dur.
    for si in range(1, n):
        start_time[si] -= si * xfade_dur

    for k, (scene_i, _) in enumerate(narration_files):
        idx_in_cmd = n + k  # audio stream index in processed inputs
        delay_ms = int(max(start_time[scene_i], 0) * 1000)
        label = f"[nar{k}]"
        filtergraph += (
            f";[{idx_in_cmd}:a]aresample=48000,adelay={delay_ms}|{delay_ms}{label}"
        )
        audio_inputs.append(label)

    if music:
        m_idx = n + len(narration_files)
        label = f"[mus]"
        filtergraph += (
            f";[{m_idx}:a]aresample=48000,volume={music_vol}{label}"
        )
        audio_inputs.append(label)

    if audio_inputs:
        amix_in = "".join(audio_inputs)
        filtergraph += (
            f";{amix_in}amix=inputs={len(audio_inputs)}:duration=longest:normalize=0,"
            f"apad,atrim=0:{total},asetpts=PTS-STARTPTS[aout]"
        )

    last_out = "vout" if n > 1 else "v0"
    filtergraph += (
        f";[{last_out}]trim=duration={total},setpts=PTS-STARTPTS,"
        f"format=yuv420p[vfinal]"
    )

    cmd += ["-filter_complex", filtergraph, "-map", "[vfinal]"]
    if audio_inputs:
        cmd += ["-map", "[aout]", "-c:a", "aac", "-b:a", "160k", "-shortest"]
    cmd += ["-c:v", "libx264", "-preset", "veryfast", "-crf", "20",
            "-movflags", "+faststart", out]

    with open(out + ".cmd.txt", "w", encoding="utf-8") as f:
        f.write(" ".join(cmd))

    print("Encoding", n, "scenes ->", out, f"({total}s)", "(this may take a minute)")
    proc = subprocess.run(cmd, capture_output=True, text=True)
    if proc.returncode != 0:
        print("FFMPEG FAILED:")
        print(proc.stderr[-4000:])
        sys.exit(1)
    print(f"DONE: {out} ({os.path.getsize(out) / 1e6:.1f} MB)")


if __name__ == "__main__":
    main()
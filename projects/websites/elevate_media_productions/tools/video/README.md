# Elevate Media — Local Brand Video Generator

Build cinematic promo videos from images + text, **100% offline, unlimited, free**.
Runs entirely on this laptop — no internet, no cloud, no per-video costs.

Uses ffmpeg (bundled via `imageio-ffmpeg`) Ken Burns zoom/pan, text overlay and
crossfade transitions. Light on RAM (no Python frame processing — ffmpeg does
the work), safe on a 4GB RAM machine.

## How to use

1. Put your images in `assets/` (or any folder; paths in the config are relative to the config file).
2. Edit `scenes.json` — one entry per scene (image + text + duration + motion).
3. Run:

```
python make_brand_video.py --config scenes.json --out my_video.mp4
```

Output is 1080p, 30fps, `.mp4`, ready for YouTube/Instagram/web.

## Config options

Top level:

| Key | Default | Meaning |
|---|---|---|
| `width` / `height` | 1920/1080 | Output resolution |
| `fps` | 30 | Frames per second |
| `output_name` | `promo.mp4` | Output filename |
| `music` | *(none)* | Optional `.mp3`/`.wav` music file |
| `music_volume` | 0.15 | Music loudness (0–1) |

Per scene:

| Key | Default | Meaning |
|---|---|---|
| `image` | required | Image file path |
| `text` | "" | Main headline shown on the image |
| `subtext` | "" | Secondary line under the headline |
| `duration` | 4 | Seconds this scene is on screen |
| `zoom` / `zoom_end` | 1.0 | Ken Burns: zoom in (e.g. 1.0 → 1.12) or out (1.1 → 1.0) |
| `pan_x` / `pan_y` | 0 | Pan within the zoom: 0 = left/top, 1 = right/bottom, 0.5 = center |
| `font_size` | 64 | Headline size |
| `text_color` / `sub_color` | white / light indigo | Text colors (hex) |

## Example

```json
{
  "scenes": [
    { "image": "assets/slide1.png", "text": "Elevate Media Productions", "subtext": "Cinematic brand videos", "duration": 4, "zoom": 1.0, "zoom_end": 1.12 },
    { "image": "assets/slide2.png", "text": "Your Story, Cinematically", "duration": 4, "zoom": 1.1, "zoom_end": 1.0 },
    { "image": "assets/slide3.png", "text": "Unlimited Local Workflow", "duration": 4, "zoom": 1.0, "zoom_end": 1.15, "pan_x": 0.5, "pan_y": 0.5 }
  ]
}
```

## Notes

- Use up to 1080p for fast renders. For social/vertical (Reels/Shorts) set
  `"width": 1080, "height": 1920` and images cropped to that ratio.
- To generate the AI-style brand images themselves (free, unlimited): run
  Stable Diffusion / Flux locally via `make_sample_images.py`-style PIL code, or
  use a free browser tool (e.g. Bing Image Creator, Ideogram free tier), then
  feed the images into this pipeline.
- Re-rendering is cheap — tweak a scene, rerun, done.
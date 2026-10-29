
from pathlib import Path

OLD_URL = "trackspend.vercel.app"
NEW_URL = "trackspend-jade.vercel.app"

# Search common source and configuration files.
EXTENSIONS = {
    ".tsx", ".ts", ".jsx", ".js", ".json",
    ".html", ".md", ".mdx", ".yaml", ".yml"
}

ROOT = Path(__file__).resolve().parent
changed_files = []

for path in ROOT.rglob("*"):
    if not path.is_file():
        continue

    # Skip dependencies, Git history, and build outputs.
    if any(part in {
        ".git", "node_modules", ".next",
        "dist", "build", ".vercel"
    } for part in path.parts):
        continue

    if path.suffix.lower() not in EXTENSIONS:
        continue

    try:
        content = path.read_text(encoding="utf-8")
    except (UnicodeDecodeError, OSError):
        continue

    if OLD_URL not in content:
        continue

    updated = content.replace(OLD_URL, NEW_URL)
    path.write_text(updated, encoding="utf-8")
    changed_files.append(path.relative_to(ROOT))

if changed_files:
    print("Updated TrackSpend Live link:")
    for path in changed_files:
        print(f"  - {path}")
    print("\nReview the changes, then commit and push to GitHub.")
else:
    print("Old URL not found in the searched source files.")
    print("Check the portfolio's project data or search for the old URL.")

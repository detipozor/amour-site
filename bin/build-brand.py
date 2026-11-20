#!/usr/bin/env python3
"""Builds the theme's logo files from the package masters in assets/brand.

Run it again after the package changes:  python3 bin/build-brand.py

What it does to each master (and nothing else, the outlines stay as they are):
- drops the background rectangle and the fixed size, so CSS sets the size;
- paints with currentColor, so the colour comes from a token (CLAUDE.md: colours only through tokens);
- for the big footer words, keeps only the letters and frames them tightly (the same frame as on the boards).
"""
import pathlib
import re
import shutil

ROOT = pathlib.Path(__file__).resolve().parent.parent
BRAND = ROOT / 'assets' / 'brand'
OUT = ROOT / 'wp-content' / 'themes' / 'amour' / 'assets' / 'brand'

# name: (master file, frame) — frame None keeps the master's own viewBox and groups,
# frame (viewBox, group transform) keeps only the first group (the letters) in a tight frame.
LOGOS = {
    'amour-horizontal': ('amour/2_lockups/lockup_horizontal_transparent.svg', None),
    'amour-main': ('amour/2_lockups/lockup_main_detailed_transparent.svg', None),
    'amour-word': ('amour/2_lockups/wordmark_transparent.svg', ('79 78 673 106', 'translate(80.00,80.00)')),
    'eudora-horizontal': ('eudora/2_lockups/lockup_horizontal_simplified_transparent.svg', None),
    'eudora-word': ('eudora/2_lockups/wordmark_nosub_light.svg', ('258.9 88.24 732.6 103.9', 'translate(252.48,90.00)')),
    'olive': ('amour/1_mark/tree_3_pictogram/tree_3_pictogram_terracotta.svg', None),
    'jug': ('eudora/1_mark/jug_3_pictogram/jug_3_pictogram_sea.svg', None),
}


def clean(svg: str, frame) -> str:
    view_box = re.search(r'viewBox="([^"]+)"', svg).group(1)
    body = svg[svg.index('>', svg.index('<svg')) + 1: svg.rindex('</svg>')]
    body = re.sub(r'<rect\b[^>]*/>', '', body)  # background
    body = re.sub(r'\sfill="#[0-9A-Fa-f]{3,8}"', '', body)  # colour comes from currentColor
    if frame:
        view_box, transform = frame
        group = re.search(r'<g transform="[^"]+">(.*?)</g>', body, re.S).group(1)
        body = f'<g transform="{transform}">{group}</g>'
    # The theme shows the file through <svg><use href="file.svg#logo"></svg>: the browser downloads it once,
    # and the colour still comes from the page (currentColor).
    return (
        f'<svg xmlns="http://www.w3.org/2000/svg" viewBox="{view_box}" fill="currentColor">'
        f'<g id="logo">{body.strip()}</g></svg>\n'
    )


def main():
    OUT.mkdir(parents=True, exist_ok=True)
    for name, (master, frame) in LOGOS.items():
        source = (BRAND / master).read_text(encoding='utf-8')
        (OUT / f'{name}.svg').write_text(clean(source, frame), encoding='utf-8')
        print(f'{name}.svg  <-  {master}')

    favicons = OUT / 'favicon'
    for brand in ('amour', 'eudora'):
        target = favicons / brand
        target.mkdir(parents=True, exist_ok=True)
        for png in sorted((BRAND / brand / '5_favicon').glob('favicon_*.png')):
            shutil.copy2(png, target / png.name)
        print(f'favicon/{brand}/  <-  {brand}/5_favicon')


if __name__ == '__main__':
    main()

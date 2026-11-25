#!/usr/bin/env python3
"""Builds the theme's logo files from the package masters in assets/brand.

Run it again after the package changes:  python3 bin/build-brand.py

What it does to each master (and nothing else):
- drops the background rectangle and the fixed size, so CSS sets the size;
- paints with currentColor, so the colour comes from a token (CLAUDE.md: colours only through tokens);
- for the big footer words, keeps only the letters and frames them tightly (the same frame as on the boards);
- drops the points the eye cannot see (agreed 25.11.2026). The masters are traced with a point every
  0.01-1 unit, up to 18,000 points per logo: 650 KB that held up the first screen. A point goes only if
  the outline moves by less than the tolerance below, under 0.06 px at the largest size on the site.
  The masters in assets/brand stay as they are.
"""
import math
import pathlib
import re
import shutil

ROOT = pathlib.Path(__file__).resolve().parent.parent
BRAND = ROOT / 'assets' / 'brand'
OUT = ROOT / 'wp-content' / 'themes' / 'amour' / 'assets' / 'brand'

# name: (master file, frame, tolerance).
# Frame None keeps the master's own viewBox and groups; frame (viewBox, group transform) keeps only the first
# group (the letters) in a tight frame. Tolerance: how far the outline may move, in the units of the path,
# and what that is at the largest size on the site (measured on a 2560 px screen).
LOGOS = {
    # header, menu and curtain, 76 px high: 1 unit = 0.059 px, so 0.5 = 0.03 px
    'amour-horizontal': ('amour/2_lockups/lockup_horizontal_transparent.svg', None, 0.5),
    # footer, 200 px wide: 1 unit = 0.099 px, so 0.5 = 0.05 px
    'amour-main': ('amour/2_lockups/lockup_main_detailed_transparent.svg', None, 0.5),
    # first screen and footer, up to 2448 px wide: 1 unit = 2.9 px, so 0.02 = 0.06 px
    'amour-word': ('amour/2_lockups/wordmark_transparent.svg', ('79 78 673 106', 'translate(80.00,80.00)'), 0.02),
    # villa header, 76 px high: 1 unit = 0.040 px, so 0.5 = 0.02 px
    'eudora-horizontal': ('eudora/2_lockups/lockup_horizontal_simplified_transparent.svg', None, 0.5),
    'eudora-word': ('eudora/2_lockups/wordmark_nosub_light.svg', ('258.9 88.24 732.6 103.9', 'translate(252.48,90.00)'), 0.02),
    # olive field in the footer, 127 px wide: 1 unit = 0.081 px, so 0.5 = 0.04 px
    'olive': ('amour/1_mark/tree_3_pictogram/tree_3_pictogram_terracotta.svg', None, 0.5),
    # 206 points already
    'jug': ('eudora/1_mark/jug_3_pictogram/jug_3_pictogram_sea.svg', None, 0),
}

TOKEN = re.compile(r'[MLHVQCZ]|[-+]?(?:\d+\.?\d*|\.\d+)(?:[eE][-+]?\d+)?')
ARGS = {'M': 2, 'L': 2, 'H': 1, 'V': 1, 'Q': 4, 'C': 6, 'Z': 0}


def parse(d: str) -> list:
    """Path data as [command, [numbers as written]]; the masters use absolute commands only."""
    out, cmd, i = [], None, 0
    tokens = TOKEN.findall(d)
    while i < len(tokens):
        if tokens[i].isalpha():
            cmd = tokens[i]
            i += 1
            if cmd == 'Z':
                out.append(['Z', []])
                continue
        count = ARGS[cmd]
        out.append([cmd, tokens[i:i + count]])
        i += count
        if cmd == 'M':
            cmd = 'L'  # pairs after a moveto are linetos
    return out


def keep_points(points: list, tolerance: float) -> list:
    """Douglas-Peucker: keeps the ends and every point that is further than the tolerance from the line."""
    keep = [False] * len(points)
    keep[0] = keep[-1] = True
    stack = [(0, len(points) - 1)]
    while stack:
        a, b = stack.pop()
        (ax, ay), (bx, by) = points[a], points[b]
        dx, dy = bx - ax, by - ay
        length = math.hypot(dx, dy)
        far, index = -1.0, None
        for k in range(a + 1, b):
            px, py = points[k]
            dist = abs(dy * (px - ax) - dx * (py - ay)) / length if length else math.hypot(px - ax, py - ay)
            if dist > far:
                far, index = dist, k
        if index is not None and far > tolerance:
            keep[index] = True
            stack += [(a, index), (index, b)]
    return keep


def slim(d: str, tolerance: float) -> str:
    """Drops lineto points that move the outline by less than the tolerance. Curves, moves and closes stay;
    the numbers that stay are written as in the master."""
    segments = parse(d)
    ends, current, start = [], None, None
    for cmd, nums in segments:
        f = [float(x) for x in nums]
        if cmd in 'ML':
            current = (f[0], f[1])
        elif cmd == 'H':
            current = (f[0], current[1])
        elif cmd == 'V':
            current = (current[0], f[0])
        elif cmd == 'Q':
            current = (f[2], f[3])
        elif cmd == 'C':
            current = (f[4], f[5])
        if cmd == 'M':
            start = current
        if cmd == 'Z':
            current = start
        ends.append(current)

    keep = [True] * len(segments)
    k = 0
    while k < len(segments):
        if segments[k][0] != 'L':
            k += 1
            continue
        last = k
        while last + 1 < len(segments) and segments[last + 1][0] == 'L':
            last += 1
        run = list(range(k - 1, last + 1))  # from the point before the run to its last point
        for index, kept in zip(run[1:], keep_points([ends[x] for x in run], tolerance)[1:]):
            keep[index] = kept
        k = last + 1

    out, previous = [], None
    for (cmd, nums), kept in zip(segments, keep):
        if not kept:
            continue
        if cmd == 'Z':
            out.append('Z')
            previous = 'Z'
            continue
        nums = [x[:-2] if x.endswith('.0') else x for x in nums]
        body = nums[0] if cmd in 'HV' else ' '.join(','.join(nums[j:j + 2]) for j in range(0, len(nums), 2))
        out.append((' ' if cmd == previous else cmd) + body)
        previous = cmd
    return ''.join(out)


def clean(svg: str, frame, tolerance: float) -> str:
    view_box = re.search(r'viewBox="([^"]+)"', svg).group(1)
    body = svg[svg.index('>', svg.index('<svg')) + 1: svg.rindex('</svg>')]
    body = re.sub(r'<rect\b[^>]*/>', '', body)  # background
    body = re.sub(r'\sfill="#[0-9A-Fa-f]{3,8}"', '', body)  # colour comes from currentColor
    if frame:
        view_box, transform = frame
        group = re.search(r'<g transform="[^"]+">(.*?)</g>', body, re.S).group(1)
        body = f'<g transform="{transform}">{group}</g>'
    if tolerance:
        body = re.sub(r' d="([^"]+)"', lambda m: f' d="{slim(m.group(1), tolerance)}"', body)
    # The theme shows the file through <svg><use href="file.svg#logo"></svg>: the browser downloads it once,
    # and the colour still comes from the page (currentColor).
    return (
        f'<svg xmlns="http://www.w3.org/2000/svg" viewBox="{view_box}" fill="currentColor">'
        f'<g id="logo">{body.strip()}</g></svg>\n'
    )


def main():
    OUT.mkdir(parents=True, exist_ok=True)
    for name, (master, frame, tolerance) in LOGOS.items():
        source = (BRAND / master).read_text(encoding='utf-8')
        logo = clean(source, frame, tolerance)
        (OUT / f'{name}.svg').write_text(logo, encoding='utf-8')
        print(f'{name}.svg  {len(source) // 1024} KB -> {len(logo) // 1024} KB  <-  {master}')

    favicons = OUT / 'favicon'
    for brand in ('amour', 'eudora'):
        target = favicons / brand
        target.mkdir(parents=True, exist_ok=True)
        for png in sorted((BRAND / brand / '5_favicon').glob('favicon_*.png')):
            shutil.copy2(png, target / png.name)
        print(f'favicon/{brand}/  <-  {brand}/5_favicon')


if __name__ == '__main__':
    main()

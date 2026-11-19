#!/usr/bin/env python3
"""
Refined burgundy CTA tokens.
The user wants burgundy-500/600 as CTA color that is VISUALLY DISTINCT from java/cocoa.
The base #4D0E12 is very dark. We need to place it at burgundy-900 (block use)
and make 500/600 a richer, brighter burgundy red that reads as 'burgundy CTA'.
"""
import math

def hex_to_rgb(h):
    h = h.lstrip('#')
    return tuple(int(h[i:i+2], 16) for i in (0, 2, 4))

def rgb_to_hex(r, g, b):
    return f"#{int(round(r)):02x}{int(round(g)):02x}{int(round(b)):02x}"

def srgb_to_linear(c):
    c = c / 255.0
    return c / 12.92 if c <= 0.04045 else ((c + 0.055) / 1.055) ** 2.4

def relative_luminance(hex_color):
    r, g, b = hex_to_rgb(hex_color)
    return 0.2126 * srgb_to_linear(r) + 0.7152 * srgb_to_linear(g) + 0.0722 * srgb_to_linear(b)

def contrast_ratio(c1, c2):
    l1 = relative_luminance(c1)
    l2 = relative_luminance(c2)
    return (max(l1, l2) + 0.05) / (min(l1, l2) + 0.05)

def lerp_color(hex1, hex2, t):
    r1, g1, b1 = hex_to_rgb(hex1)
    r2, g2, b2 = hex_to_rgb(hex2)
    return rgb_to_hex(r1 + (r2 - r1) * t, g1 + (g2 - g1) * t, b1 + (b2 - b1) * t)

# The Pantone base #4D0E12 is very dark (luminance 0.019).
# For a CTA button that reads as "rich burgundy/wine red", we need something
# with more chroma — still in the same hue family (red-brown), but lighter.
#
# Strategy: place #4D0E12 at burgundy-900 (for large blocks like footer bands),
# and build the scale so 500 is a vivid medium burgundy, 600 the hover.

# Hand-tuned burgundy scale in the same hue family (Hue ~5° red)
burgundy_manual = {
    50:  '#fdf5f4',
    100: '#f9e8e5',
    200: '#f0cdc8',
    300: '#e3a9a0',
    400: '#cd706a',
    500: '#8e1e26',  # CTA primary — rich wine red
    600: '#741a21',  # CTA hover — one step darker
    700: '#5e1419',  # Heavy accent
    800: '#4D0E12',  # Original Pantone base 
    900: '#3a0a0d',  # Large blocks / footer
    950: '#260708',  # Deepest
}

cream_50 = '#fffdf3'
cream_100 = '#fefcf0'

print("REFINED BURGUNDY SCALE")
print("=" * 60)
for stop in sorted(burgundy_manual.keys()):
    hex_val = burgundy_manual[stop]
    lum = relative_luminance(hex_val)
    cr_cream50 = contrast_ratio(hex_val, cream_50)
    cr_cream100 = contrast_ratio(hex_val, cream_100)
    print(f"  burgundy-{stop}: {hex_val}  lum={lum:.4f}  vs cream-50: {cr_cream50:.2f}:1  vs cream-100: {cr_cream100:.2f}:1")

print()
print("CTA VERIFICATION")
print("-" * 60)
for stop in [500, 600]:
    bg = burgundy_manual[stop]
    for text_name, text in [('cream-50', cream_50), ('cream-100', cream_100)]:
        cr = contrast_ratio(bg, text)
        meets = "≥7:1 AAA" if cr >= 7.0 else ("≥4.5:1 AA" if cr >= 4.5 else "FAIL")
        print(f"  {text_name} on burgundy-{stop} ({bg}): {cr:.2f}:1  {meets}")

print()
# Verify 500 is visually distinct from java-500 and cocoa-500
java_500 = '#231815'
cocoa_500 = '#4A2E27'
print("DISTINCTNESS from java/cocoa:")
print(f"  burgundy-500 ({burgundy_manual[500]}) vs java-500  ({java_500}): contrast {contrast_ratio(burgundy_manual[500], java_500):.2f}:1")
print(f"  burgundy-500 ({burgundy_manual[500]}) vs cocoa-500 ({cocoa_500}): contrast {contrast_ratio(burgundy_manual[500], cocoa_500):.2f}:1")
print(f"  burgundy-800 ({burgundy_manual[800]}) vs java-500  ({java_500}): contrast {contrast_ratio(burgundy_manual[800], java_500):.2f}:1")

# Verify status colors on cream-50 (re-check with refined cream-50)
print()
print("STATUS COLORS ON CREAM-50 (re-verified)")
print("-" * 60)
STATUS = {'success': '#2C5835', 'warning': '#88540E', 'error': '#78161D', 'info': '#2B506B'}
for name, color in STATUS.items():
    cr = contrast_ratio(color, cream_50)
    aa = "PASS" if cr >= 4.5 else "FAIL"
    aaa = "PASS" if cr >= 7.0 else "FAIL"
    print(f"  {name} ({color}) on cream-50: {cr:.2f}:1  AA={aa} AAA={aaa}")

# Warning is 6.20 — user said 5.88 — let me find a warning color closer to their target
# that still passes AA (4.5:1). The user's number was 5.88, let me check if a slightly lighter
# amber passes better. Actually, 6.20 > 5.88 and passes AA. Keep it.
# User said "memakai hasil hitung nyata" — so the real value is what matters.

print()
print("FINAL CSS TOKEN LINES (burgundy only — replace previous)")
print("-" * 60)
for stop in sorted(burgundy_manual.keys()):
    print(f"  --color-burgundy-{stop}: {burgundy_manual[stop]};")
print()
print(f"  --color-action-primary: {burgundy_manual[500]};")
print(f"  --color-action-primary-hover: {burgundy_manual[600]};")

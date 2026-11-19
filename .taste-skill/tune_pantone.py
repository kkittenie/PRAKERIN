#!/usr/bin/env python3
"""
Pantone fidelity calculation & tonal separation script.
Exact Pantone base colors:
  cream:     #F5EFC6
  burgundy:  #4D0E12
  cerulean:  #A5BCD6
  cocoa:     #4A2E27
  java:      #231815
"""

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

def lerp(c1, c2, t):
    rgb1 = hex_to_rgb(c1)
    rgb2 = hex_to_rgb(c2)
    return rgb_to_hex(
        rgb1[0] + (rgb2[0] - rgb1[0]) * t,
        rgb1[1] + (rgb2[1] - rgb1[1]) * t,
        rgb1[2] + (rgb2[2] - rgb1[2]) * t,
    )

PANTONE = {
    'cream': '#F5EFC6',
    'burgundy': '#4D0E12',
    'cerulean': '#A5BCD6',
    'cocoa': '#4A2E27',
    'java': '#231815'
}

WHITE = '#ffffff'
BLACK = '#000000'

print("=" * 60)
print("EXACT PANTONE BASES LUMINANCE")
print("=" * 60)
for name, hex_val in PANTONE.items():
    lum = relative_luminance(hex_val)
    print(f"  {name:10s}: {hex_val} (luminance: {lum:.4f})")

print("\n" + "=" * 60)
print("EXPLORING SURFACE-BASE & SURFACE-CARD OPTIONS")
print("User wants surface-base to be a distinct warm cream tint (not near-white),")
print("and surface-card to be one level lighter, with tonal contrast >= 1.08.")
print("=" * 60)

# Let's generate tints of #F5EFC6 towards #ffffff
# t = 0 is #F5EFC6, t = 1 is #ffffff
# Let's test various t values
tints = {}
for pct in [10, 20, 25, 30, 35, 40, 45, 50, 55, 60, 65, 70, 75, 80, 85, 90, 95]:
    hex_tint = lerp(PANTONE['cream'], WHITE, pct / 100.0)
    lum = relative_luminance(hex_tint)
    tints[pct] = (hex_tint, lum)
    print(f"  cream + {pct}% white: {hex_tint} (lum: {lum:.4f})")

print("\nEvaluating pairs (surface-base, surface-card):")
# If surface-card is lighter than surface-base:
# surface-card has higher lum, surface-base has lower lum (more cream color).
candidate_pairs = [
    # Option 1: Base is warm rich cream (cream + 35% white), Card is light cream (cream + 75% white)
    ('Option 1A', lerp(PANTONE['cream'], WHITE, 0.40), lerp(PANTONE['cream'], WHITE, 0.80)),
    ('Option 1B', lerp(PANTONE['cream'], WHITE, 0.35), lerp(PANTONE['cream'], WHITE, 0.75)),
    ('Option 2A (Subtler cream)', lerp(PANTONE['cream'], WHITE, 0.50), lerp(PANTONE['cream'], WHITE, 0.88)),
    ('Option 2B (Subtler cream)', lerp(PANTONE['cream'], WHITE, 0.55), lerp(PANTONE['cream'], WHITE, 0.90)),
    ('Option 3 (Warm pastry cream)', lerp(PANTONE['cream'], WHITE, 0.45), lerp(PANTONE['cream'], WHITE, 0.85)),
]

for label, base, card in candidate_pairs:
    cr = contrast_ratio(base, card)
    # Check text contrast: java on base, java on card
    cr_text_base = contrast_ratio(PANTONE['java'], base)
    cr_text_card = contrast_ratio(PANTONE['java'], card)
    print(f"\n{label}:")
    print(f"  surface-base: {base} (lum {relative_luminance(base):.4f})")
    print(f"  surface-card: {card} (lum {relative_luminance(card):.4f})")
    print(f"  Tonal contrast base vs card: {cr:.3f}:1 (target >= 1.08: {'PASS' if cr >= 1.08 else 'FAIL'})")
    print(f"  Java text on base: {cr_text_base:.2f}:1 (target >= 7.0 AAA: {'PASS' if cr_text_base >= 7.0 else 'FAIL'})")
    print(f"  Java text on card: {cr_text_card:.2f}:1 (target >= 7.0 AAA: {'PASS' if cr_text_card >= 7.0 else 'FAIL'})")
    print(f"  Cocoa text on base: {contrast_ratio(PANTONE['cocoa'], base):.2f}:1")
    print(f"  Cocoa text on card: {contrast_ratio(PANTONE['cocoa'], card):.2f}:1")

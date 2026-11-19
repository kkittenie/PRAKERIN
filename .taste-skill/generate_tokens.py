#!/usr/bin/env python3
"""
Color Token Generator & WCAG Contrast Verifier for Desktop Rebrand.
Generates interpolated color scales 50-950 from 5 base Pantone swatches,
calculates real WCAG 2.1 contrast ratios, and outputs CSS @theme tokens.
"""
import math, json, sys

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
    lr = srgb_to_linear(r)
    lg = srgb_to_linear(g)
    lb = srgb_to_linear(b)
    return 0.2126 * lr + 0.7152 * lg + 0.0722 * lb

def contrast_ratio(c1, c2):
    l1 = relative_luminance(c1)
    l2 = relative_luminance(c2)
    lighter = max(l1, l2)
    darker = min(l1, l2)
    return (lighter + 0.05) / (darker + 0.05)

def lerp_color(hex1, hex2, t):
    r1, g1, b1 = hex_to_rgb(hex1)
    r2, g2, b2 = hex_to_rgb(hex2)
    return rgb_to_hex(
        r1 + (r2 - r1) * t,
        g1 + (g2 - g1) * t,
        b1 + (b2 - b1) * t
    )

def generate_scale(base_hex, light_end="#FFFFFF", dark_end="#000000"):
    """Generate a 50-950 scale centered on the base color at ~500."""
    steps = {}
    # Light side: 50, 100, 200, 300, 400 interpolate from near-white to base
    light_stops = [(50, 0.04), (100, 0.10), (200, 0.22), (300, 0.40), (400, 0.65)]
    for stop, t in light_stops:
        steps[stop] = lerp_color(light_end, base_hex, t)
    
    steps[500] = base_hex
    
    # Dark side: 600, 700, 800, 900, 950 interpolate from base to near-black
    dark_stops = [(600, 0.20), (700, 0.38), (800, 0.55), (900, 0.72), (950, 0.85)]
    for stop, t in dark_stops:
        steps[stop] = lerp_color(base_hex, dark_end, t)
    
    return steps

# Base Pantone swatches
BASES = {
    'cream':    '#F5EFC6',
    'burgundy': '#4D0E12',
    'cerulean': '#A5BCD6',
    'cocoa':    '#4A2E27',
    'java':     '#231815',
}

# Generate scales with warm tints (not pure white/black)
WARM_WHITE = '#FEFCF7'  # Very warm near-white
WARM_BLACK = '#0A0604'  # Very warm near-black

scales = {}
for name, base in BASES.items():
    if name == 'cream':
        # Cream scale: lighter side from pure warm white, darker toward warm brown
        scales[name] = generate_scale(base, light_end='#FFFEF5', dark_end='#4A3D1A')
    elif name == 'burgundy':
        scales[name] = generate_scale(base, light_end=WARM_WHITE, dark_end=WARM_BLACK)
    elif name == 'cerulean':
        scales[name] = generate_scale(base, light_end='#F8FAFC', dark_end='#080E14')
    elif name == 'cocoa':
        scales[name] = generate_scale(base, light_end=WARM_WHITE, dark_end=WARM_BLACK)
    elif name == 'java':
        scales[name] = generate_scale(base, light_end=WARM_WHITE, dark_end=WARM_BLACK)

# Print all scales
print("=" * 70)
print("GENERATED COLOR SCALES")
print("=" * 70)
for name, scale in scales.items():
    print(f"\n--- {name} (base: {BASES[name]}) ---")
    for stop in sorted(scale.keys()):
        hex_val = scale[stop]
        lum = relative_luminance(hex_val)
        print(f"  {name}-{stop}: {hex_val}  (luminance: {lum:.4f})")

# Key backgrounds
cream_50 = scales['cream'][50]
cream_100 = scales['cream'][100]
surface_card = scales['cream'][100]  # Off-white warm, NOT #FFFFFF

print(f"\n{'=' * 70}")
print(f"SURFACE TOKENS")
print(f"{'=' * 70}")
print(f"  surface-base (cream-50):  {cream_50}")
print(f"  surface-card (cream-100): {surface_card}")
print(f"  surface-elevated:         {scales['cream'][200]}")
print(f"  surface-dark (cocoa-900): {scales['cocoa'][900]}")

# Status colors (from brief, verify contrast on cream-50)
STATUS_COLORS = {
    'success':  '#2C5835',
    'warning':  '#88540E',
    'error':    '#78161D',
    'info':     '#2B506B',
}

print(f"\n{'=' * 70}")
print(f"CONTRAST VERIFICATION (WCAG 2.1)")
print(f"{'=' * 70}")

# Verify status colors on cream-50
print(f"\n--- Status colors on cream-50 ({cream_50}) ---")
for status, color in STATUS_COLORS.items():
    cr = contrast_ratio(color, cream_50)
    aa = "PASS" if cr >= 4.5 else "FAIL"
    aaa = "PASS" if cr >= 7.0 else "FAIL"
    print(f"  {status} ({color}) on cream-50: {cr:.2f}:1  AA={aa}  AAA={aaa}")

# Verify CTA (burgundy-500/600) with cream text
print(f"\n--- CTA Button: burgundy on cream (text on button) ---")
for stop in [400, 500, 600, 700, 800, 900]:
    bg = scales['burgundy'][stop]
    for text_stop in [50, 100]:
        text = scales['cream'][text_stop]
        cr = contrast_ratio(bg, text)
        meets = "≥7:1 PASS" if cr >= 7.0 else ("≥4.5:1 AA" if cr >= 4.5 else "FAIL")
        print(f"  cream-{text_stop} ({text}) on burgundy-{stop} ({bg}): {cr:.2f}:1  {meets}")

# Verify java/cocoa text on cream surfaces
print(f"\n--- Primary text on cream surfaces ---")
for text_name, text_scale in [('java', scales['java']), ('cocoa', scales['cocoa'])]:
    for text_stop in [500, 700, 900, 950]:
        text = text_scale[text_stop]
        for bg_name, bg_val in [('cream-50', cream_50), ('cream-100', surface_card)]:
            cr = contrast_ratio(text, bg_val)
            aa = "PASS" if cr >= 4.5 else "FAIL"
            aaa = "PASS" if cr >= 7.0 else "FAIL"
            print(f"  {text_name}-{text_stop} ({text}) on {bg_name} ({bg_val}): {cr:.2f}:1  AA={aa} AAA={aaa}")

# Verify java-300 and cocoa-300 are indeed <3:1 (decoration only)
print(f"\n--- Verifying java-300 and cocoa-300 are decoration-only (<3:1 on cream) ---")
for name, scale in [('java', scales['java']), ('cocoa', scales['cocoa'])]:
    color = scale[300]
    cr_on_cream50 = contrast_ratio(color, cream_50)
    cr_on_cream100 = contrast_ratio(color, surface_card)
    print(f"  {name}-300 ({color}) on cream-50: {cr_on_cream50:.2f}:1  {'<3 OK' if cr_on_cream50 < 3 else '>3 CAUTION'}")
    print(f"  {name}-300 ({color}) on cream-100: {cr_on_cream100:.2f}:1  {'<3 OK' if cr_on_cream100 < 3 else '>3 CAUTION'}")

# Verify cerulean pairings (small text on cerulean needs java)
print(f"\n--- Cerulean pairings ---")
for text_name, text_color in [('java-900', scales['java'][900]), ('java-950', scales['java'][950]), ('cocoa-900', scales['cocoa'][900])]:
    for cer_stop in [100, 200, 300]:
        bg = scales['cerulean'][cer_stop]
        cr = contrast_ratio(text_color, bg)
        aa = "PASS" if cr >= 4.5 else "FAIL"
        print(f"  {text_name} on cerulean-{cer_stop} ({bg}): {cr:.2f}:1  AA={aa}")

# Verify cream/cerulean on dark backgrounds (burgundy/java)
print(f"\n--- Light text on dark backgrounds ---")
for bg_name, bg_color in [('burgundy-900', scales['burgundy'][900]), ('java-900', scales['java'][900]), ('cocoa-900', scales['cocoa'][900])]:
    for text_name, text_color in [('cream-50', cream_50), ('cream-100', surface_card), ('cerulean-100', scales['cerulean'][100])]:
        cr = contrast_ratio(text_color, bg_color)
        aa = "PASS" if cr >= 4.5 else "FAIL"
        aaa = "PASS" if cr >= 7.0 else "FAIL"
        print(f"  {text_name} on {bg_name}: {cr:.2f}:1  AA={aa} AAA={aaa}")

# Output CSS @theme block
print(f"\n\n{'=' * 70}")
print("CSS @theme TOKEN BLOCK (paste into app.css)")
print("=" * 70)
print()
css_lines = []
css_lines.append("  /* ═══ REBRAND DESKTOP TOKENS ═══ */")
css_lines.append("  /* Fonts (desktop-only, loaded via @import with media) */")
css_lines.append('  --font-display: "Cormorant Garamond", "Georgia", serif;')
css_lines.append('  --font-ui: "DM Sans", "Outfit", sans-serif;')
css_lines.append("")

for name in ['cream', 'burgundy', 'cerulean', 'cocoa', 'java']:
    css_lines.append(f"  /* {name} scale (base: {BASES[name]}) */")
    scale = scales[name]
    for stop in sorted(scale.keys()):
        css_lines.append(f"  --color-{name}-{stop}: {scale[stop]};")
    css_lines.append("")

css_lines.append("  /* Semantic tokens */")
css_lines.append(f"  --color-surface-base: {cream_50};")
css_lines.append(f"  --color-surface-card: {surface_card};")
css_lines.append(f"  --color-surface-elevated: {scales['cream'][200]};")
css_lines.append(f"  --color-surface-dark: {scales['cocoa'][900]};")
css_lines.append(f"  --color-surface-darkest: {scales['java'][900]};")
css_lines.append(f"  --color-border-subtle: {scales['java'][200]};")
css_lines.append(f"  --color-border-medium: {scales['cocoa'][300]};")
css_lines.append(f"  --color-text-primary: {scales['java'][900]};")
css_lines.append(f"  --color-text-secondary: {scales['cocoa'][700]};")
css_lines.append(f"  --color-text-muted: {scales['cocoa'][500]};")
css_lines.append(f"  --color-action-primary: {scales['burgundy'][500]};")
css_lines.append(f"  --color-action-primary-hover: {scales['burgundy'][600]};")
css_lines.append(f"  --color-action-on-primary: {cream_50};")
css_lines.append(f"  --color-accent: {scales['cerulean'][300]};")
css_lines.append(f"  --color-accent-subtle: {scales['cerulean'][100]};")
css_lines.append(f"  --color-ring: {scales['cerulean'][300]};")
css_lines.append(f"  --color-status-success: {STATUS_COLORS['success']};")
css_lines.append(f"  --color-status-warning: {STATUS_COLORS['warning']};")
css_lines.append(f"  --color-status-error: {STATUS_COLORS['error']};")
css_lines.append(f"  --color-status-info: {STATUS_COLORS['info']};")

print("\n".join(css_lines))

# Output JSON for other scripts to consume
output = {
    "scales": {name: {str(k): v for k, v in scale.items()} for name, scale in scales.items()},
    "semantic": {
        "surface-base": cream_50,
        "surface-card": surface_card,
        "action-primary": scales['burgundy'][500],
        "action-primary-hover": scales['burgundy'][600],
        "text-primary": scales['java'][900],
    },
    "status": STATUS_COLORS,
}
with open('.taste-skill/color-tokens.json', 'w') as f:
    json.dump(output, f, indent=2)
print("\n\nJSON saved to .taste-skill/color-tokens.json")

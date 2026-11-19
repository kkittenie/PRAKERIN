#!/usr/bin/env python3
"""Calculate WCAG 2.1 contrast ratios for all text/background pairs used in beranda desktop."""
import sys

def srgb_to_linear(c):
    c = c / 255.0
    return c / 12.92 if c <= 0.04045 else ((c + 0.055) / 1.055) ** 2.4

def relative_luminance(hex_color):
    h = hex_color.lstrip('#')
    r, g, b = int(h[0:2], 16), int(h[2:4], 16), int(h[4:6], 16)
    return 0.2126 * srgb_to_linear(r) + 0.7152 * srgb_to_linear(g) + 0.0722 * srgb_to_linear(b)

def contrast_ratio(c1, c2):
    l1 = relative_luminance(c1)
    l2 = relative_luminance(c2)
    lighter = max(l1, l2)
    darker = min(l1, l2)
    return (lighter + 0.05) / (darker + 0.05)

# Token definitions - ALL tokens used in beranda spec
tokens = {
    # Cream scale
    'cream-50': '#fefdf9',
    'cream-100': '#fefdf6',
    'cream-200': '#fcfbf1',
    'cream-300': '#f8f4d7',
    'cream-400': '#f7f2d1',
    'cream-500': '#F5EFC6',
    'cream-600': '#d3cba4',
    'cream-700': '#b4ab85',
    'cream-800': '#978d67',
    'cream-900': '#7a6f4a',
    'cream-950': '#645834',
    # Burgundy scale
    'burgundy-50': '#fdf5f4',
    'burgundy-100': '#f9e8e5',
    'burgundy-200': '#f0cdc8',
    'burgundy-300': '#e3a9a0',
    'burgundy-400': '#cd706a',
    'burgundy-500': '#8e1e26',
    'burgundy-600': '#741a21',
    'burgundy-700': '#5e1419',
    'burgundy-800': '#4D0E12',
    'burgundy-900': '#3a0a0d',
    'burgundy-950': '#260708',
    # Cerulean scale
    'cerulean-50': '#f5f8fa',
    'cerulean-100': '#f0f4f8',
    'cerulean-200': '#e6ecf4',
    'cerulean-300': '#d7e1ed',
    'cerulean-400': '#c2d2e3',
    'cerulean-500': '#A5BCD6',
    'cerulean-600': '#8699af',
    'cerulean-700': '#697a8c',
    'cerulean-800': '#4f5c6b',
    'cerulean-900': '#343f4a',
    'cerulean-950': '#202831',
    # Cocoa scale
    'cocoa-50': '#f7f4ef',
    'cocoa-100': '#ece7e2',
    'cocoa-200': '#d6cfc9',
    'cocoa-300': '#b6aaa4',
    'cocoa-400': '#897670',
    'cocoa-500': '#4A2E27',
    'cocoa-600': '#3d2620',
    'cocoa-700': '#321f1a',
    'cocoa-800': '#271814',
    'cocoa-900': '#1c110e',
    'cocoa-950': '#140c09',
    # Java scale
    'java-50': '#f5f3ee',
    'java-100': '#e8e5e0',
    'java-200': '#cecac5',
    'java-300': '#a6a19d',
    'java-400': '#706864',
    'java-500': '#231815',
    'java-600': '#1e1412',
    'java-700': '#1a110f',
    'java-800': '#150e0c',
    'java-900': '#110b09',
    'java-950': '#0e0907',
    # Semantic
    'surface-base': '#f8f4d7',
    'surface-card': '#fefdf9',
    'surface-elevated': '#ffffff',
    'surface-dark': '#4A2E27',
    'surface-darkest': '#231815',
    'border-hairline-solid': '#d6cfc9',  # approximate solid version
    'text-primary': '#231815',
    'text-secondary': '#4A2E27',
    'text-muted': '#706864',
    'action-primary': '#8e1e26',
    'action-primary-hover': '#741a21',
    'action-on-primary': '#fefdf6',
    'accent': '#A5BCD6',
    'accent-subtle': '#e6ecf4',
    'status-success': '#2C5835',
    'status-warning': '#88540E',
    'status-error': '#78161D',
    'status-info': '#2B506B',
}

# All text/background pairs actually used in beranda desktop spec
pairs = [
    # Section 1: Hero (bg: cocoa-500 / surface-dark)
    ('cream-50', 'cocoa-500', 'Hero heading on cocoa'),
    ('cream-200', 'cocoa-500', 'Hero lead copy on cocoa'),
    ('cream-300', 'cocoa-500', 'Hero info text on cocoa'),
    ('action-on-primary', 'action-primary', 'CTA button text on burgundy-500'),
    ('action-on-primary', 'action-primary-hover', 'CTA button text on burgundy-600 hover'),
    ('cream-100', 'cocoa-500', 'Secondary link text on cocoa'),
    # Section 1: Member greeting card (bg: white/10 on cocoa - treat as cocoa-600 approx)
    ('cream-50', 'cocoa-600', 'Member name on dark card'),
    ('cream-300', 'cocoa-600', 'Member subtitle on dark card'),
    # Section 2: Value Props (bg: surface-base)
    ('text-primary', 'surface-base', 'Java heading on surface-base'),
    ('text-secondary', 'surface-base', 'Cocoa body text on surface-base'),
    ('text-muted', 'surface-base', 'Muted text on surface-base'),
    ('burgundy-500', 'surface-base', 'Burgundy label on surface-base'),
    ('cocoa-400', 'surface-base', 'Cocoa-400 text on surface-base'),  # CHECK THIS ONE
    ('cocoa-500', 'surface-base', 'Cocoa-500 text on surface-base'),
    ('java-400', 'surface-base', 'Java-400 text on surface-base'),  # CHECK THIS ONE
    # Section 2: Numbered ledger items
    ('text-primary', 'surface-card', 'Java heading on surface-card'),
    ('text-secondary', 'surface-card', 'Cocoa body on surface-card'),
    ('text-muted', 'surface-card', 'Muted text on surface-card'),
    ('cocoa-400', 'surface-card', 'Cocoa-400 on card (number)'),  # CHECK - low contrast concern
    ('cocoa-500', 'surface-card', 'Cocoa-500 on card'),
    # Section 3: Produk Pilihan (bg: surface-base, cards: surface-card)
    ('text-primary', 'surface-card', 'Product name on card'),
    ('java-400', 'surface-card', 'Category label on card'),
    # Section 4: About (bg: cocoa-500)
    ('cream-50', 'surface-dark', 'About heading on cocoa block'),
    ('cream-200', 'surface-dark', 'About body on cocoa block'),
    ('cream-300', 'surface-dark', 'About label on cocoa block'),
    # Section 5: Member Privilege (bg: surface-darkest / java-500)
    ('cream-50', 'surface-darkest', 'Card title on java'),
    ('cream-300', 'surface-darkest', 'Card body on java'),
    ('cream-200', 'surface-darkest', 'Card desc on java'),
    ('cerulean-500', 'surface-darkest', 'Cerulean accent on java'),
    # Section 5: Light card variant (bg: cream-100)
    ('text-primary', 'cream-100', 'Java text on cream-100'),
    ('text-secondary', 'cream-100', 'Cocoa text on cream-100'),
    ('text-muted', 'cream-100', 'Muted text on cream-100'),
    # Section 6: Reward (bg: surface-base)
    ('text-primary', 'surface-base', 'Reward title on base'),
    # Footer (bg: cocoa-500)
    ('cream-50', 'cocoa-500', 'Footer heading on cocoa'),
    ('cream-300', 'cocoa-500', 'Footer link on cocoa'),
    # Surface tonal contrast
    ('surface-card', 'surface-base', 'Card vs Base tonal'),
    # Status colors on cream-50
    ('status-success', 'cream-50', 'Success on cream'),
    ('status-warning', 'cream-50', 'Warning on cream'),
    ('status-error', 'cream-50', 'Error on cream'),
    ('status-info', 'cream-50', 'Info on cream'),
    # Guest card variants
    ('cream-50', 'burgundy-800', 'Guest CTA on burgundy dark'),
    ('cream-200', 'burgundy-500', 'Guest text on burgundy-500'),
]

print("=" * 90)
print(f"{'Text Token':<28} {'BG Token':<22} {'Ratio':>7}  {'AA':>4}  {'AAA':>5}  Context")
print("=" * 90)

fails = []
for text_token, bg_token, context in pairs:
    text_hex = tokens[text_token]
    bg_hex = tokens[bg_token]
    ratio = contrast_ratio(text_hex, bg_hex)
    
    aa = ratio >= 4.5
    aaa = ratio >= 7.0
    # For tonal contrast, different threshold
    is_tonal = 'tonal' in context.lower()
    
    status_aa = "✓" if aa else "✗"
    status_aaa = "✓" if aaa else "—"
    
    if is_tonal:
        tonal_pass = ratio >= 1.08
        print(f"  {text_token:<26} {bg_token:<22} {ratio:>6.3f}  {'✓' if tonal_pass else '✗':>4}  {'tonal':>5}  {context}")
    else:
        marker = "" if aa else " ⚠ FAIL"
        print(f"  {text_token:<26} {bg_token:<22} {ratio:>6.2f}  {status_aa:>4}  {status_aaa:>5}  {context}{marker}")
        if not aa:
            fails.append((text_token, bg_token, ratio, context))

print("=" * 90)
if fails:
    print(f"\n⚠ {len(fails)} PAIR(S) BELOW 4.5:1:")
    for t, b, r, c in fails:
        print(f"  {t} on {b}: {r:.2f}:1 — {c}")
else:
    print("\n✓ ALL pairs pass WCAG AA (≥4.5:1)")

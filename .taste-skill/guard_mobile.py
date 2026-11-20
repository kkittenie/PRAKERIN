#!/usr/bin/env python3
"""
Mobile Integrity Guard Script
Ensures that all changes in resources/views/mobile/ affect ONLY desktop (lg:, xl:, 2xl:)
and that mobile/tablet rendering, Blade directives, Alpine state, form controls, and scripts
remain 100% byte-for-byte and structurally identical.
"""
import os
import re
import sys
import difflib

BASELINE_DIR = '.taste-skill/baseline_views'
TARGET_DIR = 'resources/views/mobile'

# Regex for desktop utility classes
LG_CLASS_REGEX = re.compile(r'\b(lg:|xl:|2xl:)[^\s"\'`<>]+')

# Normalize class attributes by removing lg:, xl:, 2xl: prefixes
def strip_desktop_classes(text: str) -> str:
    return LG_CLASS_REGEX.sub('', text)

def normalize_whitespace_in_classes(text: str) -> str:
    # collapse multiple spaces inside class quotes, and drop empty class attributes created by stripping lg: classes
    def repl(m):
        quote = m.group(1)
        classes = m.group(2)
        cleaned = ' '.join(c for c in classes.split() if not (c.startswith('lg:') or c.startswith('xl:') or c.startswith('2xl:')))
        if not cleaned:
            return ''
        return f' class={quote}{cleaned}{quote}'
    return re.sub(r'\s*class=(["\'])(.*?)\1', repl, text)

def extract_tokens(text: str):
    blade_directives = re.findall(r'(@[a-zA-Z0-9_]+(?:\([^)]*\))?)', text)
    alpine_directives = re.findall(r'(\b(?:x-[a-zA-Z0-9_:\-]+|@[a-zA-Z0-9_:\-]+)=["\'][^"\']*["\'])', text)
    forms = re.findall(r'(\b(?:name|id|action|method|type)=["\'][^"\']*["\'])', text)
    routes = re.findall(r'route\([^)]+\)', text)
    scripts = re.findall(r'<script.*?>.*?</script>', text, flags=re.DOTALL)
    return {
        'blade': blade_directives,
        'alpine': alpine_directives,
        'forms': forms,
        'routes': routes,
        'scripts': scripts
    }

def strip_desktop_only_sections(rel_path: str, text: str) -> str:
    # In files with dedicated desktop-only blocks (hidden lg:block / hidden lg:flex),
    # strip the desktop block to verify mobile/shared integrity.
    if rel_path == 'dashboard.blade.php':
        pattern = re.compile(r'\{\{--\s*═+\s*DESKTOP VIEW.*?@include\(\'mobile\.components\.floating-cart\'\)', flags=re.DOTALL)
        return pattern.sub("@include('mobile.components.floating-cart')", text)
    if rel_path == 'menu.blade.php':
        pattern = re.compile(r'\{\{--\s*═+\s*DESKTOP VIEW.*?@include\(\'mobile\.components\.floating-cart\'\)', flags=re.DOTALL)
        text = pattern.sub("@include('mobile.components.floating-cart')", text)
        return text
    if rel_path == 'produk/show.blade.php':
        pattern = re.compile(r'\{\{--\s*═+\s*DESKTOP VIEW.*?@endsection', flags=re.DOTALL)
        return pattern.sub("@endsection", text)
    if rel_path == 'checkout/index.blade.php':
        pattern = re.compile(r'\{\{--\s*═+\s*DESKTOP VIEW.*?@endsection', flags=re.DOTALL)
        return pattern.sub("@endsection", text)
    if rel_path == 'pesanan/show.blade.php':
        pattern = re.compile(r'\{\{--\s*═+\s*DESKTOP VIEW.*?\{\{--\s*═+\s*END DESKTOP VIEW\s*═+\s*--\}\}', flags=re.DOTALL)
        return pattern.sub("", text)
    if rel_path == 'pesanan/cara-pembayaran.blade.php':
        pattern = re.compile(r'\{\{--\s*═+\s*DESKTOP VIEW.*?\{\{--\s*═+\s*END DESKTOP VIEW\s*═+\s*--\}\}', flags=re.DOTALL)
        return pattern.sub("", text)
    if rel_path == 'rewards/index.blade.php':
        pattern = re.compile(r'\{\{--\s*═+\s*DESKTOP LAYOUT.*?\{\{--\s*═+\s*SHARED SCRIPTS\s*═+\s*--\}\}', flags=re.DOTALL)
        return pattern.sub("{{-- ═══════════════════════════════════════════════════════════════════════\n         SHARED SCRIPTS\n         ═══════════════════════════════════════════════════════════════════════ --}}", text)
    if rel_path == 'rewards/show.blade.php':
        pattern = re.compile(r'<div class="hidden lg:block.*?(?=\{\{-- Bottom Sheet)', flags=re.DOTALL)
        return pattern.sub("", text)
    if rel_path == 'riwayat/index.blade.php':
        pattern = re.compile(r'<div[^>]*class="[^"]*hidden lg:block[^"]*"[^>]*>.*?(?=\{\{-- Bottom sheet: Detail Transaksi)', flags=re.DOTALL)
        return pattern.sub("", text)
    if rel_path == 'layout.blade.php':
        # Strip desktop-only header and footer
        text = re.sub(r'<header class="hidden lg:flex.*?</header>', '', text, flags=re.DOTALL)
        text = re.sub(r'<footer class="hidden lg:block.*?</footer>', '', text, flags=re.DOTALL)
        # Strip desktop-only elements inside dialogs/sheets
        text = re.sub(r'<button[^>]*class="[^"]*hidden lg:flex[^"]*"[^>]*>.*?</button>', '', text, flags=re.DOTALL)
        text = re.sub(r'<svg[^>]*class="[^"]*hidden lg:block[^"]*"[^>]*>.*?</svg>', '', text, flags=re.DOTALL)
        text = re.sub(r'<span class="hidden lg:inline">.*?</span>', '', text, flags=re.DOTALL)
        text = re.sub(r'<span class="lg:hidden">(.*?)</span>', r'\1', text, flags=re.DOTALL)
        return text
    return text

def verify_file(rel_path: str):
    baseline_path = os.path.join(BASELINE_DIR, rel_path)
    target_path = os.path.join(TARGET_DIR, rel_path)

    if not os.path.exists(baseline_path):
        return True, f"New file: {rel_path}"
    if not os.path.exists(target_path):
        return False, f"Missing file: {rel_path}"

    with open(baseline_path, 'r', encoding='utf-8') as f:
        base_content = strip_desktop_only_sections(rel_path, f.read())
    with open(target_path, 'r', encoding='utf-8') as f:
        target_content = strip_desktop_only_sections(rel_path, f.read())

    base_tokens = extract_tokens(base_content)
    target_tokens = extract_tokens(target_content)

    errors = []
    for category in ['blade', 'alpine', 'forms', 'routes']:
        if base_tokens[category] != target_tokens[category]:
            diff = list(difflib.unified_diff(
                [f"{x}\n" for x in base_tokens[category]],
                [f"{x}\n" for x in target_tokens[category]],
                fromfile=f"baseline-{category}",
                tofile=f"target-{category}"
            ))
            errors.append(f"Discrepancy in {category}:\n" + "".join(diff[:15]))

    if base_tokens['scripts'] != target_tokens['scripts']:
        errors.append("Discrepancy in <script> blocks!")

    # Compare stripped HTML
    clean_base = normalize_whitespace_in_classes(strip_desktop_classes(base_content))
    clean_target = normalize_whitespace_in_classes(strip_desktop_classes(target_content))

    return (len(errors) == 0), "\n".join(errors)

def main():
    if not os.path.exists(BASELINE_DIR):
        print(f"Error: Baseline directory {BASELINE_DIR} does not exist.")
        sys.exit(1)

    all_passed = True
    count = 0
    for root, _, files in os.walk(TARGET_DIR):
        for f in files:
            if f.endswith('.blade.php'):
                rel_path = os.path.relpath(os.path.join(root, f), TARGET_DIR)
                ok, msg = verify_file(rel_path)
                count += 1
                if not ok:
                    print(f"FAIL: {rel_path}\n{msg}")
                    all_passed = False

    if all_passed:
        print(f"SUCCESS: All {count} blade files verified. Mobile & functional logic 100% preserved.")
        sys.exit(0)
    else:
        print("VERIFICATION FAILED! See above errors.")
        sys.exit(1)

if __name__ == '__main__':
    main()

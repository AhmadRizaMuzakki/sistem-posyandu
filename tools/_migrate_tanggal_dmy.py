#!/usr/bin/env python3
"""One-off helper: replace hari/bulan/tahun date blocks with x-tanggal-dmy-input in blade files."""
from __future__ import annotations

import re
from pathlib import Path

ROOT = Path(r"c:\laragon\www\sistem-posyandu")

# Maps wire:model field for hari_* -> (label, wire model for tanggal, required)
FIELD_MAP = {
    "hari_imunisasi": ("Tanggal Imunisasi", "tanggal_imunisasi", True),
    "hari_lahir_sasaran": ("Tanggal Lahir", "tanggal_lahir_sasaran", True),
    "hari_lahir_orangtua": ("Tanggal Lahir Orangtua", "tanggal_lahir_orangtua", True),
    "hari_lahir_remaja": ("Tanggal Lahir", "tanggal_lahir_remaja", True),
    "hari_lahir_orangtua_remaja": ("Tanggal Lahir Orangtua", "tanggal_lahir_orangtua_remaja", True),
    "hari_lahir_dewasa": ("Tanggal Lahir", "tanggal_lahir_dewasa", True),
    "hari_lahir_pralansia": ("Tanggal Lahir", "tanggal_lahir_pralansia", True),
    "hari_lahir_lansia": ("Tanggal Lahir", "tanggal_lahir_lansia", True),
    "hari_lahir_ibuhamil": ("Tanggal Lahir", "tanggal_lahir_ibuhamil", True),
    "hari_lahir_suami_ibuhamil": ("Tanggal Lahir Suami", "tanggal_lahir_suami_ibuhamil", False),
    "hari_lahir_orangtua": ("Tanggal Lahir", "tanggal_lahir_orangtua", True),  # orangtua modal may differ
    "hari_lahir_pendidikan": ("Tanggal Lahir", "tanggal_lahir_pendidikan", True),
}

# For orangtua modal specifically label is Tanggal Lahir
ORANGTUA_MODAL_LABEL = {
    "hari_lahir_orangtua": ("Tanggal Lahir", "tanggal_lahir_orangtua", True),
}

BLADE_FILES = [
    "resources/views/livewire/super-admin/posyandu-detail/modals/imunisasi-modal.blade.php",
    "resources/views/livewire/posyandu/modals/imunisasi-modal.blade.php",
    "resources/views/livewire/posyandu/kader-imunisasi.blade.php",
    "resources/views/livewire/super-admin/posyandu-detail/modals/balita-modal.blade.php",
    "resources/views/livewire/posyandu/modals/balita-modal.blade.php",
    "resources/views/livewire/super-admin/posyandu-detail/modals/remaja-modal.blade.php",
    "resources/views/livewire/posyandu/modals/remaja-modal.blade.php",
    "resources/views/livewire/super-admin/posyandu-detail/modals/dewasa-modal.blade.php",
    "resources/views/livewire/posyandu/modals/dewasa-modal.blade.php",
    "resources/views/livewire/super-admin/posyandu-detail/modals/pralansia-modal.blade.php",
    "resources/views/livewire/posyandu/modals/pralansia-modal.blade.php",
    "resources/views/livewire/super-admin/posyandu-detail/modals/lansia-modal.blade.php",
    "resources/views/livewire/posyandu/modals/lansia-modal.blade.php",
    "resources/views/livewire/super-admin/posyandu-detail/modals/ibuhamil-modal.blade.php",
    "resources/views/livewire/posyandu/modals/ibuhamil-modal.blade.php",
    "resources/views/livewire/super-admin/posyandu-detail/modals/orangtua-modal.blade.php",
    "resources/views/livewire/posyandu/modals/orangtua-modal.blade.php",
    "resources/views/livewire/super-admin/posyandu-detail/modals/pendidikan-modal.blade.php",
    "resources/views/livewire/posyandu/modals/pendidikan-modal.blade.php",
]

# Match a full date block: optional comment + label wrapper div containing grid-cols-3 with hari_* select
# Capture the hari wire model and surrounding outer div from label through optional tanggal error.
BLOCK_RE = re.compile(
    r"""
    (?:[ \t]*\{\{--[^\n]*--\}\}\s*)?          # optional blade comment
    [ \t]*<div>\s*
    [ \t]*<label[^>]*>.*?Tanggal.*?</label>\s*
    [ \t]*<div class="grid grid-cols-3 gap-2">\s*
    .*?wire:model="(?P<field>hari_[a-z0-9_]+)".*?
    [ \t]*</div>\s*                           # close grid
    (?:[ \t]*@error\('tanggal_[^']+'\)[^\n]*\n)?
    [ \t]*</div>
    """,
    re.VERBOSE | re.DOTALL | re.IGNORECASE,
)

# Variant without wrapping label div structure - match from label through grid close + tanggal error + outer close
# Actually some blocks nest differently. Simpler approach: find grid with hari_ and expand outward.


def find_blocks(content: str):
    """Find (start, end, hari_field) for each hari/bulan/tahun grid block including outer wrapper."""
    results = []
    for m in re.finditer(r'wire:model="(hari_[a-z0-9_]+)"', content):
        field = m.group(1)
        # walk back to find start of outer <div> that contains the label Tanggal
        pos = m.start()
        # find grid div start
        grid_start = content.rfind('<div class="grid grid-cols-3 gap-2">', 0, pos)
        if grid_start == -1:
            continue
        # find label before grid
        label_start = content.rfind("<label", 0, grid_start)
        if label_start == -1:
            continue
        # find outer div before label (the wrapper)
        outer_start = content.rfind("<div>", 0, label_start)
        # also check for comment before outer
        before = content[max(0, outer_start - 80):outer_start]
        comment_match = re.search(r"(\{\{--[^\n]*--\}\}\s*)$", before)
        start = outer_start
        if comment_match:
            start = outer_start - len(comment_match.group(1))
            # include leading whitespace of comment line
            line_start = content.rfind("\n", 0, start) + 1
            start = line_start

        # find end: after grid closes, optional @error tanggal, then </div>
        # find matching close for grid
        i = grid_start + len('<div class="grid grid-cols-3 gap-2">')
        depth = 1
        while i < len(content) and depth > 0:
            if content.startswith("<div", i):
                depth += 1
                i += 4
            elif content.startswith("</div>", i):
                depth -= 1
                i += 6
            else:
                i += 1
        grid_end = i
        # skip whitespace and optional @error for tanggal_
        rest = content[grid_end:]
        err = re.match(r"\s*@error\('tanggal_[^']+'\)[^\n]*\n?", rest)
        after_err = grid_end + (err.end() if err else 0)
        # next should be </div> closing outer
        rest2 = content[after_err:]
        close = re.match(r"\s*</div>", rest2)
        if not close:
            print(f"  WARN: no outer close for {field}")
            continue
        end = after_err + close.end()
        results.append((start, end, field))
    return results


def component_for(field: str, path: str) -> str:
    # balita orangtua uses "Tanggal Lahir Orangtua"; orangtua modal uses "Tanggal Lahir"
    if field == "hari_lahir_orangtua" and "orangtua-modal" in path.replace("\\", "/"):
        label, model, required = ("Tanggal Lahir", "tanggal_lahir_orangtua", True)
    elif field == "hari_lahir_orangtua":
        label, model, required = ("Tanggal Lahir Orangtua", "tanggal_lahir_orangtua", True)
    elif field in FIELD_MAP:
        label, model, required = FIELD_MAP[field]
    else:
        raise KeyError(field)

    # Infer indent from typical 24 spaces for modal content; use 24 default
    req = "true" if required else "false"
    return (
        f'<x-tanggal-dmy-input\n'
        f'                            label="{label}"\n'
        f'                            :required="{req}"\n'
        f'                            wire:model="{model}"\n'
        f'                        />'
    )


def transform_blade(path: Path) -> bool:
    content = path.read_text(encoding="utf-8")
    blocks = find_blocks(content)
    if not blocks:
        print(f"NO BLOCKS: {path}")
        return False
    # replace from end to start
    new = content
    for start, end, field in sorted(blocks, key=lambda x: x[0], reverse=True):
        # preserve leading indent of the replaced region
        line_start = new.rfind("\n", 0, start) + 1
        indent = new[line_start:start]
        # if start includes comment, indent is at line_start already baked in start
        # compute indent of outer div
        snippet = new[start:end]
        first_div = snippet.find("<div>")
        if first_div >= 0:
            # indent is whitespace before that div on its line
            ls = new.rfind("\n", 0, start + first_div) + 1
            indent = new[ls : start + first_div]
        else:
            indent = "                        "
        try:
            comp = component_for(field, str(path))
        except KeyError:
            print(f"  UNKNOWN field {field} in {path}")
            continue
        # indent component lines
        lines = comp.split("\n")
        indented = indent + lines[0] + "\n" + "\n".join(
            (indent + l if l.strip() else l) for l in lines[1:]
        )
        # Fix: first line already has indent from template spaces - strip and re-indent
        # component_for already has spaces; rebuild cleanly
        req = "true" if (field != "hari_lahir_suami_ibuhamil") else "false"
        if field == "hari_lahir_orangtua" and "orangtua-modal" in str(path).replace("\\", "/"):
            label, model, required = ("Tanggal Lahir", "tanggal_lahir_orangtua", True)
        elif field == "hari_lahir_orangtua":
            label, model, required = ("Tanggal Lahir Orangtua", "tanggal_lahir_orangtua", True)
        else:
            label, model, required = FIELD_MAP[field]
        req = "true" if required else "false"
        replacement = (
            f'{indent}<x-tanggal-dmy-input\n'
            f'{indent}    label="{label}"\n'
            f'{indent}    :required="{req}"\n'
            f'{indent}    wire:model="{model}"\n'
            f'{indent}/>'
        )
        new = new[:start] + replacement + new[end:]
        print(f"  replaced {field} in {path.name}")
    if new != content:
        path.write_text(new, encoding="utf-8")
        return True
    return False


def main():
    changed = 0
    for rel in BLADE_FILES:
        path = ROOT / rel
        if not path.exists():
            print(f"MISSING: {path}")
            continue
        print(f"Processing {rel}")
        if transform_blade(path):
            changed += 1
    print(f"Done. Files changed: {changed}")


if __name__ == "__main__":
    main()

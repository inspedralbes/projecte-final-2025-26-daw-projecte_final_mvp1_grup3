#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Afegeix capçaleres de fitxer segons agents/ (AgentLaravel, AgentNode, AgentJavascript, AgentDatabase).
No insereix blocs buits que trenquessin el codi; només documentació i separadors on correspongui.
"""

from __future__ import annotations

import re
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
TARGETS = [
    ROOT / "backend-laravel",
    ROOT / "backend-node",
    ROOT / "database",
    ROOT / "frontend",
]
SKIP_DIRS = {
    "vendor",
    "node_modules",
    ".nuxt",
    ".output",
    "bootstrap/cache",
    "storage",
    "public/build",
}

MARKER_PHP = "//================================ NAMESPACES"
MARKER_SQL = "-- AGENT_DATABASE:"
MARKER_VUE = "AgentNuxt.md"
MARKER_JS_AGENT = "AgentNode.md"


def should_skip(path: Path) -> bool:
    return bool(set(path.parts) & SKIP_DIRS)


def rel_name(path: Path) -> str:
    try:
        return str(path.relative_to(ROOT)).replace("\\", "/")
    except ValueError:
        return str(path)


def human_purpose(rel: str, ext: str) -> str:
    base = Path(rel).stem.replace("_", " ")
    if ext == ".php":
        return f"Capa Laravel: {base}."
    if ext == ".js":
        return f"Modul JavaScript ES5: {base}."
    if ext == ".vue":
        return f"Component o pagina Nuxt: {base}."
    if ext == ".sql":
        return f"SQL (estructura o dades): {base}."
    return f"Fitxer Loopy: {base}."


def has_file_docblock(content: str, agent_hint: str) -> bool:
    head = content[:800]
    return "/**" in head and ("Loopy" in head or agent_hint in head or "agents/" in head)


def process_php(content: str, rel: str) -> str | None:
    if MARKER_PHP in content:
        return None
    if not content.lstrip().startswith("<?php"):
        return None

    out = content
    if not has_file_docblock(content, "AgentLaravel"):
        m = re.match(r"(\<\?php\s*)(declare\([^;]+\);\s*)?", content, re.I | re.S)
        if not m:
            return None
        doc = (
            "/**\n"
            f" * {human_purpose(rel, '.php')}\n"
            " * Comentaris: agents/backend/AgentLaravel.md\n"
            " */\n\n"
        )
        out = content[: m.end()] + "\n" + doc + content[m.end() :]

    # Bloc NAMESPACES abans del primer use (classes amb namespace)
    if "\nuse " in out and MARKER_PHP not in out:
        idx = out.find("\nuse ")
        if idx > 0:
            block = "\n//================================ NAMESPACES / IMPORTS ============\n"
            out = out[:idx] + block + out[idx:]

    # Bloc MÈTODES abans de la classe si no existeix
    if "\nclass " in out and "//================================ MÈTODES" not in out:
        idx = out.find("\nclass ")
        if idx > 0:
            block = "\n//================================ MÈTODES / FUNCIONS ===========\n"
            out = out[:idx] + block + out[idx:]

    return out if out != content else None


def process_js(content: str, rel: str) -> str | None:
    if has_file_docblock(content, MARKER_JS_AGENT):
        return None
    if re.search(r"/\*\*", content[:600]):
        return None
    stripped = content.lstrip()
    if not stripped or stripped.startswith("#!"):
        return None
    header = (
        "/**\n"
        f" * {human_purpose(rel, '.js')}\n"
        " * Comentaris: agents/backend/AgentNode.md, agents/frontend/AgentJavascript.md\n"
        " * Regles: var, function, sense arrow functions; passos A/B/C dins funcions complexes.\n"
        " */\n\n"
    )
    if stripped.startswith("'use strict'"):
        end = content.find(";") + 1
        nl = content.find("\n", end)
        if nl == -1:
            nl = end
        return content[: nl + 1] + "\n\n" + header + content[nl + 1 :]
    return header + content


def process_vue(content: str, rel: str) -> str | None:
    if MARKER_VUE in content[:500]:
        return None
    if not content.lstrip().startswith("<"):
        return None
    header = (
        "<!--\n"
        f"  {human_purpose(rel, '.vue')}\n"
        "  Comentaris de codi: agents/frontend/AgentNuxt.md + AgentJavascript.md\n"
        "-->\n"
    )
    return header + content


def process_sql(content: str, rel: str) -> str | None:
    if MARKER_SQL in content[:600]:
        return None
    header = (
        f"-- AGENT_DATABASE: {rel}\n"
        f"-- {human_purpose(rel, '.sql')}\n"
        "-- Comentaris: agents/database/AgentDatabase.md\n"
        "-- GET via API Laravel | CUD via Node -> Redis -> Laravel\n\n"
    )
    return header + content


def process_file(path: Path) -> bool:
    rel = rel_name(path)
    ext = path.suffix.lower()
    try:
        raw = path.read_text(encoding="utf-8")
    except (UnicodeDecodeError, OSError):
        return False
    updated = None
    if ext == ".php":
        updated = process_php(raw, rel)
    elif ext == ".js":
        updated = process_js(raw, rel)
    elif ext == ".vue":
        updated = process_vue(raw, rel)
    elif ext == ".sql":
        updated = process_sql(raw, rel)
    if updated is None or updated == raw:
        return False
    path.write_text(updated, encoding="utf-8", newline="\n")
    return True


def main() -> int:
    changed = 0
    scanned = 0
    exts = {".php", ".js", ".vue", ".sql"}
    for base in TARGETS:
        if not base.exists():
            print(f"Ometent (no existeix): {base}")
            continue
        for path in base.rglob("*"):
            if not path.is_file() or path.suffix.lower() not in exts:
                continue
            if should_skip(path):
                continue
            scanned += 1
            if process_file(path):
                changed += 1
    print(f"Fitxers revisats: {scanned} | Modificats: {changed}")
    return 0


if __name__ == "__main__":
    sys.exit(main())

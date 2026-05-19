"""Script temporal per exportar backlog Taiga (no commitejar)."""
import json
import sys
from collections import defaultdict
from pathlib import Path

import httpx
from dotenv import dotenv_values

if hasattr(sys.stdout, "reconfigure"):
    sys.stdout.reconfigure(encoding="utf-8")

ROOT = Path(__file__).resolve().parents[1]
env = dotenv_values(ROOT / ".cursor" / "taiga-mcp.env")
base = env["TAIGA_API_URL"].rstrip("/")

r = httpx.post(
    f"{base}/auth",
    json={
        "type": "normal",
        "username": env["TAIGA_USERNAME"],
        "password": env["TAIGA_PASSWORD"],
    },
    timeout=30,
)
r.raise_for_status()
token = r.json()["auth_token"]
h = {"Authorization": f"Bearer {token}"}

projs = httpx.get(f"{base}/projects", headers=h, timeout=30).json()
print("\n=== ALL PROJECTS ===")
for p in projs:
    print(f"  [{p['id']}] {p.get('name')} | slug={p.get('slug')}")

wanted = (env.get("TAIGA_PROJECT_NAME") or "").lower()
target = None
for p in projs:
    name = (p.get("name") or "").lower()
    slug = (p.get("slug") or "").lower()
    if wanted and (wanted in name or wanted.replace("_", "-") in slug or "grup3" in name or "grup3" in slug):
        target = p
        break
if not target:
    for p in projs:
        name = (p.get("name") or "").lower()
        slug = (p.get("slug") or "").lower()
        if "mvp1" in name or "projecte" in name or "loopy" in name:
            target = p
            break
# Try fetch by slug (public or member)
for slug in [
    "ikerlopezgomez-projecte_final_mvp1_grup5",
    "projecte-final-mvp1-grup3",
    "projecte_final_mvp1_grup3",
]:
    try:
        pr = httpx.get(f"{base}/projects/by_slug", headers=h, params={"slug": slug}, timeout=30)
        if pr.status_code == 200:
            target = pr.json()
            print(f"\nFOUND BY SLUG {slug}:", json.dumps({"id": target["id"], "name": target["name"], "slug": target["slug"]}, ensure_ascii=False))
            break
    except Exception:
        pass
else:
    if not target and projs:
        target = projs[0]
    print("\nSELECTED PROJECT:", json.dumps({"id": target["id"], "name": target["name"], "slug": target["slug"]}, ensure_ascii=False))

pid = target["id"]

ms = httpx.get(f"{base}/milestones", headers=h, params={"project": pid}, timeout=30).json()
print("\n=== MILESTONES ===")
for m in sorted(ms, key=lambda x: x.get("estimated_start") or ""):
    print(
        f"  [{m['id']}] {m['name']} | closed={m.get('closed')} | "
        f"{m.get('estimated_start')} - {m.get('estimated_finish')}"
    )

stories = httpx.get(f"{base}/userstories", headers=h, params={"project": pid}, timeout=60).json()
print(f"\n=== USER STORIES ({len(stories)}) ===")
for s in sorted(stories, key=lambda x: x.get("ref", 0)):
    st = s.get("status_extra_info") or {}
    st_name = st.get("name") if isinstance(st, dict) else s.get("status")
    print(f"  #{s['ref']} | {s['subject']} | {st_name}")

tasks = httpx.get(f"{base}/tasks", headers=h, params={"project": pid}, timeout=60).json()
print(f"\n=== TASKS BY STORY ({len(tasks)} total) ===")
by_story = defaultdict(list)
for t in tasks:
    us = t.get("user_story")
    if us:
        by_story[us].append(t)

for sid in sorted(by_story.keys()):
    story = next((x for x in stories if x["id"] == sid), None)
    ref = story["ref"] if story else "?"
    subj = (story["subject"][:55] if story else "?")
    print(f"\n  US#{ref} {subj}")
    for t in by_story[sid]:
        print(f"    - {t['subject']}")

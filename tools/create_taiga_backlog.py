"""Crea user stories i tasques al backlog de Taiga."""
import json
import sys
import time
from pathlib import Path

import httpx
from dotenv import dotenv_values

if hasattr(sys.stdout, "reconfigure"):
    sys.stdout.reconfigure(encoding="utf-8")

ROOT = Path(__file__).resolve().parents[1]
env = dotenv_values(ROOT / ".cursor" / "taiga-mcp.env")
base = env["TAIGA_API_URL"].rstrip("/")

auth = httpx.post(
    f"{base}/auth",
    json={
        "type": "normal",
        "username": env["TAIGA_USERNAME"],
        "password": env["TAIGA_PASSWORD"],
    },
    timeout=30,
)
auth.raise_for_status()
token = auth.json()["auth_token"]
h = {"Authorization": f"Bearer {token}", "Content-Type": "application/json"}

slug = "ikerlopezgomez-projecte_final_mvp1_grup5"
project = httpx.get(f"{base}/projects/by_slug", headers=h, params={"slug": slug}, timeout=30)
project.raise_for_status()
pid = project.json()["id"]
print(f"Projecte: {project.json()['name']} (id={pid})")

# Estat per defecte de user stories (New)
us_statuses = httpx.get(f"{base}/userstory-statuses", headers=h, params={"project": pid}, timeout=30).json()
us_status_new = next((s["id"] for s in us_statuses if s.get("slug") == "new"), us_statuses[0]["id"])

task_statuses = httpx.get(f"{base}/task-statuses", headers=h, params={"project": pid}, timeout=30).json()
task_status_new = next((s["id"] for s in task_statuses if s.get("slug") == "new"), task_statuses[0]["id"])

existing = httpx.get(f"{base}/userstories", headers=h, params={"project": pid}, timeout=60).json()
existing_subjects = {s["subject"].strip().lower() for s in existing}

BACKLOG = [
    {
        "subject": "Com a usuari puc comprar a la botiga i gestionar el meu inventari",
        "tasks": [
            "Puc veure el catàleg de la botiga amb preus en monedes",
            "Puc comprar articles si tinc prou monedes",
            "Puc veure el meu inventari",
            "Puc equipar roba al monstre",
            "Puc canviar el fons de l'aplicació",
            "Puc usar el recuperador de ratxa des de l'inventari",
        ],
    },
    {
        "subject": "Com a usuari puc veure la pantalla d'inici gamificada",
        "tasks": [
            "Puc veure el monstre, nivell, XP i monedes a la home",
            "Puc completar hàbits des de la pantalla principal",
            "Puc veure la missió diària i la ratxa a la home",
            "Puc rebre avís quan pujo de nivell",
            "Puc veure quan es trenca la ratxa",
        ],
    },
    {
        "subject": "Com a usuari puc crear categories personalitzades d'hàbits",
        "tasks": [
            "Puc crear una categoria pròpia en crear un hàbit",
            "Puc triar categories predefinides o les meves",
        ],
    },
    {
        "subject": "Com a usuari puc importar hàbits i plantilles des del feed, xat i clans",
        "tasks": [
            "Puc importar un hàbit des d'un post del fòrum",
            "Puc importar una plantilla des d'un post del fòrum",
            "Puc importar hàbits o plantilles des del xat amb un amic",
            "Puc importar hàbits o plantilles des del xat del clan",
        ],
    },
    {
        "subject": "Com a usuari puc denunciar usuaris",
        "tasks": [
            "Puc denunciar un perfil o contingut inadequat",
            "L'administrador pot veure les denúncies al panell",
        ],
    },
    {
        "subject": "Com a usuari puc veure el temps a la pantalla principal",
        "tasks": [
            "Puc veure el temps a la pantalla principal",
            "Puc posar la meva ciutat per veure el clima",
        ],
    },
    {
        "subject": "Com a usuari puc canviar l'idioma de l'aplicació",
        "tasks": [
            "Puc canviar l'idioma de la interfície",
        ],
    },
    {
        "subject": "Com a usuari rebo un correu de benvinguda en registrar-me",
        "tasks": [
            "Rebo un correu de benvinguda en registrar-me",
        ],
    },
]

created_us = []
skipped_us = []

for entry in BACKLOG:
    subject = entry["subject"]
    key = subject.strip().lower()
    if key in existing_subjects:
        skipped_us.append(subject)
        print(f"SKIP (ja existeix): {subject}")
        continue

    us_resp = httpx.post(
        f"{base}/userstories",
        headers=h,
        json={
            "project": pid,
            "subject": subject,
            "status": us_status_new,
        },
        timeout=30,
    )
    us_resp.raise_for_status()
    us = us_resp.json()
    created_us.append(us)
    existing_subjects.add(key)
    print(f"US #{us.get('ref')} creat: {subject}")

    for task_subject in entry["tasks"]:
        time.sleep(0.15)
        t_resp = httpx.post(
            f"{base}/tasks",
            headers=h,
            json={
                "project": pid,
                "subject": task_subject,
                "user_story": us["id"],
                "status": task_status_new,
            },
            timeout=30,
        )
        t_resp.raise_for_status()
        print(f"  - tasca: {task_subject}")

print(f"\nResum: {len(created_us)} user stories creades, {len(skipped_us)} omitides")

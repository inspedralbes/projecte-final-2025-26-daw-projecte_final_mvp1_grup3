# Documentació de Loopy

Aquesta carpeta conté tota la documentació oficial i tècnica del projecte **Loopy (El Teu Habit Loop Gamificat)** per al Projecte Final de Cicle de DAW.

---

## 🌟 Documentació Principal (Oficial DAW)

| Document | Descripció |
| :--- | :--- |
| **[Manual d'Usuari i Documentació Tècnica](./ManualUsuari.md)** | **La veritable documentació oficial demanada per al projecte.** Conté la introducció completa, requisits del sistema, gestió de comptes, taula de rols (Visitant, Client, Admin), guia de navegació, fluxos pas a pas de les funcionalitats clau (Habit Loop, IA Gemini, Dashboard gamificat, Catàleg), funcionament del Backoffice d'administració (arquitectura híbrida REST + WebSockets) i resolució de problemes freqüents (FAQ). |

---

## 📁 Estructura de Directoris i Guies Tècniques

Per mantenir el repositori net i organitzat, la resta de guies de desenvolupament, planificació i arquitectura s'han dividit en les tres carpetes temàtiques següents:

### 1. 🏗️ Arquitectura i Backend (`/arquitectura_backend`)
Conté la documentació detallada sobre l'estructura interna del codi, la comunicació en temps real i l'API REST:
* **[Estructura del Projecte](./arquitectura_backend/02-ESTRUCTURA-PROYECTO.md):** Mapa complet dels directoris i fitxers del repositori.
* **[Admin Backend](./arquitectura_backend/ADMIN-BACKEND.md):** Arquitectura detallada del backoffice, middleware d'autenticació i fluxos CUD via WebSockets i Redis.
* **[Backend Documentation](./arquitectura_backend/BACKEND-DOCUMENTATION.md):** Guia dels serveis, models i controladors principals de Laravel.
* **[Connexió Redis - Laravel](./arquitectura_backend/CONEXION-REDIS-LARAVEL.md):** Configuració del sistema de cues i Pub/Sub amb Redis.
* **[Plantilles Redis Payload](./arquitectura_backend/plantillas-redis-payload.md):** Estructura dels paquets JSON utilitzats en la comunicació en temps real.

### 2. 🚀 Setup i Migracions (`/setup_migracions`)
Guies pas a pas per a la posada en marxa de l'entorn de desenvolupament i resolució d'inconvenients tècnics:
* **[Setup des de Zero](./setup_migracions/01-SETUP-DESDE-CERO.md):** Manual d'instal·lació amb Docker Compose, configuració d'entorn (`.env`), migracions i workers.
* **[Pla de Migració Home API](./setup_migracions/PLAN-MIGRACIO-HOME-API.md):** Estratègia i passos per a l'optimització de la càrrega del dashboard.
* **[Lentitud Read API](./setup_migracions/LENTITUD_READ_API.md):** Anàlisi i solucions aplicades per millorar el temps de resposta del backend.
* **[Fix Home Cargar Dades](./setup_migracions/FIX-HOME-CARGAR-DADES.md):** Documentació sobre la correcció de la càrrega inicial de dades de l'usuari.

### 3. 📋 Gestió del Projecte i MVP (`/gestio_projecte`)
Documents de seguiment de tasques, definició de l'abast i recull de comentaris dels usuaris de prova:
* **[Funcionalitats Usuari](./gestio_projecte/FUNCIONALITATS_USUARI.md):** Llistat complet de les funcionalitats actives a l'MVP1 de Loopy.
* **[Checklist](./gestio_projecte/Checklist.md):** Llista de tasques i control de qualitat abans del lliurament.
* **[Fase 2](./gestio_projecte/Fase2.md):** Planificació de futures funcionalitats i millores per a pròximes versions.
* **[Feedback Usuaris](./gestio_projecte/Feetback_Usuaris.md):** Recull d'opinions i suggeriments de millora obtinguts durant les proves d'usuari.

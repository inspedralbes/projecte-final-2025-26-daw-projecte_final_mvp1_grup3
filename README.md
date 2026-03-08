# 🌀 Loopy: El Teu Habit Loop Gamificat

**Loopy** és una aplicació de seguiment d'hàbits revolucionària dissenyada per transformar la teva rutina diària en una aventura captivadora. Basada en el concepte científic del **'Habit Loop'** (Senyal, Rutina, Recompensa), Loopy utilitza una estètica **'soft design'** (efecte plastilina/claymorphism) per oferir una experiència visual relaxant i tàctil. L'aplicació compta amb una **mascota interactiva** que evoluciona amb tu, convertint la disciplina en un joc on el creixement personal és tangible.

## ✨ Proposta de Valor

1.  **🤖 Onboarding intel·ligent amb IA (Gemini):** No comencis de zero. La nostra IA analitza el teu estil de vida i objectius per suggerir-te un 'Habit Loop' personalitzat des del primer minut.
2.  **🐾 Feedback Emocional (La teva Mascota):** La teva mascota reacciona en temps real als teus progressos. Si compleixes els teus hàbits, estarà feliç i plena d'energia; si no, necessitarà la teva atenció.
3.  **💎 Recompensa Immediata (XP i Monedes):** Cada acció suma. Guanya experiència (XP) per pujar de nivell i monedes per personalitzar l'entorn de la teva mascota o desbloquejar objectes exclusius.

## 🛠️ Stack Tecnològic

- **Frontend:** Nuxt 3 (SPA), Pinia per a la gestió d'estat, Tailwind CSS per al disseny i Capacitor 6 per a la distribució nativa.
- **Backend Core:** Laravel 11 (PHP 8.3) amb Sanctum per a una autenticació segura.
- **Real-time & IA:** Node.js 22 (Fastify), Socket.io per a la interactivitat en temps real i Google Gemini API per a la generació de contingut dinàmic.
- **Infraestructura:** PostgreSQL 16 com a base de dades principal, Redis 7 (Pub/Sub) per al caching i notificacions, i Docker per a un desplegament consistent.

## 📊 Taula de Versions

| Component      | Versió |
| :------------- | :----- |
| **Node.js**    | 22.x   |
| **PHP**        | 8.3    |
| **Laravel**    | 11.x   |
| **Nuxt**       | 3.11   |
| **PostgreSQL** | 16     |
| **Redis**      | 7.2    |

## 🚀 Instal·lació (Docker)

1. Copiar variables d'entorn: `cp .env.example .env` i editar `JWT_SECRET` i credencials si cal.
2. Des de la carpeta `docker`: `docker compose up -d --build`.
3. Frontend: http://localhost:3000 | Node: http://localhost:3001 | Laravel: http://localhost:8000.
4. Migracions: `docker compose exec backend-laravel php artisan migrate`.
5. Worker Redis (opcional): s'inicia automàticament amb Docker; manual: `php artisan redis:unified-worker`.

Detall a `docker/README.md`.

## 🔐 Variables d'Entorn

El fitxer `.env.example` a la arrel unifica credencials de PostgreSQL, Redis, `JWT_SECRET` i URLs dels serveis. Copiar a `.env` i completar abans d’executar Docker.

## 🔗 Enllaços d’Interès

- 🎨 [Disseny a Figma](https://www.figma.com/design/XyO3s84xWpSUEjQk2fwktb/Aplicaci%C3%B3-habits?node-id=0-1&t=D6xaYpsrqnb5eyuY-1)
- 📋 [Gestió del Projecte a Taiga](https://tree.taiga.io/project/ikerlopezgomez-projecte_final_mvp1_grup5/timeline)

## 👥 Autors

Aquest projecte ha estat desenvolupat pels següents alumnes de l'**Institut Pedralbes** (2n DAW):

- **Biel Domínguez**
- **Llorenç Carnisser**
- **Iker Mata**
- **Iker Lopez**

---

_Projecte Final de Cicle - 2025-26 - Grup 3_

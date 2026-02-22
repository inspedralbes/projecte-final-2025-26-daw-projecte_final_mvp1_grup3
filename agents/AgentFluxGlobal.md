# 🔄 Agent de Flux Global (The Full Circle)

## 📋 Context de l'Arquitectura
Aquest document defineix el camí que segueix una dada des que l'usuari interacciona amb el frontend fins que es persisteix a la base de dades i rep confirmació. És la guia mestra per entendre la sincronització entre els 4 pilars del projecte: **Frontend (Nuxt)**, **Relay (Node.js)**, **Bus de Dades (Redis)** i **Core (Laravel)**.

## 🚀 El Flux del "Cercle Complet"

### 1. Frontend (L'Acció)
- **Store de Pinia**: L'usuari fa clic. El store guarda un **Snapshot** (còpia de seguretat) i aplica una **Mutació Optimista** (actualitza la UI al moment).
- **Socket.io Client**: Emet l'esdeveniment (ex: `habit_action`).

### 2. Node.js (El Pont / Bridge)
- **Socket Handler**: Rep el missatge del socket.
- **Redis Queue**: Afegeix la tasca a la cua `habits_queue` mitjançant `LPUSH`.

### 3. Laravel (El Motor de Negoci)
- **Redis Worker**: Està escoltant amb `BRPOP`. Quan arriba la dada, la processa.
- **PostgreSQL**: Guarda la dada de forma real. Calcula XP, ratxes i gamificació.
- **Redis Pub/Sub**: Publica el resultat (èxit o error) al canal `habits_feedback_channel`.

### 4. Node.js (El Feedback)
- **Feedback Subscriber**: Escolta el canal de Redis.
- **Socket.io Server**: Envia la confirmació (`habit_action_confirmed`) o l'error (`validation_error`) al socket específic de l'usuari.

### 5. Frontend (El Resultat)
- **Rollback o Confirmació**: 
    - Si el resultat és **ÈXIT**: El store confirma la dada (pot actualitzar IDs temporals).
    - Si el resultat és **ERROR**: El store fa **Rollback** usant el Snapshot, tornant la UI a l'estat anterior i mostrant un avis a l'usuari.

## 🛠️ Responsabilitats de l'Agent
1. **Traçabilitat**: Quan es modifica un event en un punt (ex: Node), verificar que el receptor (ex: Laravel) entengui el nou format.
2. **Nomenclatura**: Mantenir els noms d'esdeveniments i canals sincronitzats a tot el "Cercle".
3. **Gestió d'Errors**: Assegurar que cada operació optimista tingui el seu camí de tornada per al rollback en cas de fallada.

## 📜 Regles de Or
- **No trencar el Bridge**: Qualsevol canvi en el JSON enviat per Redis ha de ser acceptat pel worker de Laravel.
- **Zero Latència Percibida**: Totes les operacions de creació/edició han de ser optimistes al frontend.
- **Feedback Obligatori**: Laravel sempre ha de publicar una resposta a Redis, fins i tot si l'acció ha fallat.

## ✅ Regla GET/CUD
- **GET**: sempre via `fetch` contra l'API de Laravel (rutes a `backend-laravel/routes/api.php`).
- **CUD**: crear/actualitzar/eliminar via Node.js → Redis → Laravel; sockets només per feedback/confirmació.

# Agent de Nuxt (Framework i Estructura)

Aquest document defineix com s'ha d'estructurar i desenvolupar l'aplicació web utilitzant **Nuxt 3**. Aquest agent treballa en coordinació amb l'`AgentJavascript` (per a la lògica) i l'`AgentTailwind` (per al disseny).

## 1. Objectiu de l'Agent
Gestionar l'arquitectura del frontend, les rutes, els components i la integració amb els serveis de backend (Socket i API REST Laravel).

## 2. Estructura de Directoris
L'aplicació ha de seguir escrupolosament l'estructura de Nuxt 3, organitzada per domini:

- `pages/`: Conté les vistes. Cada fitxer `.vue` és una ruta automàtica. Inclou `auth/`, `admin/`, `error/`.
- `components/shared/`: Components sense rol (ErrorContent, LanguageSwitcher).
- `components/user/`: Components d'usuari (home/, habits/, HeaderUser).
- `components/admin/`: Components admin (AdminStatsGrid, AdminDataTable, AdminPagination, etc.).
- `layouts/`: Plantilles base (default.vue, admin.vue).
- `stores/`: Fitxers de Pinia (veure `AgentPinia.md`).
- `composables/useApi.js`: Capa d'API unificada (authFetch, useAuthFetch, getBaseUrl). **Única referència** per a peticions autenticades.
- `composables/user/`: useGameState, useHabits, useSocket, usePlantilles, useLogros.
- `composables/admin/`: useAdminApi, useAdminDashboard, useAdminSocket, useAdminList.
- `utils/mappers/apiMappers.js`: Transformació de respostes API al format del frontend.
- `middleware/`: Protecció de rutes (require-auth.global.js).

**Regla**: Les pàgines són orquestadors lleugers; la lògica va a composables i components.

## 3. Estil de Programació (Vue 3 + ES5)
Tot i fer servir Vue 3 (Composition API), hem de mantenir la coherència amb l'`AgentJavascript`.

- **Script Setup**: Es permet l'ús de `<script setup>`, però el contingut ha de seguir les regles ES5 (var, function, etc.).
- **Imports**: `import` de Vue i Nuxt automàtics (auto-imports) són preferibles.
- **Data Fetching**: Ús de `useApi` (authFetch, useAuthFetch) per a peticions autenticades. Mai `$fetch` directe sense refresh en 401.
- **Socket**: Ús del plugin `$socket` injectat per a la comunicació en temps real.

## 4. Components i Bento Grid UI
Les pàgines han de seguir el disseny **Bento Grid** (graella modular).
- Cada secció de la pàgina ha d'estar encapsulada en un `<div>` o component que representi una "cel·la" del Bento.
- L'estructura HTML ha de ser semàntica.
- NO EMOJIS!

## 5. Integració amb Altres Agents
- **Lògica**: Per a qualsevol funció JS complexa -> Veure `AgentJavascript.md`.
- **Estat**: Per a gestió de dades d'usuari/hàbits -> Veure `AgentPinia.md`.
- **Estils**: Per a classes CSS -> Veure `AgentTailwind.md`.

## 6. Exemple d'Estructura Vue
```html
<script setup>
// Imports explícits només si cal
import { useUserStore } from '~/stores/useUserStore';

// Declaració de variables amb VAR
var userStore = useUserStore();
var titolPagina = "Els meus Hàbits";

// Funcions clàssiques
function carregarDades() {
    // A. Crida asíncrona
    // ...
}
</script>

<template>
    <div class="bento-container">
        <h1>{{ titolPagina }}</h1>
        <HabitList :habits="userStore.habits" />
    </div>
</template>
```

## ✅ Regla GET/CUD
- **GET**: sempre via `fetch` contra l'API de Laravel (rutes a `backend-laravel/routes/api.php`).
- **CUD**: crear/actualitzar/eliminar via Node.js → Redis → Laravel; sockets només per feedback/confirmació.

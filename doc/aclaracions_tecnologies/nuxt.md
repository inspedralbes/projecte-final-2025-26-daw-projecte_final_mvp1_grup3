# Nuxt 3 / Vue 3 — Aclaracions tècniques (Loopy)

> Client web SPA: interfície, rutes, composables i integració amb API i sockets.

---

## 1. Què és i per què l'hem triat

| Aspecte | Detall |
| :--- | :--- |
| **Framework** | Nuxt 3 sobre Vue 3 |
| **Mode** | `ssr: false` → SPA pura |
| **Estil** | Tailwind CSS |
| **i18n** | `@nuxtjs/i18n` (ca, es, en) |

**Per què Nuxt i no Vue+Vite sol?**

- Convencions de carpetes (`pages/`, `plugins/`, `composables/`)
- Mòduls oficials (Pinia, i18n)
- Middleware de rutes (`require-onboarding`)
- `runtimeConfig` per URLs d'API i socket

---

## 2. Model d'arquitectura frontend

### **Component-based + Composables + Stores (Pinia)**

```
pages/ (rutes) → components/ (UI) → composables/ (lògica) → stores/ (estat) → useApi / $socket
```

| Capa | Responsabilitat |
| :--- | :--- |
| **Pages** | Orquestradors: muntar dades, enllaçar events |
| **Components** | Presentació per domini (`user/`, `admin/`, `shared/`) |
| **Composables** | Lògica reutilitzable (`useApi`, `useHabits`, `useSocketBridge`) |
| **Stores** | Estat global |
| **Utils/mappers** | Transformar respostes API → format UI |

---

## 3. Estructura de carpetes

```
frontend/
├── pages/              # Rutes automàtiques (home, habits, friends, admin/...)
├── components/
│   ├── shared/         # ErrorContent, LanguageSwitcher, modals
│   ├── user/           # Home, hàbits, social, calendar
│   ├── admin/          # Panell administració
│   └── clans/
├── composables/
│   ├── useApi.js       # authFetch centralitzat
│   ├── socket/         # Pont i registre feedback
│   ├── user/           # useHabits, useGameState...
│   └── admin/
├── stores/             # Pinia
├── plugins/
│   └── socket.client.js
├── lang/               # ca.json, es.json, en.json
├── middleware/         # require-onboarding.js
└── utils/mappers/      # apiMappers.js
```

---

## 4. Com es comunica amb el backend

| Necessitat | Mecanisme | Fitxer |
| :--- | :--- | :--- |
| Llegir dades | `authFetch(url)` → Laravel | `composables/useApi.js` |
| Escriure dades pesades | `$socket.emit(...)` → Node | `plugins/socket.client.js` |
| Estat global | Pinia stores | `stores/*.js` |
| Traduccions | `$t('key')` | `lang/*.json` |

Variables d'entorn (`nuxt.config.ts`):

```typescript
runtimeConfig: {
  public: {
    socketUrl: process.env.SOCKET_URL,  // :3001
    apiUrl: process.env.API_URL,        // :8000
  },
}
```

---

## 5. Rutes i middleware

- **`pages/`** genera rutes automàticament (`/home`, `/habits`, `/friends`, `/admin/...`)
- **Middleware global** `require-onboarding`: redirigeix usuaris sense onboarding completat
- **Layouts** `layouts/default.vue`: capçalera, navegació

---

## 6. Patró de pàgina típica

Exemple `friends.vue`:

1. `mounted` → `friendshipStore.fetchFriendsList()`
2. Template → llista + desplegable d'accions
3. Accions lleugeres → `authFetch` directe
4. Xat → component `ChatWindow` + socket

**Principi:** La pàgina no calcula XP ni parseja JSON complex; delega al store/composable.

---

## 7. Mappers API

`utils/mappers/apiMappers.js` converteix respostes Laravel (snake_case, noms de camps BD) al format que esperen els components (camelCase, camps UI).

**Per què?** Desacoblar canvis d'API dels components visuals.

---

## 8. Internacionalització (i18n)

| Config | Valor |
| :--- | :--- |
| `defaultLocale` | `ca` |
| `strategy` | `no_prefix` (sense `/ca/` a la URL) |
| Fitxers | `lang/ca.json`, `es.json`, `en.json` |

Components: `{{ $t('friends.message') }}`

---

## 9. Tests al frontend

| Eina | Ubicació |
| :--- | :--- |
| Vitest | `frontend/tests/unit/` |
| Cypress / Playwright | `frontend/tests/e2e/`, `cypress/` |

---

## 10. Preguntes freqüents dels professors

**P: Per què SPA i no SSR?**  
R: L'app és molt interactiva (sockets, animacions, mascota). SPA simplifica desplegament i evita hidratació complexa. `ssr: false` explícit.

**P: On va la lògica de negoci?**  
R: **No** a les pàgines. A composables, stores i (al servidor) Laravel/Node.

**P: Com organitzeu admin vs usuari?**  
R: Carpetes `components/admin/` i `pages/admin/` separades; `useAuthStore.isAdmin` controla accés.

**P: Tailwind on es configura?**  
R: `assets/css/main.css` + PostCSS al `nuxt.config.ts`. Veure [tailwind.md](./tailwind.md).

---

## 11. Referències internes

- `agents/frontend/AgentNuxt.md`, `AgentJavascript.md`
- `.cursor/rules/frontend-architecture.mdc`
- [pinia.md](./pinia.md), [sockets.md](./sockets.md)

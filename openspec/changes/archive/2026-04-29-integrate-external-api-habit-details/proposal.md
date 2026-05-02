## Why

Loopy needs to connect user habits to real-world context so the "Routine" stage in the Habit Loop feels tangible and motivating. This change is needed now to support richer habit context without overloading the home UI, while keeping a safe fallback when external APIs fail.

## What Changes

- Add optional habit linking to external resources (books, workouts, videos) and persist normalized metadata in `HABITS.metadata` (JSONB).
- Add a secure Laravel proxy layer so external API keys remain server-side and are never exposed to the Nuxt client.
- Add category-driven API search flows in habit create/edit screens with manual fallback input (title/image) when API search fails or user prefers manual entry.
- Add data-protection behavior on category change: warn users and clear incompatible linked metadata when confirmed.
- Add a secondary `detalls` button on the habit card to open a details modal with difficulty/reward, repetition text, manual goal fields, linked API data, and weather context for "Llar" habits.

## Capabilities

### New Capabilities

- `habit-external-metadata-linking`: Link habits to external content and store/reuse `api_id`, `titol`, `url_imatge`, and `tipus_api` in habit metadata with safe manual fallback.
- `secure-external-api-proxy`: Route all external API requests through Laravel controllers that inject keys from `.env` and return sanitized responses to frontend clients.
- `habit-details-modal`: Provide a secondary details modal from home habit cards showing structured habit context, including linked metadata and formatted routine information.
- `habit-category-context-rules`: Enforce category-driven search providers, warning-based metadata reset on category changes, and weather-based context checks for household habits.

### Modified Capabilities

<!-- None -->

## Impact

- Frontend (`Nuxt 3`): habit create/edit views, category watchers, Pinia state integration, and new details modal + secondary button on `pages/home.vue`.
- Backend (`Laravel 11`): new proxy endpoints/controllers/services for Google Books, Wger, YouTube (and weather context lookup where applicable).
- Database (`PostgreSQL 16`): reuse/standardize `HABITS.metadata` JSONB contract for external-link fields.
- Security/ops: `.env` keys become required for enabled providers; client no longer calls third-party APIs directly.

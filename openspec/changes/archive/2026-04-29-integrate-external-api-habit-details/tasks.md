## 1. Backend proxy and provider integration

- [x] 1.1 Create Laravel API proxy endpoints for Google Books, Wger, and YouTube search flows.
- [x] 1.2 Implement provider service/adapters that inject API keys from `.env` and normalize response fields (`api_id`, `titol`, `url_imatge`, `tipus_api`).
- [x] 1.3 Add consistent timeout/error handling in proxy controllers so frontend can trigger manual fallback safely.
- [x] 1.4 Add optional weather-context endpoint/service for `Llar` habits with non-blocking behavior.

## 2. Habit metadata contract and persistence

- [x] 2.1 Standardize backend request validation for external metadata payload shape in create/update habit endpoints.
- [x] 2.2 Ensure `HABITS.metadata` JSONB cast/serialization preserves normalized fields for roundtrip create-edit-display flows.
- [x] 2.3 Add server-side guard rails to avoid storing provider secrets or raw unbounded payloads in metadata.
- [x] 2.4 Add tests for create/update habit endpoints covering linked metadata, manual metadata, and empty metadata.

## 3. Frontend create/edit external-link flows

- [x] 3.1 Add category watcher mapping (`Lectura` -> Google Books, `Activitat Física` -> Wger, `Benestar` -> YouTube) in habit create/edit UI logic.
- [x] 3.2 Implement provider search/select UI states (loading, success, empty, error) backed by Laravel proxy endpoints.
- [x] 3.3 Implement manual fallback controls (title/image) that remain available when provider calls fail or user opts out.
- [x] 3.4 Hydrate edit forms from persisted metadata and keep values synchronized when user changes external-link mode.

## 4. Category-change safety rules

- [x] 4.1 Add SweetAlert2 confirmation when category changes on a habit with existing linked metadata.
- [x] 4.2 Clear metadata only on explicit confirmation and preserve metadata when user cancels.
- [x] 4.3 Add frontend/backend tests for category-change confirmation paths and metadata reset behavior.

## 5. Home details modal (`detalls`)

- [x] 5.1 Add secondary `detalls` button to habit cards in `pages/home.vue` with soft secondary styling.
- [x] 5.2 Build details modal sections for difficulty/XP, human-readable repetition text, and manual objective fields.
- [x] 5.3 Render linked metadata preview block (image + title) when metadata exists, and hide it gracefully when absent.
- [x] 5.4 Integrate weather-context block for `Llar` habits as best-effort data (timeout/error safe).

## 6. Verification and rollout readiness

- [x] 6.1 Add integration tests for end-to-end flow: provider-linked habit creation, edit persistence, and home modal display.
- [x] 6.2 Add failure-path tests validating manual fallback when each provider proxy returns error/timeout.
- [x] 6.3 Document required `.env` variables, expected metadata contract, and operational notes for provider quotas/rate limits.
- [x] 6.4 Run regression checks for habit create/edit/home screens to ensure no blocking behavior when external APIs are unavailable.

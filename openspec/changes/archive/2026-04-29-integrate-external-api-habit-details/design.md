## Context

Loopy currently supports habit creation and tracking but does not provide structured links to external real-world content (books, workout routines, videos) in a secure and reusable way.  
The change spans multiple modules (Nuxt frontend, Laravel backend proxy, PostgreSQL metadata contract) and includes UX safeguards (manual fallback and destructive-change warning).

Key constraints:
- External provider keys must never reach the client.
- Main habit card in home must stay lightweight; advanced context should live behind a secondary action (`detalls`).
- API integrations must be optional and non-blocking for core habit usage.
- Existing `HABITS.metadata` (JSONB) is the canonical storage for external-link context.

## Goals / Non-Goals

**Goals:**
- Provide a secure server-side proxy architecture for third-party API calls used by habits.
- Define and stabilize the metadata schema (`api_id`, `titol`, `url_imatge`, `tipus_api`) across create/edit/display flows.
- Ensure habit creation/editing always works with manual fallback when provider APIs are unavailable.
- Add a details modal that surfaces expanded habit context without overloading the home card.
- Protect user data integrity when category changes invalidate external-linked metadata.

**Non-Goals:**
- Building a generic provider plugin framework for arbitrary APIs.
- Persisting full external provider payloads in database (only normalized summary fields are stored).
- Making external APIs mandatory for any habit category.
- Replacing the existing home card primary interaction model.

## Decisions

1. Laravel proxy as the only external API gateway  
   - Decision: Nuxt calls internal Laravel endpoints; Laravel injects provider keys from `.env`, calls providers, returns sanitized payloads.
   - Rationale: Prevent key exposure, centralize timeout/retry/rate-limit behavior, and keep provider-specific logic out of frontend.
   - Alternatives considered:
     - Direct frontend-to-provider calls with restricted keys: rejected due to key leakage risk and weak control over quotas/abuse.
     - Backend-for-frontend in Node service: rejected for unnecessary duplication since Laravel already owns the API layer.

2. JSONB normalized metadata contract in `HABITS.metadata`  
   - Decision: Store only provider-agnostic fields (`api_id`, `titol`, `url_imatge`, `tipus_api`) to support display and editing roundtrip.
   - Rationale: Keeps schema flexible while preserving enough data for offline rendering and fast UI hydration.
   - Alternatives considered:
     - Dedicated relational tables per provider: rejected for high complexity and migration overhead.
     - Raw provider JSON storage: rejected due to payload bloat and provider lock-in.

3. Category-driven provider mapping with optional fallback  
   - Decision: Frontend watcher maps category to provider (`Lectura`→Google Books, `Activitat Física`→Wger, `Benestar`→YouTube); manual title/image remains always available.
   - Rationale: Predictable UX and robust behavior under provider outages.
   - Alternatives considered:
     - Let user choose any provider per habit: rejected to avoid cognitive overload and mismatched contexts.

4. Destructive-change confirmation on category update  
   - Decision: If user changes category and linked metadata exists, show SweetAlert2 confirmation; on accept, clear metadata.
   - Rationale: Prevent silent data mismatch between category semantics and linked resource type.
   - Alternatives considered:
     - Silent metadata reset: rejected due to poor transparency.
     - Keep stale metadata: rejected due to semantic inconsistency.

5. Secondary details modal in home  
   - Decision: Add `detalls` secondary button (soft style) on home habit card to open a modal with difficulty/XP, repetition text, manual goal values, linked resource preview, and weather context for `Llar`.
   - Rationale: Preserves home card scannability while exposing richer context on demand.
   - Alternatives considered:
     - Expanding card inline: rejected due to clutter and variable card heights.

## Risks / Trade-offs

- [External API instability or quotas] -> Mitigation: enforce graceful fallback UI and manual input path; return controlled errors from Laravel proxy.
- [Provider payload format changes] -> Mitigation: isolate transformations in backend provider adapters and normalize outbound DTOs.
- [Inconsistent metadata after edits] -> Mitigation: centralize metadata clear/reset rules on category changes and validate before save.
- [Weather context latency for `Llar`] -> Mitigation: make weather block best-effort/non-blocking and timeout aggressively.
- [More backend surface area] -> Mitigation: keep endpoints scoped to search/select use-cases and reuse shared HTTP client policies.

## Migration Plan

1. Add/update backend endpoints for provider searches and optional weather context, using `.env` keys.
2. Standardize metadata payload contract in request validation and model casting/serialization.
3. Integrate create/edit frontend flows with category watchers, provider search UI, and manual fallback controls.
4. Add category-change confirmation and metadata reset logic in edit flow.
5. Add home `detalls` button and modal UI sections with conditional blocks.
6. Roll out behind a feature flag (or progressive UI exposure) if needed; monitor API error rates and fallback usage.

Rollback strategy:
- Disable new proxy endpoints and hide provider search/`detalls` UI entry points.
- Keep metadata fields backward-compatible (JSONB data can remain without breaking existing habit operations).

## Open Questions

- Should weather context be computed live on modal open, cached server-side, or pre-fetched in habit list responses?
- What timeout/retry policy should each provider use to balance responsiveness vs completeness?
- Do we need per-provider rate limiting by user/session to avoid abuse and quota exhaustion?
- Should manual fallback input remain editable after a provider item is selected, or should it become read-only until unlink?

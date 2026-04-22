## Context

Loopy is a habit-tracking app with three-tier architecture: Nuxt 3 frontend (SPA with Pinia), Laravel 11 backend, and Node.js real-time/AI service. Currently, users register without guided habit creation, leading to low engagement. This design covers the AI-powered onboarding flow using Gemini for personalized habit suggestions.

## Goals / Non-Goals

**Goals:**
- Implement 4-step questionnaire in onboarding.vue with soft design (claymorphism) aesthetic
- Integrate Gemini AI to generate 3-4 personalized habit templates per user
- Assign first mission "Completa el teu primer hàbit!" on home page load
- Enable real-time XP rewards via Socket.io when first habit is completed

**Non-Goals:**
- Full AI chat assistant functionality
- Advanced analytics or habit insights
- Social features or leaderboards

## Decisions

### 1. Onboarding Questionnaire Flow
**Decision**: 4 sequential questions with 25% progress segments.
- Question 1 (Objectiu): Maps to categoria_id (Salut, Productivitat, Ment, Aprenentatge)
- Question 2 (Energia): Maps to "Senyal" time preference (Matí, Migdia, Tarda, Nit)
- Question 3 (Obstacle): Maps to difficulty (facil/mitjana/dificil)
- Question 4 (Temps): Maps to objectiu_vegades (<15m, 30m, 1h, +1h)
**Rationale**: Progressive disclosure reduces cognitive load; each answer directly maps to habit model fields.

### 2. AI Service Architecture
**Decision**: Node.js handler receives questionnaire JSON, calls Gemini API, returns structured habit templates.
- Frontend (Nuxt) → Axios → Node.js endpoint → Gemini API → Parse JSON → Return to Frontend
**Rationale**: Keeps API keys in Node.js layer, not exposed to client. Gemini returns natural language; we parse structured JSON.

### 3. Gemini Prompt Structure
**Decision**: Send structured prompt with questionnaire data, receive JSON array of 3-4 habits.
**Prompt includes**: categoria, senyal, dificultat, temps_disponible
**Expected response**: `[{"titol": "...", "categoria": "...", "senal": "...", "rutina": "...", "recompensa": "..."}]`
**Rationale**: Constrains AI output to parseable format; fields map directly to database schema.

### 4. Middleware Redirect Strategy
**Decision**: Check USUARIS_HABITS table on app load. If empty, redirect to onboarding.
**Implementation**: Laravel middleware or Nuxt route guard checking user.habits?.length
**Rationale**: Simple, reliable; leverages existing auth state.

### 5. Real-time XP System
**Decision**: Socket.io event `mission_completed` triggers XP addition.
- Frontend emits habit completion → Laravel validates → Node.js emits `mission_completed` → Frontend shows XP animation
**Rationale**: Decouples XP award from page reload; provides immediate feedback.

## Risks / Trade-offs

- **[Risk] Gemini API latency** → Mitigation: Show loading skeleton while generating; cache common prompt results
- **[Risk] JSON parse failure from Gemini** → Mitigation: Validate response schema; fallback to default habits if invalid
- **[Risk] User skips onboarding** → Mitigation: Allow skip but prompt again after 3 days; soft redirect not hard block
- **[Risk] Socket.io connection failure** → Mitigation: Fallback to REST API XP update; show offline indicator
- **[Risk] Breaking AuthService** → Mitigation: Wrap in transaction; rollback on failure; test rollback scenario

## Migration Plan

1. **Phase 1**: Create onboarding.vue page skeleton + questionnaire component
2. **Phase 2**: Add Laravel endpoint for user profile creation on registration
3. **Phase 3**: Implement Node.js Gemini handler
4. **Phase 4**: Connect questionnaire → AI → habit selection UI
5. **Phase 5**: Add middleware redirect + first mission logic in home.vue
6. **Phase 6**: Socket.io integration for real-time XP
7. **Phase 7**: QA + rollback plan documented

## Open Questions

- Should onboarding be skippable permanently or just deferred?
- How to handle Gemini API failures - show error or fallback habits?
- Need to define exact XP amounts for first mission completion.

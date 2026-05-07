## Context

Mode Focus introduces a new user-facing workflow in the routine phase that spans navigation, timer state, session persistence, and calendar snapshot reporting. The current architecture already separates frontend orchestration from composables, uses `authFetch` for GET operations to Laravel, and routes CUD operations through Node -> Redis -> Laravel with socket feedback.

This design must preserve existing habit completion and XP behavior while adding:
- Focus-only entry from habit details modal.
- Fixed Pomodoro presets (25/5 and 50/10) with no custom values.
- Session lifecycle events (start, pause/resume, phase switch, manual exit, complete).
- Emotional feedback simulation via SweetAlert2.
- Daily snapshot metadata so calendar can show focus completion context and predominant mode.

Stakeholders are end users (focus productivity), product/game design (habit loop reinforcement), and backend consistency (habit completion registration and calendar reporting).

## Goals / Non-Goals

**Goals:**
- Provide a dedicated Focus Mode screen with clear routine context and low-distraction controls.
- Guarantee only two valid focus presets (25/5, 50/10) and enforce them across UI and persistence.
- Persist focus minutes and mode usage so manual exits still contribute to history and completion checks.
- Mark the habit as completed in `REGISTRE_ACTIVITAT` when cumulative focus reaches objective.
- Surface focus usage information in calendar daily snapshots without changing existing snapshot consumption patterns.
- Keep data flow aligned with platform rules: GET via Laravel API, CUD via Node/Redis/Laravel.

**Non-Goals:**
- No custom Pomodoro durations or user-defined presets.
- No XP rebalance, multiplier, or new reward mechanics for Focus Mode.
- No replacement of existing non-focus completion paths.
- No new admin dashboard behavior in this change.

## Decisions

### 1) Frontend domain split and ownership
**Decision:** Implement Focus Mode with user-domain components and composables:
- New page/view under user flow (for SPA transition from habit details).
- Session/timer logic in a dedicated user composable (or focused extension of existing routine composable).
- Keep page component as orchestrator only; business logic remains in composables/stores.

**Why:** Matches frontend architecture constraints and keeps timer/session logic testable and reusable.

**Alternatives considered:**
- Put all logic in page component: rejected due to tight coupling and poor reuse.
- Add logic into global game store only: rejected because habit focus state should remain isolated from unrelated game state.

### 2) Access guard from habit details modal only
**Decision:** The "Iniciar sesion de focus" entrypoint is added exclusively in `habit-details-modal` flow; access is denied when habit is already completed today.

**Why:** Product requirement and cleaner home UI. Also prevents inconsistent duplicate completions.

**Alternatives considered:**
- Add CTA on home cards: rejected (UI clutter and contradicts requirement).
- Allow read-only entry for completed habits: rejected for MVP because requirement states access is prohibited.

### 3) Deterministic timer state machine
**Decision:** Model timer behavior as explicit states and transitions:
- `idle` -> `running_work` -> `running_rest` -> repeat or finish
- `paused_work` / `paused_rest`
- `exited` / `completed`
Each session stores selected mode (`25_5` or `50_10`) and accumulates focused work minutes only.

**Why:** Reduces edge-case bugs (pause/resume, tab switches, manual exit, boundary seconds) and improves observability.

**Alternatives considered:**
- Free-form boolean flags (`isRunning`, `isBreak`): rejected due to fragile branching and harder recovery.

### 4) Alert strategy with SweetAlert2
**Decision:** Trigger two simulation alerts:
- Distraction alert when document visibility changes away from app tab during active session.
- Reward alert when a work cycle ends and rest begins.

**Why:** Reinforces habit loop with minimal implementation risk using existing modal stack.

**Alternatives considered:**
- Native `alert()`: rejected for poor UX consistency.
- In-page banners only: rejected because requirement explicitly asks for SweetAlert2 simulation.

### 5) Persistence and completion contract
**Decision:** Persist session events through backend CUD path (Node -> Redis -> Laravel) and use Laravel GET endpoints for reads/history checks. Completion logic checks cumulative focused minutes against habit objective and marks `REGISTRE_ACTIVITAT` when reached.

**Why:** Enforces existing backend architecture and keeps completion as canonical backend-side result.

**Alternatives considered:**
- Frontend-only completion marking: rejected (consistency and trust issues).
- Direct CUD from frontend to Laravel: rejected by architecture rule.

### 6) Daily snapshot extension
**Decision:** Extend snapshot payload to include focus metadata:
- `completedWithFocus` (boolean)
- `predominantFocusMode` (`25_5` or `50_10` when applicable)
Expose these in the existing daily calendar view model.

**Why:** Meets reporting requirement while minimizing UI and API churn.

**Alternatives considered:**
- New standalone focus-history endpoint only: rejected since requirement targets daily snapshot visibility.

## Risks / Trade-offs

- [Timer drift between UI and persisted elapsed minutes] -> Mitigation: persist timestamps/checkpoints and derive elapsed time from server-safe deltas where possible.
- [Duplicate completion writes when session ends near threshold] -> Mitigation: idempotent completion command keyed by user/habit/date.
- [False distraction alerts on transient tab events] -> Mitigation: trigger only for active running states and debounce rapid visibility toggles.
- [Music integration failures (search/playback availability)] -> Mitigation: graceful fallback UI with disabled controls and non-blocking timer workflow.
- [Snapshot mode ambiguity with mixed 25/5 and 50/10 use] -> Mitigation: define predominant mode by total focused minutes per mode for the day.


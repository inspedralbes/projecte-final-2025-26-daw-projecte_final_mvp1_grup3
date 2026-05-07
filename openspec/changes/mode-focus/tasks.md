## 1. Routing and Focus Mode entrypoint

- [x] 1.1 Add `Iniciar sessio de focus` action inside habit details modal only (not on home cards).
- [x] 1.2 Block Focus Mode entry when the habit is already completed for current date.
- [x] 1.3 Wire SPA navigation from habit details modal to the dedicated Focus Mode screen with active habit context.

## 2. Focus Mode UI and local state

- [x] 2.1 Build Focus Mode screen shell with habit header, close (`X`) action, central timer, and claymorphism-based layout.
- [x] 2.2 Implement fixed mode selector (`25_5` and `50_10`) and disable timer start until one preset is selected.
- [x] 2.3 Implement play/pause controls and phase-aware timer visuals (work color vs rest color).
- [x] 2.4 Add music section UI with search trigger, previous/play-next controls, and current track title display.

## 3. Session lifecycle and behavior rules

- [x] 3.1 Implement deterministic session state machine (`idle`, running/paused work/rest, `exited`, `completed`) in user composable/store.
- [x] 3.2 Implement work-to-rest automatic transition with SweetAlert2 reward modal.
- [x] 3.3 Implement tab-visibility distraction detection with SweetAlert2 warning during active running states.
- [x] 3.4 Implement manual exit (`X`) flow to persist partial focused minutes and return user to Home.

## 4. Backend contracts and persistence pipeline

- [x] 4.1 Add/extend Node CUD command(s) for focus session events (start/pause/resume/exit/complete) through Redis -> Laravel pipeline.
- [x] 4.2 Implement Laravel-side focus accumulation and idempotent completion registration in `REGISTRE_ACTIVITAT` when objective threshold is reached.
- [x] 4.3 Keep XP reward behavior unchanged for Focus Mode completions (same difficulty mapping as existing completion path).
- [x] 4.4 Ensure GET reads for focus history/session context use Laravel API endpoints via `authFetch`.

## 5. Daily snapshot and calendar integration

- [x] 5.1 Extend snapshot generation to include per-habit focus metadata (`completed_with_focus`, `predominant_focus_mode`).
- [x] 5.2 Update calendar snapshot response mapping so daily view can display Focus Mode completion context and predominant mode.
- [x] 5.3 Define and implement predominant mode calculation by total focused minutes per preset for that day.

## 6. Verification and regression checks

- [x] 6.1 Add frontend tests for modal entry rules, fixed preset requirement, and timer control state transitions.
- [x] 6.2 Add backend tests for focus accumulation, completion idempotency, and unchanged XP rewards.
- [x] 6.3 Add calendar snapshot tests for new focus metadata fields and no-regression behavior when focus data is absent.
- [x] 6.4 Run end-to-end validation of main flow: Home -> details -> Focus Mode -> session complete/exit -> Home -> calendar daily snapshot (e2e test implemented; local run requires backend listening on `localhost:8000`).

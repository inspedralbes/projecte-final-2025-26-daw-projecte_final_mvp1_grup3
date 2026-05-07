## Why

Loopy necesita convertir la fase de rutina en sesiones de trabajo profundo con estructura clara, retroalimentacion emocional y menos friccion de uso. Implementar Mode Focus ahora permite mejorar adherencia diaria sin romper el flujo actual de habitos ni la recompensa de gamificacion.

## What Changes

- Add a dedicated Focus Mode experience for habit execution with fixed Pomodoro presets (25/5 and 50/10) and no custom durations.
- Restrict Focus Mode entry to the habit details modal flow (Home -> details -> "Iniciar sesion de focus") and block access when the habit is already completed for the current date.
- Add in-session behavior for timer lifecycle (work/rest phases, play/pause, manual exit), emotional simulation alerts, and tab-distraction detection using SweetAlert2.
- Add focus session persistence so partial/manual exits save minutes and completed focus goals mark the habit as completed in `REGISTRE_ACTIVITAT`.
- Extend daily calendar snapshot visibility to include focus usage context (focus-completed flag and predominant mode 25/5 or 50/10).
- Add focus page media controls requirements (music search, previous/play-next controls, current track display) as part of the session UI.

## Capabilities

### New Capabilities
- focus-mode-routine: Defines Focus Mode access, fixed mode selection, timer behavior, session controls, emotional feedback simulation, and completion/exit rules for routine execution.

### Modified Capabilities
- habit-details-modal: Adds the exclusive "Iniciar sesion de focus" action in habit details and defines its navigation/eligibility behavior.
- daily-snapshot: Adds focus-session metadata exposure for calendar daily view, including focus completion context and predominant preset used.

## Impact

- Affected frontend areas: user home habit details modal, new Focus Mode page/view, timer and player UI components, navigation guards, and SweetAlert2 interaction flow.
- Affected composables/state: habit execution/session state, focus timer state, and snapshot aggregation inputs for calendar consumption.
- Affected backend contracts: focus-session tracking and habit completion registration path that updates `REGISTRE_ACTIVITAT` while preserving current XP rules.
- Affected integrations: YouTube-based music search/playback controls in Focus Mode and existing calendar snapshot rendering.

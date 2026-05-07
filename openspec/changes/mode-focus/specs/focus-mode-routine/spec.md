## ADDED Requirements

### Requirement: Focus Mode access and fixed presets
The system SHALL provide a Focus Mode session entry only from the habit details modal and SHALL require the user to select exactly one preset mode (`25_5` or `50_10`) before starting the timer. The system MUST deny Focus Mode access when the habit is already completed for the current date.

#### Scenario: Start Focus Mode from habit details modal
- **WHEN** a user opens a habit details modal for a habit not completed today and presses `Iniciar sessio de focus`
- **THEN** the system navigates to the dedicated Focus Mode screen for that habit in the SPA flow

#### Scenario: Preset selection is mandatory
- **WHEN** the user is on Focus Mode and has not selected either `25_5` or `50_10`
- **THEN** the system SHALL keep the timer start action disabled

#### Scenario: Access denied for completed habit
- **WHEN** the user attempts to open Focus Mode for a habit already marked completed for today
- **THEN** the system SHALL block entry and keep the user in the originating context without creating a new focus session

### Requirement: Focus session lifecycle and timer behavior
The system SHALL run Focus Mode using alternating work/rest phases according to the selected preset, with explicit play/pause control and manual exit, and SHALL accumulate only work-phase minutes as focus minutes.

#### Scenario: Work to rest transition reward
- **WHEN** a running work phase reaches zero in Focus Mode
- **THEN** the system starts the corresponding rest phase and shows a SweetAlert2 reward message indicating the pet celebrates the effort

#### Scenario: Pause and resume preserve remaining time
- **WHEN** the user pauses and later resumes an active work or rest phase
- **THEN** the system continues from the previously remaining countdown time for that phase

#### Scenario: Manual exit stores partial progress
- **WHEN** the user exits Focus Mode with the `X` button before completing the habit objective
- **THEN** the system persists the accumulated focus work minutes for history and returns the user to Home

### Requirement: Focus distraction feedback
The system SHALL trigger a SweetAlert2 distraction alert when the app tab loses visibility during an active Focus Mode session.

#### Scenario: Tab switch triggers distraction alert
- **WHEN** document visibility changes away from the app while a focus session is actively running
- **THEN** the system shows a SweetAlert2 modal indicating the pet is sad due to distraction

### Requirement: Focus completion registration
The system SHALL mark the habit as completed in `REGISTRE_ACTIVITAT` once cumulative focus work minutes for the session day satisfy the habit objective, without altering the existing XP rewards by difficulty.

#### Scenario: Objective reached through focus minutes
- **WHEN** accumulated focus work minutes for the active habit reach the configured objective threshold
- **THEN** the system records the habit as completed for that date in `REGISTRE_ACTIVITAT` using the existing completion pipeline

#### Scenario: XP remains standard on focus completion
- **WHEN** a habit completion is achieved through Focus Mode
- **THEN** the system awards the same standard XP for that habit difficulty as non-focus completion paths

### Requirement: Focus music controls
The system SHALL provide integrated music controls inside Focus Mode including search entry, previous/play-next controls, and currently playing track title display.

#### Scenario: Music controls visible during session
- **WHEN** the Focus Mode screen is rendered
- **THEN** the system shows a music section with search trigger, track controls, and current track name placeholder

## MODIFIED Requirements

### Requirement: Secondary details action on home habit card
The system SHALL provide a secondary `detalls` action on each home habit card that opens a modal with expanded habit information and SHALL expose the Focus Mode entry action only inside that modal.

#### Scenario: Open details modal
- **WHEN** a user presses the `detalls` button on a habit card
- **THEN** the system opens a modal for that habit without navigating away from home

#### Scenario: Focus entry appears only in details modal
- **WHEN** the details modal for a habit not completed today is rendered
- **THEN** the system shows an `Iniciar sessio de focus` action in the modal and does not show that action on the home habit card itself

### Requirement: Structured details modal content
The details modal SHALL display the habit's difficulty and XP reward, readable repetition text derived from weekly days, manual goal unit/value, and linked external metadata preview when available, and SHALL include Focus Mode eligibility state for the current day.

#### Scenario: Render full details for linked habit
- **WHEN** the selected habit has metadata and configured objective fields
- **THEN** the modal displays difficulty/XP, human-readable repetition, objective unit/value, and resource image/title

#### Scenario: Render details when metadata is missing
- **WHEN** the selected habit has no external metadata
- **THEN** the modal still displays non-API habit details and omits the metadata block gracefully

#### Scenario: Focus entry disabled when habit already completed today
- **WHEN** the selected habit is already completed for the current date
- **THEN** the modal SHALL not allow starting Focus Mode for that habit

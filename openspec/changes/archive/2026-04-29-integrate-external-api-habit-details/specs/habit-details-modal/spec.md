## ADDED Requirements

### Requirement: Secondary details action on home habit card
The system SHALL provide a secondary `detalls` action on each home habit card that opens a modal with expanded habit information.

#### Scenario: Open details modal
- **WHEN** a user presses the `detalls` button on a habit card
- **THEN** the system opens a modal for that habit without navigating away from home

### Requirement: Structured details modal content
The details modal SHALL display the habit's difficulty and XP reward, readable repetition text derived from weekly days, manual goal unit/value, and linked external metadata preview when available.

#### Scenario: Render full details for linked habit
- **WHEN** the selected habit has metadata and configured objective fields
- **THEN** the modal displays difficulty/XP, human-readable repetition, objective unit/value, and resource image/title

#### Scenario: Render details when metadata is missing
- **WHEN** the selected habit has no external metadata
- **THEN** the modal still displays non-API habit details and omits the metadata block gracefully

### Requirement: Household weather context in modal
For habits in category `Llar`, the modal SHALL include a weather-context block that indicates whether current conditions support the habit.

#### Scenario: Display weather suitability for household habit
- **WHEN** a `Llar` habit details modal is opened and weather context is available
- **THEN** the modal shows whether current weather conditions allow or discourage completing the habit

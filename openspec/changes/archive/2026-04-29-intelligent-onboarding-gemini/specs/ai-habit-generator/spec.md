## ADDED Requirements

### Requirement: AI service receives questionnaire data
The Node.js backend SHALL expose an endpoint that accepts questionnaire responses (categoria, senyal, dificultat, temps) and returns personalized habit templates.

#### Scenario: Valid questionnaire data received
- **WHEN** POST request is sent to /api/onboarding/generate with {categoria, senyal, dificultat, temps}
- **THEN** service validates all required fields are present
- **AND** service calls Gemini API with structured prompt

### Requirement: Gemini generates habit suggestions
The system SHALL send a structured prompt to Gemini containing the user's questionnaire responses and receive 3-4 personalized habit suggestions in JSON format.

#### Scenario: Gemini returns valid JSON
- **WHEN** Gemini API responds with valid JSON array
- **THEN** system parses and returns habit templates with fields: titol, categoria, senyal, rutina, recompensa

#### Scenario: Gemini returns invalid JSON
- **WHEN** Gemini API response cannot be parsed as valid JSON
- **THEN** system returns fallback default habits
- **AND** logs error for monitoring

### Requirement: Habit templates contain required fields
Each generated habit template SHALL contain: titol (string), categoria (string), senyal (string), rutina (string), recompensa (string).

#### Scenario: All required fields present
- **WHEN** habit template is parsed
- **THEN** template contains non-empty titol, categoria, senyal, rutina, recompensa
- **AND** fields are strings with reasonable length

### Requirement: Frontend displays habit selection UI
The system SHALL display generated habits as selectable cards where users can mark/unmark preferences.

#### Scenario: Habits displayed as cards
- **WHEN** AI returns habit templates
- **THEN** frontend displays each as a card with title, category preview, and select checkbox

#### Scenario: User selects habit
- **WHEN** user clicks/taps on a habit card
- **THEN** habit is marked as selected (visual toggle)
- **AND** selection state is stored

#### Scenario: User deselects habit
- **WHEN** user clicks/taps selected habit card
- **THEN** habit is deselected
- **AND** selection state is updated

### Requirement: Selected habits are persisted
The system SHALL send selected habit IDs to Laravel backend for persistence in USUARIS_HABITS and HABITS tables.

#### Scenario: User confirms selection
- **WHEN** user clicks "Confirmar" button
- **THEN** system sends POST to Laravel /api/habits/assign with selected habits
- **AND** Laravel creates records in HABITS table
- **AND** Laravel creates records in USUARIS_HABITS table linking user to habits
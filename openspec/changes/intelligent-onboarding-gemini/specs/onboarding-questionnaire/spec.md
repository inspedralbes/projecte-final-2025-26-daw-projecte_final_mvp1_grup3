## ADDED Requirements

### Requirement: User sees progress bar during onboarding
The system SHALL display a segmented progress bar showing current step out of 4 (25% per step) with soft design claymorphism aesthetic.

#### Scenario: Progress bar displays correct percentage
- **WHEN** user is on question 1
- **THEN** progress bar shows 25%
- **WHEN** user is on question 2
- **THEN** progress bar shows 50%
- **WHEN** user is on question 3
- **THEN** progress bar shows 75%
- **WHEN** user is on question 4
- **THEN** progress bar shows 100%

### Requirement: User completes question 1 about goal
The system SHALL present Question 1 asking about the user's primary goal: Salut, Productivitat, Ment, or Aprenentatge. The selected value MUST map to categoria_id.

#### Scenario: User selects Salut
- **WHEN** user selects "Salut" option
- **THEN** categoria_id is set to the corresponding ID for health category
- **AND** user can proceed to question 2

#### Scenario: User selects Productivitat
- **WHEN** user selects "Productivitat" option
- **THEN** categoria_id is set to the corresponding ID for productivity category

#### Scenario: User selects Ment
- **WHEN** user selects "Ment" option
- **THEN** categoria_id is set to the corresponding ID for mental category

#### Scenario: User selects Aprenentatge
- **WHEN** user selects "Aprenentatge" option
- **THEN** categoria_id is set to the corresponding ID for learning category

### Requirement: User completes question 2 about energy time
The system SHALL present Question 2 asking about the user's peak energy time: Matí, Migdia, Tarda, or Nit. The selected value MUST map to the "Senyal" field in the Habit model.

#### Scenario: User selects morning energy
- **WHEN** user selects "Matí" option
- **THEN** senyal is set to "morning"

#### Scenario: User selects afternoon energy
- **WHEN** user selects "Migdia" option
- **THEN** senyal is set to "afternoon"

#### Scenario: User selects evening energy
- **WHEN** user selects "Tarda" option
- **THEN** senyal is set to "evening"

#### Scenario: User selects night energy
- **WHEN** user selects "Nit" option
- **THEN** senyal is set to "night"

### Requirement: User completes question 3 about obstacle
The system SHALL present Question 3 asking about the user's main obstacle: Estrès, Temps, Memòria, or Mandra. The selected value MUST adjust the habit difficulty level.

#### Scenario: User selects Estrès
- **WHEN** user selects "Estrès" option
- **THEN** difficulty is set to "facil"

#### Scenario: User selects Temps
- **WHEN** user selects "Temps" option
- **THEN** difficulty is set to "mitjana"

#### Scenario: User selects Memòria
- **WHEN** user selects "Memòria" option
- **THEN** difficulty is set to "mitjana"

#### Scenario: User selects Mandra
- **WHEN** user selects "Mandra" option
- **THEN** difficulty is set to "dificil"

### Requirement: User completes question 4 about available time
The system SHALL present Question 4 asking about available time per day: <15m, 30m, 1h, or +1h. The selected value MUST map to objectiu_vegades (target frequency).

#### Scenario: User selects less than 15 minutes
- **WHEN** user selects "<15m" option
- **THEN** objectiu_vegades is set to 1

#### Scenario: User selects 30 minutes
- **WHEN** user selects "30m" option
- **THEN** objectiu_vegades is set to 1

#### Scenario: User selects 1 hour
- **WHEN** user selects "1h" option
- **THEN** objectiu_vegades is set to 1

#### Scenario: User selects more than 1 hour
- **WHEN** user selects "+1h" option
- **THEN** objectiu_vegades is set to 2

### Requirement: Questionnaire data is sent to AI service
The system SHALL collect all four questionnaire responses and send them to the AI service endpoint for habit generation.

#### Scenario: All questions answered
- **WHEN** user completes question 4
- **THEN** system sends POST request to AI service with {categoria, senyal, dificultat, temps}
- **AND** system displays loading state while waiting for response

## ADDED Requirements

### Requirement: First mission is assigned on home page load
The system SHALL automatically assign mission "Completa el teu primer hàbit!" to the user's missio_diaria_id field when they first load the home page after onboarding.

#### Scenario: User with onboarding completed enters home
- **WHEN** user navigates to home page
- **AND** user has completed onboarding (has habits in USUARIS_HABITS)
- **THEN** missio_diaria_id is set to first mission ID (if not already set)

### Requirement: Mascota welcomes user on first visit
The system SHALL trigger the interactive pet's welcome dialog when user enters home page after completing onboarding.

#### Scenario: First home page visit after onboarding
- **WHEN** user loads home page for first time after onboarding
- **THEN** mascot dialog shows welcome message
- **AND** dialog is dismissible

### Requirement: Real-time XP reward on mission completion
The system SHALL award XP immediately via Socket.io when user completes their first onboarding habit.

#### Scenario: First habit marked complete
- **WHEN** user completes first onboarding habit
- **THEN** Socket.io event 'mission_completed' is emitted
- **AND** XP is added to user's total (immediate, no page reload)

#### Scenario: XP animation displays
- **WHEN** Socket.io receives 'mission_completed'
- **THEN** frontend displays XP +N animation
- **AND** user sees visual feedback of earned XP

### Requirement: User with no habits redirected to onboarding
The system SHALL check if user has any habits in USUARIS_HABITS on app load and redirect to onboarding if empty.

#### Scenario: User without habits accesses protected route
- **WHEN** user tries to access home or any protected route
- **AND** USUARIS_HABITS query returns empty
- **THEN** user is redirected to /onboarding

### Requirement: Middleware checks habit existence
The middleware or route guard SHALL query USUARIS_HABITS to determine if onboarding is required.

#### Scenario: Check returns records
- **WHEN** middleware queries USUARIS_HABITS for user_id
- **AND** records exist
- **THEN** user can access requested route

#### Scenario: Check returns empty
- **WHEN** middleware queries USUARIS_HABITS for user_id
- **AND** no records exist
- **THEN** redirect to /onboarding
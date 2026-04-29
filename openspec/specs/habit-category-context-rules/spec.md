# Spec: Habit Category Context Rules

## Purpose

Define the business rules that govern how a habit's category determines provider integration, metadata invalidation on category change, and whether external API linkage is mandatory.

## Requirements

### Requirement: Category-driven provider selection
The system SHALL map habit categories to predefined providers: `Lectura` to Google Books, `Activitat Física` to Wger, and `Benestar` to YouTube.

#### Scenario: Activate provider search by category
- **WHEN** a user selects one of the mapped categories during habit creation or edition
- **THEN** the system enables the corresponding provider search flow for that category

### Requirement: Safe metadata invalidation on category change
If a habit with linked metadata changes to a different category, the system SHALL warn the user that linked context will be removed and MUST clear metadata only after explicit confirmation.

#### Scenario: Confirm metadata reset
- **WHEN** a user changes category on a habit that already contains external metadata and accepts the warning dialog
- **THEN** the system clears the metadata fields before saving the updated habit

#### Scenario: Cancel metadata reset
- **WHEN** a user changes category on a linked habit and rejects the warning dialog
- **THEN** the system preserves current metadata and does not apply the destructive category change

### Requirement: Optional API linkage by business rule
The system SHALL treat provider linkage as optional and MUST allow users to complete habit create/edit flows without selecting any external resource.

#### Scenario: Save habit without provider link
- **WHEN** a user completes the form without selecting provider data
- **THEN** the habit is saved successfully using only manual fields

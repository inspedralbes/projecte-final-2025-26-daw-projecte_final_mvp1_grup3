# Spec: Habit External Metadata Linking

## Purpose

Allow users to optionally associate a habit with an external resource (e.g. a book, exercise, or video) and persist normalised metadata in the habit record. Provides a manual fallback path when external providers are unavailable.

## Requirements

### Requirement: Habit external metadata persistence
The system SHALL allow users to optionally link a habit to an external resource and persist normalized metadata in `HABITS.metadata` using the fields `api_id`, `titol`, `url_imatge`, and `tipus_api`.

#### Scenario: Persist linked resource metadata
- **WHEN** a user selects an external resource during habit creation or edition
- **THEN** the system saves the selected resource as normalized metadata with all required fields

### Requirement: Manual fallback for external link data
The system SHALL provide a manual fallback path for external-link context so users can set title and image even when external providers are unavailable or not desired.

#### Scenario: Fallback after provider failure
- **WHEN** a provider request fails during resource search or selection
- **THEN** the system keeps the habit flow available and allows manual title and image input

#### Scenario: User chooses manual mode
- **WHEN** a user opts out of provider-based selection
- **THEN** the system accepts manually entered title and image as valid external-link metadata context

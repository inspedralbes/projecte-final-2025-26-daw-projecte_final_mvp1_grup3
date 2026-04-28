## ADDED Requirements

### Requirement: User can report inappropriate content
The system SHALL allow users to report posts or comments they believe violate community guidelines.

#### Scenario: User reports a post
- **WHEN** user clicks "Report" on a post and selects a reason
- **THEN** a report record is created

#### Scenario: User reports a comment
- **WHEN** user clicks "Report" on a comment and selects a reason
- **THEN** a report record is created

### Requirement: Moderators can review reports
The system SHALL provide moderators with a list of pending reports to review.

#### Scenario: Moderator views pending reports
- **WHEN** moderator navigates to admin/reports
- **THEN** the system displays all pending reports

#### Scenario: Moderator resolves a report
- **WHEN** moderator reviews a report and takes action
- **THEN** the report is marked as resolved

### Requirement: Moderation audit trail
The system SHALL log all moderation actions for audit purposes.

#### Scenario: Moderator action is logged
- **WHEN** moderator takes action on a report
- **THEN** the action is recorded in ADMIN_LOGS with JSONB details
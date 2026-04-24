## ADDED Requirements

### Requirement: User receives real-time notifications for social interactions
The system SHALL notify users in real-time when someone interacts with their content via WebSockets.

#### Scenario: User receives notification for new comment on their post
- **WHEN** another user comments on the user's post
- **THEN** the user receives a WebSocket notification

#### Scenario: User receives notification for like on their post
- **WHEN** another user likes the user's post
- **THEN** the user receives a WebSocket notification

#### Scenario: User receives notification for reply to their comment
- **WHEN** another user replies to the user's comment
- **THEN** the user receives a WebSocket notification

### Requirement: Real-time feed updates
The system SHALL update the feed in real-time without page refresh.

#### Scenario: New post appears in real-time
- **WHEN** a user creates a new post
- **THEN** all connected users see the new post appear in their feed

#### Scenario: Like counter updates in real-time
- **WHEN** a user likes a post
- **THEN** all users viewing that post see the counter update
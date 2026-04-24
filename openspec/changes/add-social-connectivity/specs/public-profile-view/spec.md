## ADDED Requirements

### Requirement: User can view public profile
Any authenticated user SHALL be able to view another user's public profile with pet, level, XP, achievements, and streak.

#### Scenario: View public profile successfully
- **WHEN** user sends GET /api/users/{id}/profile
- **THEN** system returns public profile data including pet, level, total_xp, achievements, streak

#### Scenario: View profile of non-existent user
- **WHEN** user sends GET /api/users/{invalid-id}/profile
- **THEN** system returns 404 error

#### Scenario: View own profile as public
- **WHEN** user sends GET /api/users/self/profile
- **THEN** system returns same data as public view (no private data exposed)
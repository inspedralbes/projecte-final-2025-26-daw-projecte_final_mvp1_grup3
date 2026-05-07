## ADDED Requirements

### Requirement: User can send friend request
A user SHALL be able to send a friend request to another user by addressee_id.

#### Scenario: Send friend request successfully
- **WHEN** user sends POST /api/friends/request with addressee_id
- **THEN** system creates FRIENDSHIPS record with status 'pending' and returns 201

#### Scenario: Send friend request to non-existent user
- **WHEN** user sends POST /api/friends/request with invalid addressee_id
- **THEN** system returns 404 error

#### Scenario: Send friend request to self
- **WHEN** user sends POST /api/friends/request with own addressee_id
- **THEN** system returns 400 error

#### Scenario: Send friend request when one already exists
- **WHEN** user sends friend request to user they already have a relationship with
- **THEN** system returns 409 error

### Requirement: User can accept friend request
A user SHALL be able to accept a pending friend request sent to them.

#### Scenario: Accept friend request successfully
- **WHEN** user sends PUT /api/friends/accept/{id}
- **THEN** system updates FRIENDSHIPS status to 'accepted' and returns 200

#### Scenario: Accept friend request not sent to current user
- **WHEN** user tries to accept a request they didn't receive
- **THEN** system returns 403 error

#### Scenario: Accept already accepted request
- **WHEN** user tries to accept an already accepted request
- **THEN** system returns 400 error

### Requirement: User can reject friend request
A user SHALL be able to reject a pending friend request sent to them.

#### Scenario: Reject friend request successfully
- **WHEN** user sends PUT /api/friends/reject/{id}
- **THEN** system updates FRIENDSHIPS status to 'rejected' and returns 200

#### Scenario: Reject friend request not sent to current user
- **WHEN** user tries to reject a request they didn't receive
- **THEN** system returns 403 error

### Requirement: User can view friends list
A user SHALL be able to view their list of accepted friends with online status.

#### Scenario: View friends list
- **WHEN** user sends GET /api/friends
- **THEN** system returns paginated list of accepted friends with user data and online status

#### Scenario: View empty friends list
- **WHEN** user with no friends sends GET /api/friends
- **THEN** system returns empty array

### Requirement: User can view pending requests
A user SHALL be able to view incoming pending friend requests.

#### Scenario: View pending requests
- **WHEN** user sends GET /api/friends/pending
- **THEN** system returns list of pending requests sent to current user
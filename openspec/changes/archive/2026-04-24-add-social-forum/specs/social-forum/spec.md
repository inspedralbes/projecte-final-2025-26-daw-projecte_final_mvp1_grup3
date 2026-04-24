## ADDED Requirements

### Requirement: User can create a social post
The system SHALL allow users to create posts in the social feed that can optionally link to their own habits or templates.

#### Scenario: User creates a text-only post
- **WHEN** user fills in the post content and clicks "Publish"
- **THEN** the post is saved to SOCIAL_POSTS and appears in the feed

#### Scenario: User creates a post linked to a habit
- **WHEN** user selects one of their habits and clicks "Publish"
- **THEN** the post is saved with habit_id populated and displays a preview card

#### Scenario: User creates a post linked to a template
- **WHEN** user selects one of their templates and clicks "Publish"
- **THEN** the post is saved with plantilla_id populated and displays a preview card

### Requirement: User can view the social feed
The system SHALL display a paginated feed of social posts ordered by most recent.

#### Scenario: User loads the social page
- **WHEN** user navigates to /social
- **THEN** the system fetches and displays posts from SOCIAL_POSTS ordered by created_at DESC

#### Scenario: User scrolls to load more posts
- **WHEN** user scrolls to the bottom of the feed
- **THEN** the system fetches the next page of posts

### Requirement: User can comment on posts and comments
The system SHALL allow users to comment on posts and on other comments up to 3 levels deep.

#### Scenario: User comments on a post
- **WHEN** user submits a comment on a post
- **THEN** the comment is saved with depth_level = 0

#### Scenario: User replies to a level 0 comment
- **WHEN** user submits a reply to a depth 0 comment
- **THEN** the reply is saved with depth_level = 1

#### Scenario: User replies to a level 2 comment
- **WHEN** user attempts to reply to a depth 2 comment
- **THEN** the system returns a validation error

### Requirement: User can like posts and comments
The system SHALL allow users to like posts and comments.

#### Scenario: User likes a post
- **WHEN** user clicks the like button on a post
- **THEN** a record is created in SOCIAL_LIKES and the counter increments

#### Scenario: User unlikes a post
- **WHEN** user clicks the like button on a liked post
- **THEN** the like record is deleted and the counter decrements

### Requirement: User can import habits from shared posts
The system SHALL allow users to import habits from posts that have linked habits, with configurable days of the week.

#### Scenario: User imports a habit with custom days
- **WHEN** user clicks "Import" on a habit post, selects days of the week, and confirms
- **THEN** a new habit is created in the user's habit list

#### Scenario: User attempts import exceeding 20 habit limit
- **WHEN** user has 20 active habits and tries to import another
- **THEN** the system returns a validation error

### Requirement: User can import templates from shared posts
The system SHALL allow users to import templates from posts that have linked templates, with manual habit selection.

#### Scenario: User imports a template with habit selection
- **WHEN** user clicks "Import" on a template post, selects which habits to import, and confirms
- **THEN** a new template is created with selected habits

### Requirement: User can delete their own posts
The system SHALL allow post authors to delete their own posts.

#### Scenario: User deletes their post
- **WHEN** user clicks "Delete" on their own post
- **THEN** the post is removed from the feed (soft delete)
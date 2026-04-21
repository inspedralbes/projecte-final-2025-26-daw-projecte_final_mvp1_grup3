## 1. Database SQL

- [x] 1.1 Add SOCIAL_POSTS table (user_id, content, habit_id, plantilla_id, created_at, updated_at, deleted_at) to init.sql
- [x] 1.2 Add SOCIAL_COMMENTS table (post_id, user_id, parent_id, content, depth_level, created_at, updated_at) to init.sql
- [x] 1.3 Add SOCIAL_LIKES table (user_id, likeable_id, likeable_type, created_at) to init.sql
- [x] 1.4 Add foreign keys and indexes to init.sql

## 2. API - Posts

- [x] 2.1 Create SocialPostController with CRUD methods
- [x] 2.2 Implement store() - create post with validation
- [x] 2.3 Implement index() - paginated feed (created_at DESC)
- [x] 2.4 Implement show() - single post with relations
- [x] 2.5 Implement destroy() - soft delete for own posts
- [x] 2.6 Add routes to api.php (/api/social/posts)

## 3. API - Comments

- [x] 3.1 Create SocialCommentController
- [x] 3.2 Implement store() - validation for depth_level < 3
- [x] 3.3 Implement index() - comments by post_id
- [x] 3.4 Implement destroy() - delete own comment
- [x] 3.5 Add routes to api.php (/api/social/comments)

## 4. API - Likes

- [x] 4.1 Create SocialLikeController
- [x] 4.2 Implement store() - toggle like (create/delete)
- [x] 4.3 Implement checkLiked() - return if current user liked
- [x] 4.4 Add routes to api.php (/api/social/likes)

## 5. API - Imports

- [x] 5.1 Create ImportController
- [x] 5.2 Implement importHabit() - from post to user habits (with dies_setmana)
- [x] 5.3 Implement importPlantilla() - from post to user templates
- [x] 5.4 Validate 20-habit limit before import
- [x] 5.5 Add routes to api.php (/api/social/import)

## 6. API - Moderation

- [x] 6.1 Extend ReportController for social reports
- [x] 6.2 Add report routes for posts and comments
- [x] 6.3 Implement admin/reports endpoint
- [x] 6.4 Log moderation actions to ADMIN_LOGS

## 7. Backend - Socket Events

- [x] 7.1 Create SocialHandler in backend-node/src/handlers/user/
- [x] 7.2 Handle social_comment event (emit to post author)
- [x] 7.3 Handle social_like event (emit to post author)
- [x] 7.4 Broadcast new_post to all connected users
- [x] 7.5 Broadcast like_update to post room

## 8. Frontend - Stores

- [x] 8.1 Create socialStore (frontend/stores/useSocialStore.js)
- [x] 8.2 Implement fetchFeed() action
- [x] 8.3 Implement createPost(), addComment(), toggleLike()
- [x] 8.4 Implement importHabit(), importPlantilla()
- [x] 8.5 Handle WebSocket events in store

## 9. Frontend - Components

- [x] 9.1 Create SocialFeed page (frontend/pages/social.vue)
- [x] 9.2 Create PostCard component (frontend/components/user/social/PostCard.vue)
- [x] 9.3 Create LikeButton component (frontend/components/user/social/LikeButton.vue)
- [x] 9.4 Create CommentList component (frontend/components/user/social/CommentList.vue)
- [x] 9.5 Create CommentItem component (frontend/components/user/social/CommentItem.vue)
- [x] 9.6 Create CommentForm component (frontend/components/user/social/CommentForm.vue)
- [x] 9.7 Create ImportWizard component (frontend/components/user/social/ImportWizard.vue)

## 10. Frontend - Routes

- [x] 10.1 Add /social route in router
- [x] 10.2 Protect route with auth middleware
- [x] 10.3 Add moderation route /admin/reports (admin only)

## 11. Integration & Testing

- [ ] 11.1 Test full flow: create post, comment, like, unlike
- [ ] 11.2 Test import habit with custom days
- [ ] 11.3 Test import plantilla with habit selection
- [ ] 11.4 Test WebSocket notifications
- [ ] 11.5 Test report and moderation flow
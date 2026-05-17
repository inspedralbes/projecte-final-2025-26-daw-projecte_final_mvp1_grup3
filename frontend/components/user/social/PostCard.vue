<template>
  <div
    class="post-expandable"
    :class="showComments ? 'post-expandable--active' : ''"
  >
    <button type="button" class="post-card" @click="toggleExpand">
      <div class="post-card__avatar">
        <div class="post-card__avatar-ring" :style="avatarBackgroundStyle">
          <div class="post-card__avatar-inner">
            <img
              v-if="monsterImage"
              :src="monsterImage"
              alt="Monstre del perfil"
              class="post-card__avatar-img"
              :style="monsterStyle"
              decoding="async"
              draggable="false"
            />
          </div>
        </div>
      </div>
      <div class="post-card__body">
        <p class="post-card__author">{{ post.user?.nom }}</p>
        <p class="post-card__content">{{ post.content }}</p>
      </div>
      <span class="post-card__dots" aria-hidden="true">
        <span></span><span></span><span></span>
      </span>
    </button>

    <div v-if="showComments" class="post-expand-inline">
      <div class="post-expand-top">
        <button class="post-expand-close" type="button" @click="showComments = false">
          <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M11.2917 6.54167L6.54167 11.2917M6.54167 6.54167L11.2917 11.2917M16.8333 8.91667C16.8333 13.2889 13.2889 16.8333 8.91667 16.8333C4.54441 16.8333 1 13.2889 1 8.91667C1 4.54441 4.54441 1 8.91667 1C13.2889 1 16.8333 4.54441 16.8333 8.91667Z" stroke="#FAF9F9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>
        <span class="post-expand-date">{{ formatDate(post.created_at) }}</span>
        <button v-if="isOwner" class="post-expand-delete" type="button" @click="deletePost">
          <svg width="15" height="15" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M2 4h12M5.333 4V2.667a1.333 1.333 0 011.334-1.334h2.666a1.333 1.333 0 011.334 1.334V4m2 0v9.333a1.333 1.333 0 01-1.334 1.334H4.667a1.333 1.333 0 01-1.334-1.334V4h9.334z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <span>{{ $t('social.delete') }}</span>
        </button>
      </div>

      <div class="post-expand-panel">
        <div class="post-expand-header">
          <div class="post-expand-avatar-row">
            <button type="button" class="post-expand-avatar-btn" @click="openProfile">
              <div class="post-card__avatar-ring post-card__avatar-ring--lg" :style="avatarBackgroundStyle">
                <div class="post-card__avatar-inner">
                  <img
                    v-if="monsterImage"
                    :src="monsterImage"
                    alt="Monstre del perfil"
                    class="post-card__avatar-img"
                    :style="monsterStyle"
                    decoding="async"
                    draggable="false"
                  />
                </div>
              </div>
            </button>
            <div>
              <p class="post-expand-author">
                <span class="post-expand-author__name" @click="openProfile">{{ post.user?.nom }}</span>
              </p>
              <p class="post-expand-time">{{ formatDate(post.created_at) }}</p>
            </div>
          </div>
        </div>

        <p class="post-expand-content">{{ post.content }}</p>

        <div v-if="post.habit || post.plantilla" class="post-expand-attachment">
          <div v-if="post.habit" class="post-expand-attachment__item">
            <span class="post-expand-attachment__badge post-expand-attachment__badge--habit">{{ $t('social.habit') }}</span>
            <span class="post-expand-attachment__name">{{ post.habit.titol }}</span>
          </div>
          <div v-if="post.plantilla" class="post-expand-attachment__item">
            <span class="post-expand-attachment__badge post-expand-attachment__badge--template">{{ $t('social.template') }}</span>
            <span class="post-expand-attachment__name">{{ post.plantilla.titol }}</span>
          </div>
          <button type="button" class="post-expand-import-btn" @click="$emit('import', post)">
            {{ $t('social.import') }}
          </button>
        </div>

        <div class="post-expand-actions">
          <UserSocialLikeButton
            :content-id="post.id"
            content-type="post"
            :initial-liked="post.liked_by_current_user"
            :initial-count="post.likes_count || 0"
          />
          <span class="post-expand-comment-count">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            {{ commentsCount }}
          </span>
        </div>

        <div class="post-expand-comments-section">
          <UserSocialCommentForm :post-id="post.id" />
          <UserSocialCommentList :post-id="post.id" :initial-comments="post.comments || []" />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { useSocialStore } from "~/stores/useSocialStore.js";
import { useAuthStore } from "~/stores/useAuthStore.js";
import { getMonsterImageFromUser } from "~/utils/monsterImage.js";
import bosqueImg from "~/assets/img/Bosque.png";

export default {
  name: "PostCard",
  props: {
    post: { type: Object, required: true }
  },
  emits: ["import", "deleted"],
  data: function () {
    return {
      showComments: false,
      commentsCount: this.post.comments_count || 0
    };
  },
  computed: {
    isOwner: function () {
      var authStore = useAuthStore();
      return this.post.user_id === authStore.user?.id;
    },
    monsterImage: function () {
      return getMonsterImageFromUser(this.post.user);
    },
    monsterStyle: function () {
      return {
        transform: "scale(1.2) translateY(10%)",
        filter: "drop-shadow(0 2px 4px rgba(0,0,0,0.2))"
      };
    },
    avatarBackgroundStyle: function () {
      return {
        backgroundImage: "url(" + bosqueImg + ")",
        backgroundSize: "cover",
        backgroundPosition: "center",
      };
    }
  },
  watch: {
    'post.comments_count': function (newVal) {
      this.commentsCount = newVal || 0;
    }
  },
  methods: {
    toggleExpand: function () {
      this.showComments = !this.showComments;
    },
    openProfile: function () {
      if (this.post.user_id) {
        var authStore = useAuthStore();
        if (this.post.user_id === authStore.user?.id) {
          this.$router.push('/perfil');
        } else {
          this.$router.push('/user/' + this.post.user_id);
        }
      }
    },
    formatDate: function (date) {
      if (!date) return "";
      var d = new Date(date);
      var now = new Date();
      var diffMs = now - d;
      var diffMins = Math.floor(diffMs / 60000);
      var diffHours = Math.floor(diffMs / 3600000);
      var diffDays = Math.floor(diffMs / 86400000);

      if (diffMins < 1) return this.$t('social.just_now');
      if (diffMins < 60) return diffMins + "m";
      if (diffHours < 24) return diffHours + "h";
      if (diffDays < 7) return diffDays + "d";
      return d.toLocaleDateString();
    },
    deletePost: async function () {
      if (!confirm(this.$t('social.confirm_delete'))) return;

      var socialStore = useSocialStore();
      var result = await socialStore.deletePost(this.post.id);

      if (result) {
        this.$emit("deleted", this.post.id);
      }
    }
  }
};
</script>

<style scoped>
.post-expandable {
  overflow: hidden;
  border-radius: 10px;
  max-height: 100px;
  transition: max-height 0.28s ease, background-color 0.2s ease, padding 0.2s ease;
}

.post-expandable--active {
  background: rgba(0, 0, 0, 0.54);
  padding: 10px;
  max-height: 15000px;
}

/* --- Collapsed card (like habit-card) --- */
.post-card {
  position: relative;
  display: flex;
  align-items: center;
  width: 100%;
  min-height: 86px;
  padding: 14px 18px 14px 74px;
  background-color: #faf9f9;
  border-radius: 10px;
  border: none;
  font: inherit;
  text-align: left;
  cursor: pointer;
}

.post-card__avatar {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  width: 48px;
  height: 48px;
}

.post-card__avatar-ring {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  overflow: hidden;
}

.post-card__avatar-ring--lg {
  width: 56px;
  height: 56px;
}

.post-card__avatar-inner {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  border: 1px solid rgba(255, 255, 255, 0.3);
  background: rgba(255, 255, 255, 0.15);
  padding: 2px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.post-card__avatar-img {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.post-card__body {
  flex: 1;
  min-width: 0;
}

.post-card__author {
  margin: 0;
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 16px;
  font-weight: 700;
  line-height: 1.1;
  color: #2b2d42;
}

.post-card__content {
  margin: 3px 0 0;
  font-size: 13px;
  color: #707070;
  line-height: 1.3;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.post-card__dots {
  position: absolute;
  top: 8px;
  right: 8px;
  display: flex;
  align-items: center;
  gap: 3px;
}

.post-card__dots span {
  width: 6px;
  height: 6px;
  border-radius: 999px;
  background-color: #d9d9d9;
}

/* --- Expanded panel (like habit-expand) --- */
.post-expand-inline {
  animation: post-sheet-up 0.22s ease-out;
  margin-top: 8px;
}

.post-expand-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 8px;
}

.post-expand-close {
  border: 0;
  background: transparent;
  color: #faf9f9;
  cursor: pointer;
  padding: 4px;
}

.post-expand-date {
  color: rgba(250, 249, 249, 0.6);
  font-size: 12px;
}

.post-expand-delete {
  border: 0;
  background: transparent;
  color: rgba(250, 249, 249, 0.6);
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 12px;
  font-weight: 400;
  cursor: pointer;
  transition: color 0.15s, opacity 0.15s;
  padding: 0;
  line-height: 1;
}

.post-expand-delete:hover {
  color: #faf9f9;
}

.post-expand-panel {
  background: #faf9f9;
  border-radius: 14px;
  padding: 16px;
}

.post-expand-header {
  margin-bottom: 12px;
}

.post-expand-avatar-row {
  display: flex;
  align-items: center;
  gap: 12px;
}

.post-expand-avatar-btn {
  border: 0;
  background: transparent;
  padding: 0;
  cursor: pointer;
  flex-shrink: 0;
}

.post-expand-author {
  margin: 0;
}

.post-expand-author__name {
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 18px;
  font-weight: 700;
  color: #2b2d42;
  cursor: pointer;
  transition: color 0.15s;
}

.post-expand-author__name:hover {
  color: #79D45D;
}

.post-expand-time {
  margin: 2px 0 0;
  font-size: 12px;
  color: #707070;
}

.post-expand-content {
  margin: 0 0 12px;
  font-size: 15px;
  color: #2b2d42;
  line-height: 1.5;
  white-space: pre-wrap;
  word-break: break-word;
}

/* --- Attachment block --- */
.post-expand-attachment {
  padding: 10px;
  background: #ecfdf3;
  border-radius: 10px;
  border: 1px solid #bbf7d0;
  margin-bottom: 12px;
}

.post-expand-attachment__item {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 6px;
}

.post-expand-attachment__badge {
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  padding: 2px 8px;
  border-radius: 6px;
  color: #ffffff;
}

.post-expand-attachment__badge--habit {
  background: #79D45D;
}

.post-expand-attachment__badge--template {
  background: #94bef0;
}

.post-expand-attachment__name {
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 14px;
  font-weight: 600;
  color: #2b2d42;
}

.post-expand-import-btn {
  border: 2px solid #6FBC58;
  background: #79D45D;
  color: #ffffff;
  border-radius: 10px;
  padding: 6px 16px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: filter 0.15s;
}

.post-expand-import-btn:hover {
  filter: brightness(0.97);
}

/* --- Actions row --- */
.post-expand-actions {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 12px;
  padding-bottom: 12px;
  border-bottom: 1px solid #e5e5e5;
}

.post-expand-comment-count {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  color: #707070;
  font-size: 13px;
}

/* --- Comments section --- */
.post-expand-comments-section {
  margin-top: 4px;
}

@keyframes post-sheet-up {
  from { transform: translateY(22px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}
</style>

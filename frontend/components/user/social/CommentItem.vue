<template>
  <div :class="['comment-item', depth > 0 ? 'comment-item--nested' : '']">
    <div class="comment-item__avatar">
      <button
        type="button"
        @click="openProfile"
        class="comment-item__avatar-btn"
      >
        <div class="comment-item__avatar-ring" :style="avatarBackgroundStyle">
          <div class="comment-item__avatar-inner">
            <img
              v-if="monsterImage"
              :src="monsterImage"
              alt="Monstre del perfil"
              class="comment-item__avatar-img"
              :style="monsterStyle"
              decoding="async"
              draggable="false"
            />
          </div>
        </div>
      </button>
    </div>
    <div class="comment-item__body">
      <div class="comment-item__bubble">
        <div class="comment-item__header">
          <span
            class="comment-item__author"
            @click="openProfile"
          >{{ comment.user?.nom }}</span>
          <span class="comment-item__time">{{ formatDate(comment.created_at) }}</span>
        </div>
        <p class="comment-item__text">
          <span v-if="comment._replyToUserName" class="comment-item__mention">@{{ comment._replyToUserName }}</span>
          {{ comment.content }}
        </p>
      </div>
      <div class="comment-item__actions">
        <button
          @click="onReplyClick"
          class="comment-item__reply-btn"
        >
          {{ $t('social.reply') }}
        </button>
        <UserSocialLikeButton
          :content-id="comment.id"
          content-type="comment"
          :initial-liked="comment.liked_by_current_user"
          :initial-count="comment.likes_count || 0"
          class="comment-item__like"
        />
      </div>
      <div v-if="showReply" class="comment-item__reply-form">
        <UserSocialCommentForm
          :post-id="comment.post_id"
          :parent-id="replyParentId"
          :reply-to-name="replyToName"
          @submitted="onReplySubmitted"
        />
      </div>
      <div v-if="comment.children && comment.children.length > 0">
        <UserSocialCommentItem
          v-for="child in comment.children"
          :key="child.id"
          :comment="child"
          :depth="depth + 1"
          :root-comment-id="rootId"
        />
      </div>
    </div>
  </div>
</template>

<script>
import { useAuthStore } from "~/stores/useAuthStore.js";
import { getMonsterImageFromUser } from "~/utils/monsterImage.js";
import bosqueImg from "~/assets/img/Bosque.png";

export default {
  name: "CommentItem",
  props: {
    comment: { type: Object, required: true },
    depth: { type: Number, default: 0 },
    rootCommentId: { type: Number, default: null }
  },
  data: function () {
    return {
      showReply: false
    };
  },
  computed: {
    avatarBackgroundStyle: function () {
      return {
        backgroundImage: "url(" + bosqueImg + ")",
        backgroundSize: "cover",
        backgroundPosition: "center",
      };
    },
    monsterImage: function () {
      return getMonsterImageFromUser(this.comment.user);
    },
    monsterStyle: function () {
      return {
        transform: "scale(1.2) translateY(10%)",
        filter: "drop-shadow(0 2px 4px rgba(0,0,0,0.15))"
      };
    },
    rootId: function () {
      if (this.depth === 0) return this.comment.id;
      return this.rootCommentId || this.comment.id;
    },
    replyParentId: function () {
      if (this.depth === 0) return this.comment.id;
      return this.comment.id;
    },
    replyToName: function () {
      if (this.depth === 0) return null;
      return this.comment.user ? this.comment.user.nom : null;
    }
  },
  methods: {
    openProfile: function () {
      if (this.comment.user_id) {
        var authStore = useAuthStore();
        if (this.comment.user_id === authStore.user?.id) {
          this.$router.push('/perfil');
        } else {
          this.$router.push('/user/' + this.comment.user_id);
        }
      }
    },
    onReplyClick: function () {
      this.showReply = !this.showReply;
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
    onReplySubmitted: function () {
      this.showReply = false;
      this.$emit("replySubmitted");
    }
  }
};
</script>

<style scoped>
.comment-item {
  display: flex;
  gap: 10px;
  margin-top: 12px;
}

.comment-item--nested {
  margin-left: 28px;
  margin-top: 8px;
}

.comment-item__avatar {
  flex-shrink: 0;
}

.comment-item__avatar-btn {
  border: 0;
  background: transparent;
  padding: 0;
  cursor: pointer;
}

.comment-item__avatar-ring {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  overflow: hidden;
}

.comment-item__avatar-inner {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  border: 1px solid rgba(255, 255, 255, 0.3);
  background: rgba(255, 255, 255, 0.15);
  padding: 1px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.comment-item__avatar-img {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.comment-item__body {
  flex: 1;
  min-width: 0;
}

.comment-item__bubble {
  background: #f3f3f3;
  border-radius: 10px;
  padding: 8px 12px;
}

.comment-item__header {
  display: flex;
  align-items: center;
  gap: 8px;
}

.comment-item__author {
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 13px;
  font-weight: 700;
  color: #2b2d42;
  cursor: pointer;
  transition: color 0.15s;
}

.comment-item__author:hover {
  color: #79D45D;
}

.comment-item__time {
  font-size: 11px;
  color: #b0b0b0;
}

.comment-item__text {
  margin: 2px 0 0;
  font-size: 13px;
  color: #5b5b5b;
  line-height: 1.4;
}

.comment-item__mention {
  color: #79D45D;
  font-weight: 700;
  margin-right: 4px;
}

.comment-item__actions {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-top: 4px;
}

.comment-item__reply-btn {
  border: 0;
  background: transparent;
  color: #79D45D;
  font-family: "Comfortaa", system-ui, sans-serif;
  font-size: 11px;
  font-weight: 700;
  cursor: pointer;
  padding: 2px 0;
  transition: opacity 0.15s;
}

.comment-item__reply-btn:hover {
  opacity: 0.8;
}

.comment-item__like {
  transform: scale(0.8);
  transform-origin: left center;
}

.comment-item__reply-form {
  margin-top: 6px;
}
</style>

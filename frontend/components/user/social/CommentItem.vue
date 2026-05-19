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
        <button v-if="isOwner" type="button" @click="deleteComment" class="comment-item__delete-corner" title="Eliminar comentari">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="3 6 5 6 21 6"></polyline>
            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
          </svg>
        </button>
        <button v-else type="button" @click="$emit('report', comment.user_id)" class="comment-item__report-corner" title="Reportar usuari">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
            <line x1="12" y1="9" x2="12" y2="13"></line>
            <line x1="12" y1="17" x2="12.01" y2="17"></line>
          </svg>
        </button>
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
          @report="$emit('report', $event)"
          @commentDeleted="$emit('commentDeleted')"
        />
      </div>
    </div>
  </div>
</template>

<script>
import { useAuthStore } from "~/stores/useAuthStore.js";
import { useSocialStore } from "~/stores/useSocialStore.js";
import { getMonsterImageFromUser } from "~/utils/monsterImage.js";
import bosqueImg from "~/assets/img/Fons/Fons_Bosc.png";
import fonsAplicacioImg from "~/assets/img/Fons/Fons_Aplicacio.png";
import fonsPlatjaImg from "~/assets/img/Fons/Fons_Platja.png";
import fonsCasaImg from "~/assets/img/Fons/Fons_Casa.png";

export default {
  name: "CommentItem",
  props: {
    comment: { type: Object, required: true },
    depth: { type: Number, default: 0 },
    rootCommentId: { type: Number, default: null }
  },
  emits: ["replySubmitted", "report", "commentDeleted"],
  data: function () {
    return {
      showReply: false
    };
  },
  computed: {
    avatarBackgroundStyle: function () {
      var fonsKey = this.comment.user ? this.comment.user.fons_key : null;
      var bg = bosqueImg;
      if (fonsKey === "fons_platja") bg = fonsPlatjaImg;
      else if (fonsKey === "fons_casa") bg = fonsCasaImg;
      else if (fonsKey === "fons_aplicacio") bg = fonsAplicacioImg;
      return {
        backgroundImage: "url(" + bg + ")",
        backgroundSize: "cover",
        backgroundPosition: "center",
      };
    },
    monsterImage: function () {
      var skinKey = this.comment.user ? this.comment.user.skin_key : null;
      return getMonsterImageFromUser(this.comment.user, skinKey);
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
    },
    isOwner: function () {
      var authStore = useAuthStore();
      return this.comment.user_id === authStore.user?.id;
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
    },
    deleteComment: async function () {
      var socialStore = useSocialStore();
      var ok = await socialStore.deleteComment(this.comment.id);
      if (ok) {
        this.$emit("commentDeleted");
      }
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

.comment-item__report {
  border: 0;
  background: #E85B7A;
  color: #ffffff;
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 10px;
  font-weight: 600;
  cursor: pointer;
  transition: filter 0.15s;
  padding: 4px 6px;
  border-radius: 6px;
  line-height: 1;
}

.comment-item__report:hover {
  filter: brightness(0.9);
}

.comment-item__delete-corner {
  position: absolute;
  top: 6px;
  right: 6px;
  border: 0;
  background: transparent;
  color: #b0b0b0;
  width: 24px;
  height: 24px;
  border-radius: 6px;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: color 0.15s, background 0.15s;
}

.comment-item__delete-corner:hover {
  color: #e74c3c;
  background: rgba(231, 76, 60, 0.1);
}

.comment-item__report-corner {
  position: absolute;
  top: 6px;
  right: 6px;
  border: 0;
  background: #FF8DA6;
  color: #ffffff;
  width: 24px;
  height: 24px;
  border-radius: 6px;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: filter 0.15s;
}

.comment-item__report-corner:hover {
  filter: brightness(0.9);
}

.comment-item__bubble {
  background: #f3f3f3;
  border-radius: 10px;
  padding: 8px 12px;
  position: relative;
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

<template>
  <div :class="['flex gap-3', depth > 0 ? 'ml-8 mt-3' : 'mt-4']">
    <div class="flex-shrink-0">
      <button
        type="button"
        @click="openProfile"
        class="w-12 h-12 rounded-full overflow-hidden shadow-inner p-0 border-0 bg-transparent cursor-pointer"
      >
        <div class="w-full h-full rounded-full overflow-hidden" :style="avatarBackgroundStyle">
          <div class="w-full h-full rounded-full border border-gray-200 bg-white/20 p-1 flex items-center justify-center">
            <img
              v-if="monsterImage"
              :src="monsterImage"
              alt="Monstre del perfil"
              class="w-full h-full object-contain"
              :style="monsterStyle"
              decoding="async"
              draggable="false"
            />
          </div>
        </div>
      </button>
    </div>
    <div class="flex-1">
      <div class="bg-gray-50 rounded-lg px-4 py-2">
        <div class="flex items-center gap-2">
          <span
            class="font-semibold text-sm text-gray-800 cursor-pointer hover:text-blue-600 hover:underline transition-colors"
            @click="openProfile"
          >{{ comment.user?.nom }}</span>
          <span class="text-xs text-gray-500">{{ formatDate(comment.created_at) }}</span>
        </div>
        <p class="text-gray-700 text-sm mt-1">{{ comment.content }}</p>
      </div>
      <div v-if="depth < 3" class="mt-1 flex items-center gap-3">
        <button
          @click="showReply = !showReply"
          class="text-xs text-blue-600 hover:text-blue-800"
        >
          {{ $t('social.reply') }}
        </button>
        <UserSocialLikeButton
          :content-id="comment.id"
          content-type="comment"
          :initial-liked="comment.liked_by_current_user"
          :initial-count="comment.likes_count || 0"
          class="scale-75 origin-left"
        />
      </div>
      <div v-if="showReply" class="mt-2">
        <UserSocialCommentForm
          :post-id="comment.post_id"
          :parent-id="comment.id"
          @submitted="onReplySubmitted"
        />
      </div>
      <div v-if="comment.children && comment.children.length > 0">
        <UserSocialCommentItem
          v-for="child in comment.children"
          :key="child.id"
          :comment="child"
          :depth="depth + 1"
        />
      </div>
    </div>
  </div>

  <UserSocialPublicProfileView
    v-if="showProfile"
    :user-id="comment.user_id"
    @close="showProfile = false"
  />
</template>

<script>
import { getMonsterImageFromUser } from "~/utils/monsterImage.js";
import bosqueImg from "~/assets/img/Bosque.png";

export default {
  name: "CommentItem",
  props: {
    comment: { type: Object, required: true },
    depth: { type: Number, default: 0 }
  },
  data: function () {
    return {
      showReply: false,
      showProfile: false
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
    }
  },
  methods: {
    openProfile: function () {
      if (this.comment.user_id) {
        this.showProfile = true;
      }
    },
    getInitials: function (name) {
      if (!name) return "?";
      return name
        .split(" ")
        .map(function (n) { return n[0]; })
        .join("")
        .toUpperCase()
        .substring(0, 2);
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

<template>
  <div :class="['flex gap-3', depth > 0 ? 'ml-8 mt-3' : 'mt-4']">
    <div class="flex-shrink-0">
      <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-white text-sm font-bold">
        {{ getInitials(comment.user?.nom) }}
      </div>
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
      <div v-if="depth < 2" class="mt-1 flex items-center gap-3">
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

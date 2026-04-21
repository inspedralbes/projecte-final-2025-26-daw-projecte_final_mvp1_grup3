<template>
  <div class="mt-4">
    <h4 class="text-sm font-semibold text-gray-700 mb-2">
      {{ $t('social.comments') }} ({{ comments.length }})
    </h4>
    <div v-if="loading" class="text-center py-4 text-gray-500 text-sm">
      {{ $t('home.loading') }}
    </div>
    <div v-else-if="comments.length === 0" class="text-center py-4 text-gray-500 text-sm">
      {{ $t('social.no_comments') }}
    </div>
    <div v-else>
      <UserSocialCommentItem
        v-for="comment in comments"
        :key="comment.id"
        :comment="comment"
        :depth="0"
        @replySubmitted="onReplySubmitted"
      />
    </div>
  </div>
</template>

<script>
import { useSocialStore } from "~/stores/useSocialStore.js";

export default {
  name: "CommentList",
  props: {
    postId: { type: Number, required: true },
    initialComments: { type: Array, default: function () { return []; } }
  },
  data: function () {
    return {
      comments: this.initialComments,
      loading: false
    };
  },
  mounted: function () {
    this.loadComments();
  },
  methods: {
    loadComments: async function () {
      if (this.comments.length > 0) return;

      this.loading = true;
      var socialStore = useSocialStore();
      var result = await socialStore.getComments(this.postId);

      if (result) {
        this.comments = result;
      }

      this.loading = false;
    },
    onReplySubmitted: function () {
      this.loadComments();
    }
  }
};
</script>

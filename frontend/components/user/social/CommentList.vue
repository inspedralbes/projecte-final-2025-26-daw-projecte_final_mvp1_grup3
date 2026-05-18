<template>
  <div class="comment-list">
    <h4 class="comment-list__title">
      {{ $t('social.comments') }} ({{ comments.length }})
    </h4>
    <div v-if="loading" class="comment-list__status">
      {{ $t('home.loading') }}
    </div>
    <div v-else-if="comments.length === 0" class="comment-list__status">
      {{ $t('social.no_comments') }}
    </div>
    <div v-else class="comment-list__scroll">
      <UserSocialCommentItem
        v-for="comment in treeComments"
        :key="comment.id"
        :comment="comment"
        :depth="0"
        @replySubmitted="onReplySubmitted"
        @commentDeleted="loadComments"
        @report="$emit('report', $event)"
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
  emits: ["report"],
  data: function () {
    return {
      comments: this.initialComments,
      loading: false
    };
  },
  computed: {
    treeComments: function () {
      var map = {};
      var roots = [];

      this.comments.forEach(function (comment) {
        var c = Object.assign({}, comment);
        c.children = [];
        c._replyToUserName = null;
        c._rootParentId = null;
        map[c.id] = c;
      });

      this.comments.forEach(function (comment) {
        var c = map[comment.id];
        if (!c.parent_id || !map[c.parent_id]) {
          roots.push(c);
          return;
        }
        var parent = map[c.parent_id];
        var grandparent = parent.parent_id ? map[parent.parent_id] : null;
        if (grandparent) {
          c._replyToUserName = parent.user ? parent.user.nom : null;
          var root = grandparent;
          while (root.parent_id && map[root.parent_id]) {
            root = map[root.parent_id];
          }
          c._rootParentId = root.id;
          root.children.push(c);
        } else {
          parent.children.push(c);
        }
      });

      return roots;
    }
  },
  mounted: function () {
    this.loadComments();
  },
  watch: {
    initialComments: {
      handler: function (newVal) {
        this.comments = newVal;
      },
      deep: true
    }
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

<style scoped>
.comment-list {
  margin-top: 8px;
}

.comment-list__title {
  margin: 0 0 8px;
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 14px;
  font-weight: 700;
  color: #2b2d42;
}

.comment-list__status {
  text-align: center;
  padding: 16px 0;
  color: #b0b0b0;
  font-size: 13px;
}

.comment-list__scroll {
  max-height: none;
  overflow-y: visible;
}
</style>

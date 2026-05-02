<template>
  <div class="mt-2">
    <div class="flex gap-2">
      <input
        v-model="content"
        type="text"
        :placeholder="$t('social.write_comment')"
        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-blue-500"
        @keyup.enter="submit"
      />
      <button
        @click="submit"
        :disabled="!content.trim() || loading"
        class="px-4 py-2 bg-blue-500 text-white rounded-lg text-sm font-medium hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed"
      >
        {{ $t('social.send') }}
      </button>
    </div>
    <p v-if="error" class="text-red-500 text-xs mt-1">{{ error }}</p>
  </div>
</template>

<script>
import { useSocialStore } from "~/stores/useSocialStore.js";

export default {
  name: "CommentForm",
  props: {
    postId: { type: Number, required: true },
    parentId: { type: Number, default: null }
  },
  emits: ["submitted"],
  data: function () {
    return {
      content: "",
      loading: false,
      error: null
    };
  },
  methods: {
    submit: async function () {
      if (!this.content.trim() || this.loading) return;

      this.loading = true;
      this.error = null;

      var socialStore = useSocialStore();
      var result = await socialStore.addComment(this.postId, this.content, this.parentId);

      if (result) {
        this.content = "";
        this.$emit("submitted");
      } else {
        this.error = socialStore.error || this.$t('social.error_comment');
      }

      this.loading = false;
    }
  }
};
</script>

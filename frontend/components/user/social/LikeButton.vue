<template>
  <button
    @click="toggleLike"
    :class="[
      'flex items-center gap-1 px-3 py-1 rounded-full transition-colors',
      liked ? 'bg-red-100 text-red-600' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
    ]"
    :disabled="loading"
  >
    <svg
      xmlns="http://www.w3.org/2000/svg"
      :class="['w-5 h-5', liked ? 'fill-current' : 'fill-none']"
      viewBox="0 0 24 24"
      stroke="currentColor"
      stroke-width="2"
    >
      <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
    </svg>
    <span>{{ count }}</span>
  </button>
</template>

<script>
import { useSocialStore } from "~/stores/useSocialStore.js";

export default {
  name: "LikeButton",
  props: {
    postId: { type: Number, required: true },
    initialLiked: { type: Boolean, default: false },
    initialCount: { type: Number, default: 0 }
  },
  data: function () {
    return {
      liked: this.initialLiked,
      count: this.initialCount,
      loading: false
    };
  },
  methods: {
    toggleLike: async function () {
      if (this.loading) return;

      this.loading = true;
      var socialStore = useSocialStore();
      var result = await socialStore.toggleLike(this.postId, "App\\Models\\SocialPost");

      if (result) {
        this.liked = result.liked !== undefined ? result.liked : !this.liked;
        this.count = result.likes_count !== undefined ? result.likes_count : this.count;
      }

      this.loading = false;
    }
  }
};
</script>

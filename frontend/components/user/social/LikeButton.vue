<!--
  Component o pagina Nuxt: LikeButton.
  Comentaris de codi: agents/frontend/AgentNuxt.md + AgentJavascript.md
-->
<template>
  <button
    @click="toggleLike"
    :class="[
      'like-btn',
      liked ? 'like-btn--active' : ''
    ]"
    :disabled="loading"
  >
    <svg
      xmlns="http://www.w3.org/2000/svg"
      :class="['like-btn__icon', liked ? 'like-btn__icon--filled' : '']"
      viewBox="0 0 24 24"
      stroke="currentColor"
      stroke-width="2"
    >
      <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
    </svg>
    <span class="like-btn__count">{{ count }}</span>
  </button>
</template>

<script>
import { useSocialStore } from "~/stores/useSocialStore.js";

export default {
  name: "LikeButton",
  props: {
    contentId: { type: Number, required: true },
    contentType: { type: String, default: "post" },
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
  watch: {
    initialLiked: function (newVal) {
      this.liked = newVal;
    },
    initialCount: function (newVal) {
      this.count = newVal;
    }
  },
  methods: {
    toggleLike: async function () {
      if (this.loading) return;

      this.loading = true;
      var socialStore = useSocialStore();
      var result = await socialStore.toggleLike(this.contentId, this.contentType);

      if (result) {
        this.liked = result.liked !== undefined ? result.liked : !this.liked;
        this.count = result.likes_count !== undefined ? result.likes_count : this.count;
      }

      this.loading = false;
    }
  }
};
</script>

<style scoped>
.like-btn {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 5px 12px;
  border: 0;
  border-radius: 10px;
  background: #f3f3f3;
  color: #707070;
  font-family: "Comfortaa", system-ui, sans-serif;
  font-size: 13px;
  cursor: pointer;
  transition: background 0.15s, color 0.15s;
}

.like-btn:hover {
  background: #e9e9e9;
}

.like-btn--active {
  background: #fde8ec;
  color: #ff6b8a;
}

.like-btn--active:hover {
  background: #fbd0d9;
}

.like-btn__icon {
  width: 18px;
  height: 18px;
  fill: none;
}

.like-btn__icon--filled {
  fill: currentColor;
}

.like-btn__count {
  font-weight: 600;
  line-height: 1;
}
</style>

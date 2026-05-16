<template>
  <div class="bg-white rounded-2xl sm:rounded-3xl shadow-sm border border-gray-100 p-4">
    <div class="flex items-start gap-3">
      <div class="flex-shrink-0">
        <button
          type="button"
          @click="openProfile"
          class="w-14 h-14 rounded-full overflow-hidden shadow-inner p-0 border-0 bg-transparent cursor-pointer"
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
      <div class="flex-1 min-w-0">
        <div class="flex items-center justify-between">
          <div>
            <span
              class="font-semibold text-gray-800 cursor-pointer hover:text-blue-600 hover:underline transition-colors"
              @click="openProfile"
            >{{ post.user?.nom }}</span>
            <span class="text-gray-500 text-sm ml-2">{{ formatDate(post.created_at) }}</span>
          </div>
          <div class="relative" v-if="isOwner">
            <button @click="showMenu = !showMenu" class="text-gray-400 hover:text-gray-600 p-1">
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>
              </svg>
            </button>
            <div v-if="showMenu" class="absolute right-0 mt-1 w-32 bg-white rounded-lg shadow-lg border z-10">
              <button
                @click="deletePost"
                class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50"
              >
                {{ $t('social.delete') }}
              </button>
            </div>
          </div>
        </div>

        <p class="text-gray-700 mt-2 whitespace-pre-wrap">{{ post.content }}</p>

        <div v-if="post.habit || post.plantilla" class="mt-3 p-3 bg-blue-50 rounded-lg">
          <div v-if="post.habit" class="flex items-center gap-2">
            <span class="text-blue-600 font-medium">{{ post.habit.titol }}</span>
            <span class="text-gray-500 text-sm">({{ $t('social.habit') }})</span>
          </div>
          <div v-if="post.plantilla" class="flex items-center gap-2">
            <span class="text-purple-600 font-medium">{{ post.plantilla.titol }}</span>
            <span class="text-gray-500 text-sm">({{ $t('social.template') }})</span>
          </div>
          <button
            @click="$emit('import', post)"
            class="mt-2 px-3 py-1 bg-blue-500 text-white text-sm rounded-lg hover:bg-blue-600"
          >
            {{ $t('social.import') }}
          </button>
        </div>

        <div class="flex items-center gap-4 mt-4">
          <UserSocialLikeButton
            :content-id="post.id"
            content-type="post"
            :initial-liked="post.liked_by_current_user"
            :initial-count="post.likes_count || 0"
          />
          <button
            @click="showComments = !showComments"
            class="flex items-center gap-1 text-gray-500 hover:text-gray-700"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            <span class="text-sm">{{ commentsCount }}</span>
          </button>
        </div>

        <UserSocialPublicProfileView
          v-if="showProfile"
          :user-id="post.user_id"
          @close="showProfile = false"
        />

        <div v-if="showComments" class="mt-4 border-t pt-4">
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
      showMenu: false,
      showComments: false,
      showProfile: false,
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
    openProfile: function () {
      if (this.post.user_id) {
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
    deletePost: async function () {
      if (!confirm(this.$t('social.confirm_delete'))) return;

      var socialStore = useSocialStore();
      var result = await socialStore.deletePost(this.post.id);

      if (result) {
        this.$emit("deleted", this.post.id);
      }

      this.showMenu = false;
    },
  }
};
</script>

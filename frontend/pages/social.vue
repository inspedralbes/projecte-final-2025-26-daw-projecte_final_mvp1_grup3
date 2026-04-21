<template>
  <div class="min-h-screen bg-gray-50">
    <HeaderUser />

    <div class="max-w-2xl mx-auto px-4 py-6">
      <h1 class="text-2xl font-bold text-gray-800 mb-6">{{ $t('social.title') }}</h1>

      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <textarea
          v-model="newPostContent"
          :placeholder="$t('social.whats_new')"
          rows="3"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 resize-none"
        ></textarea>
        <div class="flex justify-between items-center mt-3">
          <span class="text-xs text-gray-500">{{ newPostContent.length }}/500</span>
          <button
            @click="createPost"
            :disabled="!newPostContent.trim() || posting"
            class="px-4 py-2 bg-blue-500 text-white rounded-lg font-medium hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ posting ? $t('home.loading') : $t('social.post') }}
          </button>
        </div>
        <p v-if="postError" class="text-red-500 text-sm mt-2">{{ postError }}</p>
      </div>

      <div v-if="loading" class="text-center py-8">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
        <p class="text-gray-500 mt-2">{{ $t('home.loading') }}</p>
      </div>

      <div v-else-if="posts.length === 0" class="text-center py-12">
        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/>
        </svg>
        <p class="text-gray-500">{{ $t('social.no_posts') }}</p>
        <p class="text-gray-400 text-sm mt-1">{{ $t('social.be_first') }}</p>
      </div>

      <div v-else class="space-y-4">
        <UserSocialPostCard
          v-for="post in posts"
          :key="post.id"
          :post="post"
          @import="openImportWizard"
          @deleted="onPostDeleted"
        />
      </div>

      <UserSocialImportWizard
        :show="showImportWizard"
        :post="selectedPost"
        @close="closeImportWizard"
      />
    </div>
  </div>
</template>

<script>
import { useSocialStore } from "~/stores/useSocialStore.js";

export default {
  name: "SocialPage",
  middleware: ["auth"],
  data: function () {
    return {
      newPostContent: "",
      posting: false,
      postError: null,
      loading: false,
      posts: [],
      showImportWizard: false,
      selectedPost: null
    };
  },
  mounted: function () {
    this.loadPosts();
  },
  methods: {
    loadPosts: async function () {
      this.loading = true;
      var socialStore = useSocialStore();
      var result = await socialStore.fetchFeed();

      if (result) {
        this.posts = socialStore.posts;
      }

      this.loading = false;
    },
    createPost: async function () {
      if (!this.newPostContent.trim() || this.posting) return;

      this.posting = true;
      this.postError = null;

      var socialStore = useSocialStore();
      var result = await socialStore.createPost({
        content: this.newPostContent
      });

      if (result) {
        this.newPostContent = "";
        await this.loadPosts();
      } else {
        this.postError = socialStore.error || this.$t('social.error_post');
      }

      this.posting = false;
    },
    openImportWizard: function (post) {
      this.selectedPost = post;
      this.showImportWizard = true;
    },
    closeImportWizard: function () {
      this.showImportWizard = false;
      this.selectedPost = null;
    },
    onPostDeleted: function (postId) {
      this.posts = this.posts.filter(function (p) {
        return p.id !== postId;
      });
    }
  }
};
</script>

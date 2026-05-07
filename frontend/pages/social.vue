<template>
  <div class="w-full min-w-0 min-h-screen overflow-x-hidden pb-24 lg:pb-8">
    <!-- Rounded shell: header + body share one card (full-bleed header had square top corners before) -->
    <div
      class="w-full max-w-2xl mx-auto min-w-0 box-border px-2 sm:px-4 md:px-6 pt-2 sm:pt-3"
    >
      <div
        class="rounded-2xl sm:rounded-3xl overflow-hidden bg-white shadow-md border border-gray-100"
      >
        <HeaderSocial />
        <div class="px-3 sm:px-5 py-4 sm:py-6">
      <h1 class="text-xl sm:text-2xl font-bold text-gray-800 mb-4 sm:mb-6">{{ $t('social.title') }}</h1>

      <div
        class="w-full max-w-full min-w-0 box-border bg-gray-50/90 rounded-2xl sm:rounded-3xl shadow-sm border border-gray-100/90 p-3 sm:p-5 mb-5 sm:mb-6"
      >
        <textarea
          v-model="newPostContent"
          :placeholder="$t('social.whats_new')"
          rows="3"
          class="w-full max-w-full min-w-0 box-border px-3 py-2.5 border border-gray-300 rounded-xl sm:rounded-2xl focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 resize-none text-base leading-relaxed"
        ></textarea>

        <!-- Preview de l'adjunt -->
        <div v-if="selectedAttachment" class="mt-2 p-2 bg-blue-50 rounded-xl sm:rounded-2xl border border-blue-100 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <div :class="['w-8 h-8 rounded-lg flex items-center justify-center text-white', selectedAttachment.type === 'habit' ? 'bg-blue-500' : 'bg-purple-500']">
              <svg v-if="selectedAttachment.type === 'habit'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
              </svg>
              <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
              </svg>
            </div>
            <div>
              <span class="text-xs font-semibold text-gray-500 uppercase">{{ selectedAttachment.type === 'habit' ? $t('social.habit') : $t('social.template') }}</span>
              <p class="text-sm font-medium text-gray-800">{{ selectedAttachment.titol || selectedAttachment.nom }}</p>
            </div>
          </div>
          <button @click="selectedAttachment = null" class="text-gray-400 hover:text-red-500">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <div
          class="mt-3 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between sm:gap-2"
        >
          <div class="flex items-center gap-2 min-w-0">
            <button
              @click="showAttachmentSelector = true"
              class="p-2 shrink-0 text-gray-500 hover:text-blue-500 hover:bg-blue-50 rounded-full transition-all"
              :title="$t('social.add_attachment') || 'Adjuntar hàbit o plantilla'"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
              </svg>
            </button>
            <span class="text-xs text-gray-400 tabular-nums">{{ newPostContent.length }}/500</span>
          </div>
          <button
            @click="createPost"
            :disabled="!newPostContent.trim() || posting"
            class="w-full sm:w-auto shrink-0 px-4 py-2.5 sm:py-2 text-sm sm:text-base bg-blue-500 text-white rounded-xl sm:rounded-2xl font-medium hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed touch-manipulation"
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

      <UserSocialAttachmentSelector
        :show="showAttachmentSelector"
        @close="showAttachmentSelector = false"
        @selected="onAttachmentSelected"
      />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { useSocialStore } from "~/stores/useSocialStore.js";
import HeaderSocial from "~/components/HeaderSocial.vue";

export default {
  name: "SocialPage",
  components: {
    HeaderSocial,
  },
  middleware: ["auth"],
  data: function () {
    return {
      newPostContent: "",
      posting: false,
      postError: null,
      loading: false,
      posts: [],
      showImportWizard: false,
      selectedPost: null,
      showAttachmentSelector: false,
      selectedAttachment: null
    };
  },
  mounted: function () {
    this.loadPosts();
  },
  methods: {
    loadPosts: async function () {
      this.loading = true;
      try {
        var socialStore = useSocialStore();
        await socialStore.fetchFeed();
        this.posts = socialStore.posts;
      } catch (e) {
        console.error("Error loading posts:", e);
      } finally {
        this.loading = false;
      }
    },
    createPost: async function () {
      if (!this.newPostContent.trim() || this.posting) return;

      this.posting = true;
      this.postError = null;

      var postData = {
        content: this.newPostContent
      };

      if (this.selectedAttachment) {
        if (this.selectedAttachment.type === 'habit') {
          postData.habit_id = this.selectedAttachment.id;
        } else {
          postData.plantilla_id = this.selectedAttachment.id;
        }
      }

      var socialStore = useSocialStore();
      var result = await socialStore.createPost(postData);

      if (result) {
        this.newPostContent = "";
        this.selectedAttachment = null;
      } else {
        this.postError = socialStore.error || this.$t('social.error_post');
      }

      this.posting = false;
    },
    onAttachmentSelected: function (attachment) {
      this.selectedAttachment = attachment;
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

<template>
  <div class="social-page min-h-screen bg-transparent pb-24 lg:pb-8">
    <div class="max-w-2xl mx-auto px-3 sm:px-6 pt-2 sm:pt-3">
      <HeaderSocial />

      <div class="moment-divider mt-4 mb-4" role="presentation">
        <span class="moment-divider__line" aria-hidden="true"></span>
        <span class="moment-divider__text">comparteix un missatge</span>
        <span class="moment-divider__line" aria-hidden="true"></span>
      </div>

      <div class="social-compose-card">
        <textarea
          v-model="newPostContent"
          :placeholder="$t('social.whats_new')"
          rows="3"
          class="social-compose-textarea"
        ></textarea>

        <div v-if="selectedAttachments && selectedAttachments.length > 0" class="social-compose-attachments-list">
          <div v-for="(att, index) in selectedAttachments" :key="att.type + '-' + att.id" class="social-compose-attachment">
            <div class="social-compose-attachment__info">
              <div
                class="social-compose-attachment__icon"
                :class="att.type === 'habit' ? 'social-compose-attachment__icon--habit' : 'social-compose-attachment__icon--template'"
              >
                <svg v-if="att.type === 'habit'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                </svg>
              </div>
              <div>
                <span class="social-compose-attachment__type">{{ att.type === 'habit' ? $t('social.habit') : $t('social.template') }}</span>
                <p class="social-compose-attachment__name">{{ att.titol || att.nom }}</p>
              </div>
            </div>
            <button @click="removeAttachment(index)" class="social-compose-attachment__remove">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
        </div>

        <div class="social-compose-actions">
          <div class="social-compose-actions__left">
            <button
              @click="showAttachmentSelector = true"
              class="social-compose-attach-btn"
              :title="$t('social.add_attachment') || 'Adjuntar hàbit o plantilla'"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
              </svg>
            </button>
            <span class="social-compose-counter">{{ newPostContent.length }}/500</span>
          </div>
          <button
            @click="createPost"
            :disabled="!newPostContent.trim() || posting"
            class="social-compose-submit"
          >
            {{ posting ? $t('home.loading') : $t('social.post') }}
          </button>
        </div>
        <p v-if="postError" class="social-compose-error">{{ postError }}</p>
      </div>

      <div class="moment-divider mt-6 mb-4" role="presentation">
        <span class="moment-divider__line" aria-hidden="true"></span>
        <span class="moment-divider__text">missatges de la comunitat</span>
        <span class="moment-divider__line" aria-hidden="true"></span>
      </div>

      <div v-if="loading" class="social-loading">
        <div class="social-loading__spinner"></div>
        <p class="social-loading__text">{{ $t('home.loading') }}</p>
      </div>

      <div v-else-if="posts.length === 0" class="social-empty">
        <svg class="social-empty__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/>
        </svg>
        <p class="social-empty__title">{{ $t('social.no_posts') }}</p>
        <p class="social-empty__subtitle">{{ $t('social.be_first') }}</p>
      </div>

      <div v-else class="social-feed">
        <UserSocialPostCard
          v-for="post in posts"
          :key="post.id"
          :post="post"
          @import="openImportWizard"
          @deleted="onPostDeleted"
          @report="openReportModal"
        />
      </div>

      <UserSocialImportWizard
        :show="showImportWizard"
        :post="selectedPost"
        :attachment="selectedAttachmentToImport"
        @close="closeImportWizard"
      />

      <ReportUserModal
        :show="showReportModal"
        :userId="selectedReportUserId"
        @close="showReportModal = false"
        @submit="handleReportSubmit"
      />

      <UserSocialAttachmentSelector
        :show="showAttachmentSelector"
        @close="showAttachmentSelector = false"
        @selected="onAttachmentSelected"
      />
    </div>
  </div>
</template>

<script>
import { useSocialStore } from "~/stores/useSocialStore.js";
import { authFetch } from "~/composables/useApi.js";
import HeaderSocial from "~/components/HeaderSocial.vue";
import ReportUserModal from "~/components/user/social/ReportUserModal.vue";

export default {
  name: "SocialPage",
  components: {
    HeaderSocial,
    ReportUserModal
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
      selectedAttachmentToImport: null,
      showAttachmentSelector: false,
      selectedAttachments: [],
      showReportModal: false,
      selectedReportUserId: null
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
        content: this.newPostContent,
        attachments: this.selectedAttachments
      };

      if (this.selectedAttachments.length > 0) {
        var firstHabit = this.selectedAttachments.find(function (a) { return a.type === 'habit'; });
        var firstPlantilla = this.selectedAttachments.find(function (a) { return a.type === 'plantilla'; });
        if (firstHabit) postData.habit_id = firstHabit.id;
        if (firstPlantilla) postData.plantilla_id = firstPlantilla.id;
      }

      var socialStore = useSocialStore();
      var result = await socialStore.createPost(postData);

      if (result) {
        this.newPostContent = "";
        this.selectedAttachments = [];
      } else {
        this.postError = socialStore.error || this.$t('social.error_post');
      }

      this.posting = false;
    },
    onAttachmentSelected: function (attachment) {
      if (!this.selectedAttachments) {
        this.selectedAttachments = [];
      }
      var exists = this.selectedAttachments.some(function (item) {
        return item.id === attachment.id && item.type === attachment.type;
      });
      if (!exists) {
        this.selectedAttachments.push(attachment);
      }
    },
    removeAttachment: function (index) {
      this.selectedAttachments.splice(index, 1);
    },
    openImportWizard: function (data) {
      if (data && data.post && data.attachment) {
        this.selectedPost = data.post;
        this.selectedAttachmentToImport = data.attachment;
      } else {
        this.selectedPost = data;
        this.selectedAttachmentToImport = null;
      }
      this.showImportWizard = true;
    },
    closeImportWizard: function () {
      this.showImportWizard = false;
      this.selectedPost = null;
      this.selectedAttachmentToImport = null;
    },
    onPostDeleted: function (postId) {
      this.posts = this.posts.filter(function (p) {
        return p.id !== postId;
      });
    },
    openReportModal: function (userId) {
      this.selectedReportUserId = userId;
      this.showReportModal = true;
    },
    handleReportSubmit: async function (reportData) {
      const motiusMap = {
        nom: "Nom inapropiat",
        insult: "Text insultant",
        us_indegut: "Ús indegut de l'app",
        comentari: "Comentari ofensiu",
        altres: "Altres"
      };
      const motiuText = motiusMap[reportData.motiu] || reportData.motiu;
      const reasonText = "[" + motiuText + "]" + (reportData.detalls ? " - " + reportData.detalls : "");

      try {
        const resposta = await authFetch("/api/social/report", {
          method: "POST",
          headers: {
            "Content-Type": "application/json"
          },
          body: JSON.stringify({
            content_type: "user",
            content_id: reportData.userId,
            motiu: motiuText,
            detalls: reportData.detalls || ""
          })
        });

        if (resposta.ok) {
          this.showReportModal = false;
          if (this.$swal) {
            this.$swal.fire({
              icon: "success",
              title: "Report enviat",
              text: "El report s'ha enviat correctament per a la seva revisió.",
              confirmButtonColor: "#79D45D"
            });
          } else {
            alert("El report s'ha enviat correctament.");
          }
        } else {
          alert("Error a l'enviar el report. Si us plau, torna-ho a provar.");
        }
      } catch (e) {
        console.error("Error reportant usuari:", e);
        alert("Error de connexió a l'enviar el report.");
      }
    }
  }
};
</script>

<style scoped>
.social-page {
  font-family: "Comfortaa", system-ui, sans-serif;
}

.moment-divider {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 30px;
  width: 100%;
}

.moment-divider__text {
  flex-shrink: 0;
  color: #faf9f9;
  font-size: 15px;
  line-height: 1.2;
  white-space: nowrap;
}

.moment-divider__line {
  flex: 1 1 0;
  min-width: 0;
  height: 3px;
  background: #faf9f9;
  border-radius: 999px;
}

/* --- Compose card --- */
.social-compose-card {
  background: #faf9f9;
  border-radius: 10px;
  padding: 14px;
  margin-bottom: 12px;
}

.social-compose-textarea {
  width: 100%;
  min-width: 0;
  box-sizing: border-box;
  padding: 10px 14px;
  border: 1px solid #e5e5e5;
  border-radius: 10px;
  background: #ffffff;
  resize: none;
  font-family: "Comfortaa", system-ui, sans-serif;
  font-size: 14px;
  line-height: 1.5;
  color: #2b2d42;
  outline: none;
  transition: border-color 0.2s;
}

.social-compose-textarea:focus {
  border-color: #79D45D;
  box-shadow: 0 0 0 2px rgba(121, 212, 93, 0.15);
}

.social-compose-attachments-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-top: 8px;
}

.social-compose-attachment {
  padding: 8px 12px;
  background: #ecfdf3;
  border-radius: 10px;
  border: 1px solid #bbf7d0;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.social-compose-attachment__info {
  display: flex;
  align-items: center;
  gap: 10px;
}

.social-compose-attachment__icon {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #ffffff;
  flex-shrink: 0;
}

.social-compose-attachment__icon--habit {
  background: #79D45D;
}

.social-compose-attachment__icon--template {
  background: #94bef0;
}

.social-compose-attachment__type {
  font-size: 10px;
  font-weight: 700;
  color: #707070;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.social-compose-attachment__name {
  margin: 0;
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 14px;
  font-weight: 600;
  color: #2b2d42;
}

.social-compose-attachment__remove {
  border: 0;
  background: transparent;
  color: #707070;
  cursor: pointer;
  padding: 4px;
  border-radius: 6px;
  transition: color 0.15s;
}

.social-compose-attachment__remove:hover {
  color: #ff6b8a;
}

.social-compose-actions {
  margin-top: 10px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
}

.social-compose-actions__left {
  display: flex;
  align-items: center;
  gap: 8px;
}

.social-compose-attach-btn {
  border: 0;
  background: transparent;
  color: #707070;
  padding: 6px;
  border-radius: 10px;
  cursor: pointer;
  transition: color 0.15s, background 0.15s;
}

.social-compose-attach-btn:hover {
  color: #79D45D;
  background: rgba(121, 212, 93, 0.1);
}

.social-compose-counter {
  font-size: 11px;
  color: #b0b0b0;
  font-variant-numeric: tabular-nums;
}

.social-compose-submit {
  border: 2px solid #6FBC58;
  background: #79D45D;
  color: #ffffff;
  border-radius: 10px;
  padding: 8px 20px;
  font-family: "Comfortaa", system-ui, sans-serif;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: filter 0.15s, opacity 0.15s;
  flex-shrink: 0;
}

.social-compose-submit:hover {
  filter: brightness(0.97);
}

.social-compose-submit:disabled {
  opacity: 0.45;
  cursor: not-allowed;
}

.social-compose-error {
  margin: 6px 0 0;
  color: #ff6b8a;
  font-size: 13px;
}

/* --- Loading --- */
.social-loading {
  text-align: center;
  padding: 40px 0;
}

.social-loading__spinner {
  display: inline-block;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  border: 3px solid #e5e5e5;
  border-top-color: #79D45D;
  animation: social-spin 0.7s linear infinite;
}

@keyframes social-spin {
  to { transform: rotate(360deg); }
}

.social-loading__text {
  margin: 8px 0 0;
  color: #707070;
  font-size: 13px;
}

/* --- Empty --- */
.social-empty {
  text-align: center;
  padding: 48px 0;
}

.social-empty__icon {
  width: 56px;
  height: 56px;
  margin: 0 auto 12px;
  color: #d9d9d9;
}

.social-empty__title {
  margin: 0;
  color: #707070;
  font-size: 14px;
  font-weight: 600;
}

.social-empty__subtitle {
  margin: 4px 0 0;
  color: #b0b0b0;
  font-size: 12px;
}

/* --- Feed --- */
.social-feed {
  display: flex;
  flex-direction: column;
  gap: 10px;
}
</style>

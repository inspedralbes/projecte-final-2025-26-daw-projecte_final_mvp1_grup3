<!--
  Component o pagina Nuxt: PostCard.
  Comentaris de codi: agents/frontend/AgentNuxt.md + AgentJavascript.md
-->
<template>
  <div class="post-card-wrapper">
    <div
      class="post-expandable"
      :class="showComments ? 'post-expandable--active' : ''"
    >
      <button type="button" class="post-card" @click="toggleExpand">
      <div class="post-card__avatar">
        <div class="post-card__avatar-ring" :style="avatarBackgroundStyle">
          <div class="post-card__avatar-inner">
            <img
              v-if="monsterImage"
              :src="monsterImage"
              alt="Monstre del perfil"
              class="post-card__avatar-img"
              :style="monsterStyle"
              decoding="async"
              draggable="false"
            />
          </div>
        </div>
      </div>
      <div class="post-card__body">
        <p class="post-card__author">{{ post.user?.nom }}</p>
        <p class="post-card__content">{{ post.content }}</p>
      </div>
      <span class="post-card__dots" aria-hidden="true">
        <span></span><span></span><span></span>
      </span>
    </button>

    <div v-if="showComments" class="post-expand-inline">
      <div class="post-expand-top">
        <button class="post-expand-close" type="button" @click.stop="showComments = false">
          <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M11.2917 6.54167L6.54167 11.2917M6.54167 6.54167L11.2917 11.2917M16.8333 8.91667C16.8333 13.2889 13.2889 16.8333 8.91667 16.8333C4.54441 16.8333 1 13.2889 1 8.91667C1 4.54441 4.54441 1 8.91667 1C13.2889 1 16.8333 4.54441 16.8333 8.91667Z" stroke="#FAF9F9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>
        <span class="post-expand-date">{{ formatDate(post.created_at) }}</span>
        <button v-if="isOwner" class="post-expand-delete" type="button" @click.stop="deletePost">
          <svg width="15" height="15" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M2 4h12M5.333 4V2.667a1.333 1.333 0 011.334-1.334h2.666a1.333 1.333 0 011.334 1.334V4m2 0v9.333a1.333 1.333 0 01-1.334 1.334H4.667a1.333 1.333 0 01-1.334-1.334V4h9.334z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <span>{{ $t('social.delete') }}</span>
        </button>
      </div>

      <div class="post-expand-panel">
        <div class="post-expand-header">
          <div class="post-expand-avatar-row">
            <button type="button" class="post-expand-avatar-btn" @click="openProfile">
              <div class="post-card__avatar-ring post-card__avatar-ring--lg" :style="avatarBackgroundStyle">
                <div class="post-card__avatar-inner">
                  <img
                    v-if="monsterImage"
                    :src="monsterImage"
                    alt="Monstre del perfil"
                    class="post-card__avatar-img"
                    :style="monsterStyle"
                    decoding="async"
                    draggable="false"
                  />
                </div>
              </div>
            </button>
            <div>
              <p class="post-expand-author">
                <span class="post-expand-author__name">{{ post.user?.nom }}</span>
              </p>
              <p class="post-expand-time">{{ formatDate(post.created_at) }}</p>
            </div>
          </div>
          <button v-if="!isOwner" class="post-expand-report-corner" type="button" @click.stop="$emit('report', { type: 'post', id: post.id })" title="Reportar post">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
              <line x1="12" y1="9" x2="12" y2="13"></line>
              <line x1="12" y1="17" x2="12.01" y2="17"></line>
            </svg>
          </button>
        </div>

        <p class="post-expand-content">{{ post.content }}</p>

        <div v-if="post.attachments && post.attachments.length > 0" class="post-expand-attachments-container">
          <div v-for="att in post.attachments" :key="att.type + '-' + att.id" class="att-wrapper">
            <div class="post-expand-attachment">
              <div class="post-expand-attachment__info">
                <span class="post-expand-attachment__badge" :class="att.type === 'habit' ? 'post-expand-attachment__badge--habit' : 'post-expand-attachment__badge--template'">
                  {{ att.type === 'habit' ? $t('social.habit') : $t('social.template') }}
                </span>
                <span class="post-expand-attachment__name">{{ att.titol || (att.habit ? att.habit.titol : (att.plantilla ? att.plantilla.titol : '')) }}</span>
              </div>
              <div class="post-expand-attachment__actions">
                <button v-if="att.type === 'habit' && att.habit" type="button" class="att-details-toggle" :class="expandedAttachments[att.type + '-' + att.id] ? 'att-details-toggle--open' : ''" @click.stop="toggleAttDetails(att)">
                  <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M3.5 5.25L7 8.75L10.5 5.25" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
                <button type="button" class="post-expand-import-btn" @click="$emit('import', { post: post, attachment: att })">
                  {{ $t('social.import') }}
                </button>
              </div>
            </div>
            <Transition name="att-detail">
              <div v-if="att.type === 'habit' && att.habit && expandedAttachments[att.type + '-' + att.id]" class="att-habit-details">
                <div class="att-habit-meta">
                  <span class="att-meta-item"><span class="att-meta-icon">☆</span>{{ getHabitDifficulty(att.habit) }}</span>
                  <span class="att-meta-item"><span class="att-meta-icon">↻</span>{{ getHabitFrequency(att.habit) }}</span>
                  <span class="att-meta-item"><span class="att-meta-icon">◎</span>{{ att.habit.objectiu_vegades || 1 }}x</span>
                  <span v-if="att.habit.moment_dia && att.habit.moment_dia !== 'tot_dia'" class="att-meta-item"><span class="att-meta-icon">🕐</span>{{ getHabitMoment(att.habit) }}</span>
                </div>
                <div v-if="getHabitExternImage(att.habit) || getHabitExternTitle(att.habit)" class="att-habit-extern">
                  <img v-if="getHabitExternImage(att.habit)" :src="getHabitExternImage(att.habit)" alt="" class="att-habit-extern__img" @error="$event.target.style.display='none'" />
                  <span v-if="getHabitExternTitle(att.habit)" class="att-habit-extern__titol">{{ getHabitExternTitle(att.habit) }}</span>
                </div>
              </div>
            </Transition>
          </div>
        </div>

        <div class="post-expand-actions">
          <UserSocialLikeButton
            :content-id="post.id"
            content-type="post"
            :initial-liked="post.liked_by_current_user"
            :initial-count="post.likes_count || 0"
          />
          <span class="post-expand-comment-count">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            {{ commentsCount }}
          </span>
        </div>

        <div class="post-expand-comments-section">
          <UserSocialCommentForm :post-id="post.id" />
          <UserSocialCommentList :post-id="post.id" :initial-comments="post.comments || []" @report="$emit('report', $event)" />
        </div>
      </div>
    </div>
    </div>

    <UserSocialConfirmModal
      :show="showDeleteConfirm"
      :title="'Eliminar post'"
      :message="'Segur que vols eliminar aquest post? Aquesta acció no es pot desfer.'"
      confirm-text="Eliminar"
      @confirm="confirmDeletePost"
      @cancel="showDeleteConfirm = false"
    />
  </div>
</template>

<script>
import { useSocialStore } from "~/stores/useSocialStore.js";
import { useAuthStore } from "~/stores/useAuthStore.js";
import { getMonsterImageFromUser } from "~/utils/monsterImage.js";
import bosqueImg from "~/assets/img/Fons/Fons_Bosc.png";
import fonsAplicacioImg from "~/assets/img/Fons/Fons_Aplicacio.png";
import fonsPlatjaImg from "~/assets/img/Fons/Fons_Platja.png";
import fonsCasaImg from "~/assets/img/Fons/Fons_Casa.png";
import UserSocialConfirmModal from "~/components/user/social/ConfirmModal.vue";

export default {
  name: "PostCard",
  components: {
    UserSocialConfirmModal
  },
  props: {
    post: { type: Object, required: true }
  },
  emits: ["import", "deleted", "report"],
  data: function () {
    return {
      showComments: false,
      showDeleteConfirm: false,
      commentsCount: this.post.comments_count || 0,
      expandedAttachments: {}
    };
  },
  computed: {
    isOwner: function () {
      var authStore = useAuthStore();
      return this.post.user_id === authStore.user?.id;
    },
    monsterImage: function () {
      var skinKey = this.post.user ? this.post.user.skin_key : null;
      return getMonsterImageFromUser(this.post.user, skinKey);
    },
    monsterStyle: function () {
      return {
        transform: "scale(1.2) translateY(10%)",
        filter: "drop-shadow(0 2px 4px rgba(0,0,0,0.2))"
      };
    },
    avatarBackgroundStyle: function () {
      var fonsKey = this.post.user ? this.post.user.fons_key : null;
      var bg = bosqueImg;
      if (fonsKey === "fons_platja") bg = fonsPlatjaImg;
      else if (fonsKey === "fons_casa") bg = fonsCasaImg;
      else if (fonsKey === "fons_aplicacio") bg = fonsAplicacioImg;
      return {
        backgroundImage: "url(" + bg + ")",
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
    toggleExpand: function () {
      this.showComments = !this.showComments;
    },
    openProfile: function () {
      if (this.post.user_id) {
        var authStore = useAuthStore();
        if (this.post.user_id === authStore.user?.id) {
          this.$router.push('/perfil');
        } else {
          this.$router.push('/user/' + this.post.user_id);
        }
      }
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
    deletePost: function () {
      this.showDeleteConfirm = true;
    },
    confirmDeletePost: async function () {
      this.showDeleteConfirm = false;
      var socialStore = useSocialStore();
      var result = await socialStore.deletePost(this.post.id);
      if (result) {
        this.$emit("deleted", this.post.id);
      }
    },
    toggleAttDetails: function (att) {
      var key = att.type + '-' + att.id;
      this.expandedAttachments = Object.assign({}, this.expandedAttachments, { [key]: !this.expandedAttachments[key] });
    },
    getHabitDifficulty: function (habit) {
      var d = String(habit.dificultat || 'facil').toLowerCase();
      if (d === 'dificil') return 'Difícil';
      if (d === 'mitja' || d === 'media') return 'Mitjana';
      return 'Fàcil';
    },
    getHabitFrequency: function (habit) {
      var f = String(habit.frequencia_tipus || 'diaria').toLowerCase();
      if (f === 'setmanal') return 'Setmanal';
      if (f === 'mensual') return 'Mensual';
      return 'Diària';
    },
    getHabitMoment: function (habit) {
      var m = String(habit.moment_dia || '').toLowerCase();
      if (m === 'mati') return 'Matí';
      if (m === 'tarda') return 'Tarda';
      if (m === 'nit') return 'Nit';
      return '';
    },
    getHabitExternImage: function (habit) {
      var meta = habit.metadata || habit.metadada;
      if (typeof meta === 'string') { try { meta = JSON.parse(meta); } catch (e) { return null; } }
      if (!meta) return null;
      return meta.url_imatge && String(meta.url_imatge).trim() ? String(meta.url_imatge).trim() : null;
    },
    getHabitExternTitle: function (habit) {
      var meta = habit.metadata || habit.metadada;
      if (typeof meta === 'string') { try { meta = JSON.parse(meta); } catch (e) { return null; } }
      if (!meta) return null;
      return meta.titol && String(meta.titol).trim() ? String(meta.titol).trim() : null;
    }
  }
};
</script>

<style scoped>
.post-expandable {
  overflow: hidden;
  border-radius: 10px;
  max-height: 100px;
  transition: max-height 0.28s ease, background-color 0.2s ease, padding 0.2s ease;
}

.post-expandable--active {
  background: rgba(0, 0, 0, 0.54);
  padding: 10px;
  max-height: 15000px;
}

/* --- Collapsed card (like habit-card) --- */
.post-card {
  position: relative;
  display: flex;
  align-items: center;
  width: 100%;
  min-height: 86px;
  padding: 14px 18px 14px 74px;
  background-color: #faf9f9;
  border-radius: 10px;
  border: none;
  font: inherit;
  text-align: left;
  cursor: pointer;
}

.post-card__avatar {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  width: 48px;
  height: 48px;
}

.post-card__avatar-ring {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  overflow: hidden;
}

.post-card__avatar-ring--lg {
  width: 56px;
  height: 56px;
}

.post-card__avatar-inner {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  border: 1px solid rgba(255, 255, 255, 0.3);
  background: rgba(255, 255, 255, 0.15);
  padding: 2px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.post-card__avatar-img {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.post-card__body {
  flex: 1;
  min-width: 0;
}

.post-card__author {
  margin: 0;
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 16px;
  font-weight: 700;
  line-height: 1.1;
  color: #2b2d42;
}

.post-card__content {
  margin: 3px 0 0;
  font-size: 13px;
  color: #707070;
  line-height: 1.3;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.post-card__dots {
  position: absolute;
  top: 8px;
  right: 8px;
  display: flex;
  align-items: center;
  gap: 3px;
}

.post-card__dots span {
  width: 6px;
  height: 6px;
  border-radius: 999px;
  background-color: #d9d9d9;
}

/* --- Expanded panel (like habit-expand) --- */
.post-expand-inline {
  animation: post-sheet-up 0.22s ease-out;
  margin-top: 8px;
}

.post-expand-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 8px;
}

.post-expand-close {
  border: 0;
  background: transparent;
  color: #faf9f9;
  cursor: pointer;
  padding: 4px;
}

.post-expand-date {
  color: rgba(250, 249, 249, 0.6);
  font-size: 12px;
}

.post-expand-delete {
  border: 0;
  background: transparent;
  color: #ffffff;
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 11px;
  font-weight: 600;
  cursor: pointer;
  transition: opacity 0.15s;
  padding: 4px 8px;
  line-height: 1;
}

.post-expand-delete:hover {
  opacity: 0.7;
}

.post-expand-report {
  border: 0;
  background: #E85B7A;
  color: #ffffff;
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 11px;
  font-weight: 600;
  cursor: pointer;
  transition: filter 0.15s;
  padding: 4px 8px;
  border-radius: 6px;
  line-height: 1;
}

.post-expand-report:hover {
  filter: brightness(0.9);
}

.post-expand-report-corner {
  border: 0;
  background: #FF8DA6;
  color: #ffffff;
  width: 32px;
  height: 32px;
  border-radius: 8px;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: filter 0.15s;
  flex-shrink: 0;
}

.post-expand-report-corner:hover {
  filter: brightness(0.9);
}

.post-expand-panel {
  background: #faf9f9;
  border-radius: 14px;
  padding: 16px;
}

.post-expand-header {
  margin-bottom: 12px;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}

.post-expand-avatar-row {
  display: flex;
  align-items: center;
  gap: 12px;
}

.post-expand-avatar-btn {
  border: 0;
  background: transparent;
  padding: 0;
  cursor: pointer;
  flex-shrink: 0;
}

.post-expand-author {
  margin: 0;
}

.post-expand-author__name {
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 18px;
  font-weight: 700;
  color: #2b2d42;
}

.post-expand-time {
  margin: 2px 0 0;
  font-size: 12px;
  color: #707070;
}

.post-expand-content {
  margin: 0 0 12px;
  font-size: 15px;
  color: #2b2d42;
  line-height: 1.5;
  white-space: pre-wrap;
  word-break: break-word;
}

/* --- Attachment block --- */
.post-expand-attachments-container {
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-bottom: 12px;
}

.post-expand-attachment {
  padding: 12px;
  background: #ecfdf3;
  border-radius: 10px;
  border: 1px solid #bbf7d0;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}

.post-expand-attachment__info {
  display: flex;
  align-items: center;
  gap: 10px;
  min-width: 0;
}

.post-expand-attachment__badge {
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  padding: 3px 8px;
  border-radius: 6px;
  color: #ffffff;
  flex-shrink: 0;
}

.post-expand-attachment__badge--habit {
  background: #79D45D;
}

.post-expand-attachment__badge--template {
  background: #94bef0;
}

.post-expand-attachment__name {
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 14px;
  font-weight: 600;
  color: #2b2d42;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.post-expand-import-btn {
  border: 2px solid #6FBC58;
  background: #79D45D;
  color: #ffffff;
  border-radius: 10px;
  padding: 6px 16px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: filter 0.15s;
}

.post-expand-import-btn:hover {
  filter: brightness(0.97);
}

.post-expand-attachment__actions {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-shrink: 0;
}

.att-details-toggle {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 30px;
  height: 30px;
  border-radius: 8px;
  border: 1.5px solid #d1d5db;
  background: #fff;
  color: #6b7280;
  cursor: pointer;
  transition: transform 0.2s ease, border-color 0.2s ease, color 0.2s ease;
}

.att-details-toggle--open {
  transform: rotate(180deg);
  border-color: #79D45D;
  color: #79D45D;
}

.att-details-toggle:hover {
  border-color: #79D45D;
  color: #79D45D;
}

.att-habit-details {
  padding: 10px 12px;
  background: #f0fdf4;
  border-radius: 0 0 10px 10px;
  border: 1px solid #bbf7d0;
  border-top: none;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.att-habit-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 8px 14px;
}

.att-meta-item {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 13px;
  font-weight: 600;
  color: #2b2d42;
}

.att-meta-icon {
  color: #79D45D;
  font-size: 14px;
}

.att-habit-extern {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 10px;
  border-radius: 10px;
  background: rgba(121, 212, 93, 0.08);
  border: 1.5px solid rgba(121, 212, 93, 0.25);
}

.att-habit-extern__img {
  width: 44px;
  height: 44px;
  border-radius: 8px;
  object-fit: cover;
  flex-shrink: 0;
}

.att-habit-extern__titol {
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 13px;
  font-weight: 600;
  color: #2b2d42;
  line-height: 1.25;
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}

.att-detail-enter-active {
  transition: max-height 0.25s ease, opacity 0.2s ease;
  overflow: hidden;
}

.att-detail-leave-active {
  transition: max-height 0.2s ease, opacity 0.15s ease;
  overflow: hidden;
}

.att-detail-enter-from {
  max-height: 0;
  opacity: 0;
}

.att-detail-enter-to {
  max-height: 300px;
  opacity: 1;
}

.att-detail-leave-from {
  max-height: 300px;
  opacity: 1;
}

.att-detail-leave-to {
  max-height: 0;
  opacity: 0;
}

/* --- Actions row --- */
.post-expand-actions {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 12px;
  padding-bottom: 12px;
  border-bottom: 1px solid #e5e5e5;
}

.post-expand-comment-count {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  color: #707070;
  font-size: 13px;
}

/* --- Comments section --- */
.post-expand-comments-section {
  margin-top: 4px;
}

@keyframes post-sheet-up {
  from { transform: translateY(22px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}
</style>

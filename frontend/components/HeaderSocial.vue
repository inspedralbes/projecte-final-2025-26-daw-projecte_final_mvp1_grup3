<!--
  Component o pagina Nuxt: HeaderSocial.
  Comentaris de codi: agents/frontend/AgentNuxt.md + AgentJavascript.md
-->
<template>
  <header class="social-switcher">
    <button
      type="button"
      class="social-switcher__btn"
      aria-label="Anterior"
      @click="goPrev"
    >
      <svg
        class="social-switcher__icon"
        width="50"
        height="50"
        viewBox="0 0 50 50"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
        aria-hidden="true"
      >
        <path
          d="M15.625 37.5V12.5H11.4584V37.5H15.625ZM38.5417 37.5V12.5L19.7917 25L38.5417 37.5Z"
          fill="#FAF9F9"
        />
      </svg>
    </button>

    <div class="social-switcher__title-container">
      <h1 class="social-switcher__title">{{ currentLabel }}</h1>
    </div>

    <button
      type="button"
      class="social-switcher__btn social-switcher__btn--next"
      aria-label="Següent"
      @click="goNext"
    >
      <svg
        class="social-switcher__icon social-switcher__icon--next"
        width="50"
        height="50"
        viewBox="0 0 50 50"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
        aria-hidden="true"
      >
        <path
          d="M15.625 37.5V12.5H11.4584V37.5H15.625ZM38.5417 37.5V12.5L19.7917 25L38.5417 37.5Z"
          fill="#FAF9F9"
        />
      </svg>
    </button>
  </header>
</template>

<script>
export default {
  name: 'HeaderSocial',
  data() {
    return {};
  },
  computed: {
    pages() {
      const locale = this.$i18n?.locale || 'ca';
      let forumLabel = 'Fòrum';
      if (locale === 'es') forumLabel = 'Foro';
      else if (locale === 'en') forumLabel = 'Forum';

      return [
        { id: 'forum', label: forumLabel, path: '/social' },
        { id: 'friends', label: this.$t('nav.friends') || 'Amics', path: '/friends' },
        { id: 'clans', label: this.$t('nav.clans') || 'Clans', path: '/clans' }
      ];
    },
    currentIndex() {
      const path = this.$route.path;
      if (path.startsWith('/clans')) return 2;
      if (path.startsWith('/friends')) return 1;
      return 0; // /social
    },
    currentLabel() {
      return this.pages[this.currentIndex].label;
    }
  },
  methods: {
    goPrev() {
      const newIndex = (this.currentIndex - 1 + this.pages.length) % this.pages.length;
      this.$router.push(this.pages[newIndex].path);
    },
    goNext() {
      const newIndex = (this.currentIndex + 1) % this.pages.length;
      this.$router.push(this.pages[newIndex].path);
    }
  }
};
</script>

<style scoped>
.social-switcher {
  display: grid;
  grid-template-columns: 50px 1fr 50px;
  align-items: center;
  gap: 8px 12px;
  width: 100%;
  padding: 10px 0 24px 0;
}

.social-switcher__btn {
  width: 50px;
  height: 50px;
  padding: 0;
  border: none;
  border-radius: 50%;
  background: transparent;
  color: #faf9f9;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.social-switcher__btn:focus-visible {
  outline: 2px solid #79d45d;
  outline-offset: 2px;
}

.social-switcher__icon {
  width: 50px;
  height: 50px;
  display: block;
  filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.35));
  transition: transform 0.2s ease;
}

.social-switcher__icon--next {
  transform: scaleX(-1);
}

.social-switcher__btn:active .social-switcher__icon {
  transform: scale(0.9);
}

.social-switcher__btn--next:active .social-switcher__icon--next {
  transform: scaleX(-1) scale(0.9);
}

.social-switcher__title-container {
  display: flex;
  justify-content: center;
  align-items: center;
  overflow: hidden;
}

.social-switcher__title {
  margin: 0;
  min-width: 0;
  text-align: center;
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-weight: 600;
  font-size: 48px;
  line-height: 1.05;
  letter-spacing: -0.02em;
  color: #faf9f9;
  word-break: break-word;
  text-shadow: 0 1px 3px rgba(0, 0, 0, 0.35);
  animation: slideIn 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@media (max-width: 380px) {
  .social-switcher__title {
    font-size: clamp(28px, 11vw, 48px);
  }
}
</style>

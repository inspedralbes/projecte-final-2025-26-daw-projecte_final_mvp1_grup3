<template>
  <header class="social-header">
    <nav class="social-header__nav" aria-label="Social">
      <ul class="social-header__list">
        <li>
          <NuxtLink to="/social" class="social-header__link" :class="{ 'social-header__link--active': isForumPage }">{{ $t('nav.forum') }}</NuxtLink>
        </li>
        <li>
          <NuxtLink to="/friends" class="social-header__link" :class="{ 'social-header__link--active': isFriendsPage }">{{ $t('nav.friends') }}</NuxtLink>
        </li>
        <li>
          <NuxtLink to="/clans" class="social-header__link" :class="{ 'social-header__link--active': isClansPage }">{{ $t('nav.clans') }}</NuxtLink>
        </li>
      </ul>
    </nav>
  </header>
</template>

<script>
export default {
  name: 'HeaderSocial',
  computed: {
    isForumPage: function () {
      return this.$route.path === '/social';
    },
    isFriendsPage: function () {
      return this.$route.path === '/friends';
    },
    isClansPage: function () {
      return this.$route.path === '/clans' || this.$route.path.startsWith('/clans/');
    }
  }
};
</script>

<style scoped>
.social-header {
  width: 100%;
  padding: 10px 0;
}

.social-header__nav {
  width: 100%;
}

.social-header__list {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 32px;
  list-style: none;
  margin: 0;
  padding: 0;
}

@media (min-width: 640px) {
  .social-header__list {
    gap: 48px;
  }
}

.social-header__link {
  color: rgba(250, 249, 249, 0.55);
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-weight: 700;
  font-size: 15px;
  text-decoration: none;
  padding: 4px 0;
  transition: color 0.2s;
}

@media (min-width: 640px) {
  .social-header__link {
    font-size: 17px;
  }
}

.social-header__link:hover {
  color: #faf9f9;
}

.social-header__link--active {
  color: #faf9f9;
  border-bottom: 3px solid #79D45D;
}
</style>

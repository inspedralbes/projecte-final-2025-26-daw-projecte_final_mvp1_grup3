<template>
  <header class="w-full p-3 bg-white border-b border-gray-100">
    <nav class="w-full flex items-center justify-center px-2 sm:px-4" aria-label="Social">
      <ul class="flex items-center justify-center gap-8 sm:gap-12 list-none m-0 p-0">
        <li>
          <NuxtLink to="/social" class="nav-link" :class="{ active: isForumPage }">{{ $t('nav.forum') }}</NuxtLink>
        </li>
        <li>
          <NuxtLink to="/friends" class="nav-link" :class="{ active: isFriendsPage }">{{ $t('nav.friends') }}</NuxtLink>
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
    }
  }
};
</script>

<style scoped>
header {
  width: 100%;
}
nav {
  width: 100%;
}
nav ul li {
  margin: 0;
}
nav a {
  color: inherit;
  text-decoration: none;
}

.nav-link {
  color: #6b7280;
  font-weight: 600;
  transition: color 0.2s;
  padding: 0.25rem 0;
  font-size: 0.9375rem;
}
@media (min-width: 640px) {
  .nav-link {
    font-size: 1.0625rem;
  }
}
.nav-link:hover {
  color: #3b82f6;
}
.nav-link.active {
  color: #3b82f6;
  border-bottom: 2px solid #3b82f6;
}
</style>

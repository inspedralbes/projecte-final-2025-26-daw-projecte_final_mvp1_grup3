<template>
  <header class="w-full p-3 bg-white border-b border-gray-100">
    <nav class="w-full flex items-center justify-between px-4">
      
      <NuxtLink to="/home" class="brand"> 
        <img :src="logo" alt="Loopy logo" class="logo-img"/>
        <span class="app-name">Loopy</span>
      </NuxtLink>

      <ul class="flex-1 flex items-center justify-center space-x-6 list-none m-0 p-0">
        <li v-if="showSimpleNav">
          <NuxtLink to="/social" class="nav-link" :class="{ active: isForumPage }">{{ $t('nav.forum') }}</NuxtLink>
        </li>
        <li v-if="showSimpleNav">
          <NuxtLink to="/friends" class="nav-link" :class="{ active: isFriendsPage }">{{ $t('nav.friends') }}</NuxtLink>
        </li>
        <li v-if="showSimpleNav">
          <NuxtLink to="/clans" class="nav-link" :class="{ active: isClansPage }">Clans</NuxtLink>
        </li>
        <li v-if="!showSimpleNav">
          <NuxtLink to="/home" class="nav-link">{{ $t('nav.home') }}</NuxtLink> 
        </li>
        <li v-if="!showSimpleNav">
          <NuxtLink to="/habits" class="nav-link">{{ $t('nav.create') }}</NuxtLink>
        </li>
        <li v-if="!showSimpleNav">
          <NuxtLink to="/plantilles" class="nav-link">{{ $t('nav.catalog') }}</NuxtLink>
        </li>
        <li v-if="!showSimpleNav">
          <NuxtLink to="/social" class="nav-link">{{ $t('nav.forum') }}</NuxtLink>
        </li>
        <li v-if="!showSimpleNav">
          <NuxtLink to="/friends" class="nav-link">{{ $t('nav.friends') }}</NuxtLink>
        </li>
        <li v-if="!showSimpleNav">
          <NuxtLink to="/clans" class="nav-link">Clans</NuxtLink>
        </li>
        <li v-if="!showSimpleNav">
          <NuxtLink to="/perfil" class="nav-link">{{ $t('nav.profile') }}</NuxtLink>
        </li>
      </ul>
      <div class="flex items-center gap-2">
        <LanguageSwitcher />
        <button @click="handleLogout" class="logout-btn" :title="$t('nav.logout')">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
          </svg>
        </button>
      </div>
      
    </nav>
  </header>
</template>

<script>
import logo from '~/assets/img/LogoLoopy.png'
import LanguageSwitcher from './LanguageSwitcher.vue'
import { useAuthStore } from '~/stores/useAuthStore'

export default {
  name: "HeaderSocial",
  components: {
    LanguageSwitcher,
  },
  props: {
    simpleNav: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      logo: logo,
    }
  },
  computed: {
    showSimpleNav() {
      return this.$route.path === '/social' || this.$route.path === '/friends' || this.$route.path.startsWith('/clans')
    },
    isForumPage() {
      return this.$route.path === '/social'
    },
    isFriendsPage() {
      return this.$route.path === '/friends'
    },
    isClansPage() {
      return this.$route.path.startsWith('/clans')
    },
  },
  methods: {
    async handleLogout() {
      const authStore = useAuthStore()
      await authStore.logout()
      await this.$router.push('/auth/login')
    },
  },
}
</script>

<style scoped>
header {
  width: 100%;
}
nav {
  width: 100%;
}
nav ul {
  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: center;
  gap: 1.5rem;
  list-style: none;
  margin: 0;
  padding: 0;
}
nav ul li {
  margin: 0;
}
nav a {
  color: inherit;
  text-decoration: none;
}

.brand {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  color: inherit;
  text-decoration: none;
}
.brand:hover {
  color: #d1d5db;
}
.logo-img {
  width: 3rem;
  height: 3rem;
  border-radius: 50%;
  object-fit: cover;
  display: block;
}
.app-name {
  font-size: 1.25rem;
  font-weight: 700;
}

.nav-link {
  color: #6b7280;
  font-weight: 500;
  transition: color 0.2s;
  padding: 0.25rem 0;
}
.nav-link:hover {
  color: #3b82f6;
}
.nav-link.active {
  color: #3b82f6;
  border-bottom: 2px solid #3b82f6;
}

.logout-btn {
  background: none;
  border: none;
  padding: 0.5rem;
  margin-right: 1rem;
  color: #4b5563;
  cursor: pointer;
  border-radius: 0.375rem;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.logout-btn:hover {
  background-color: rgba(243, 244, 246, 0.5);
  color: #dc2626;
}
</style>
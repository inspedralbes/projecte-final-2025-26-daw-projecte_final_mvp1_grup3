<template>
  <header class="w-full p-3">
    <nav class="w-full flex items-center justify-between px-4">
      
      <NuxtLink to="/home" class="brand"> 
        <img :src="logo" alt="Loopy logo" class="logo-img"/>
        <span class="app-name">Loopy</span>
      </NuxtLink>

      <ul class="flex-1 flex items-center justify-center space-x-6 list-none m-0 p-0">
        <li>
          <NuxtLink to="/home" class="nav-link">{{ $t('nav.home') }}</NuxtLink> 
        </li>
        <li>
          <NuxtLink to="/habits" class="nav-link">{{ $t('nav.create') }}</NuxtLink>
        </li>
        <li>
          <NuxtLink to="/plantilles" class="nav-link">{{ $t('nav.catalog') }}</NuxtLink>
        </li>
        <li>
          <a href="javascript:void(0)" class="nav-link opacity-50 cursor-not-allowed" :title="$t('nav.soon')">{{ $t('nav.forum') }}</a>
        </li>
        <li>
          <NuxtLink to="/perfil" class="nav-link">{{ $t('nav.profile') }}</NuxtLink>
        </li>
      </ul>
      <LanguageSwitcher />
      <button @click="handleLogout" class="logout-btn">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
        </svg>
      </button>
      
    </nav>
  </header>
</template>

<script setup>
import logo from '~/assets/img/LogoLoopy.png'
import LanguageSwitcher from './LanguageSwitcher.vue'
import { useAuthStore } from '~/stores/useAuthStore'

const authStore = useAuthStore()

const handleLogout = async () => {
  await authStore.logout()
  await navigateTo('/Login')
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
  color: #d1d5db; /* gray-300 */
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

.logout-btn {
  background: none;
  border: none;
  padding: 0.5rem;
  margin-right: 1rem;
  color: #4b5563; /* gray-600 */
  cursor: pointer;
  border-radius: 0.375rem;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.logout-btn:hover {
  background-color: #f3f4f6; /* gray-100 */
  color: #dc2626; /* red-600 */
}
</style>
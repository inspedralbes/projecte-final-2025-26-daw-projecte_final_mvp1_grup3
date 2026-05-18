<template>
  <!-- Top header (ocult a /calendar i /social) -->
  <header v-if="!isCalendarRoute && !isSocialRoute" class="w-full p-3">
    <!-- Mòbil: hamburguesa | stats al centre | clima a la dreta -->
    <nav class="w-full lg:hidden">
      <div class="flex w-full items-center gap-1 px-1 min-h-[2.75rem]">
        <div class="flex shrink-0 w-10 justify-start">
          <button class="hamburger-btn" @click="drawerOpen = !drawerOpen" aria-label="Menu">
            <svg
              class="hamburger-menu-svg"
              width="14"
              height="10"
              viewBox="0 0 14 10"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
              aria-hidden="true"
            >
              <path
                d="M1 1H13M1 5H13M1 9H13"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
            </svg>
          </button>
        </div>
        <div class="mobile-stats-bar mobile-stats-bar--center flex flex-1 justify-center min-w-0">
          <div class="stat-item">
            <span class="stat-icon">🔥</span>
            <span class="stat-value">{{ gameStore.ratxa }}</span>
          </div>
          <div class="stat-item">
            <img :src="xpIcon" alt="" class="stat-icon-img" width="22" height="22" />
            <span class="stat-value">{{ gameStore.xpTotal }}</span>
          </div>
          <div class="stat-item">
            <img :src="coinIcon" alt="" class="stat-icon-img" width="22" height="22" />
            <span class="stat-value">{{ gameStore.monedes }}</span>
          </div>
        </div>
        <div
          class="header-weather-pill shrink-0 flex flex-row items-center gap-1 min-w-[9rem] max-w-[12.5rem] rounded-xl bg-white pl-2 pr-1 py-1 shadow-sm border border-gray-200"
        >
          <div class="flex flex-row flex-1 min-w-0 items-center gap-1.5">
            <template v-if="headerWeatherLoading">
              <span class="text-[11px] text-gray-500 animate-pulse flex-1 text-center py-0.5">…</span>
            </template>
            <template v-else-if="headerWeather && headerWeather.ok === true">
              <p
                class="flex-1 min-w-0 text-center text-sm font-black text-gray-900 leading-none tabular-nums"
                :title="headerDateShort"
              >
                {{ headerDateShort }}
              </p>
              <div class="flex flex-row items-center justify-end shrink-0 gap-0.5">
                <span class="text-lg leading-none" aria-hidden="true">{{ headerWeatherEmoji }}</span>
                <span class="text-sm font-bold text-gray-900 tabular-nums leading-none">
                  {{ headerWeatherTemp }}
                </span>
              </div>
            </template>
            <template v-else>
              <span class="text-[11px] text-gray-400 flex-1 text-center py-0.5" title="Clima no disponible">—</span>
            </template>
          </div>
          <button
            type="button"
            class="weather-city-trigger flex shrink-0 h-8 w-8 items-center justify-center rounded-lg bg-gray-50 text-gray-600 hover:bg-gray-100 active:bg-gray-200 transition touch-manipulation"
            :title="$t('header.weather_change')"
            :aria-label="$t('header.weather_change')"
            @click.stop="openWeatherCityModal"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </button>
        </div>
      </div>
    </nav>

    <!-- Escriptori -->
    <nav class="hidden lg:flex w-full items-center px-4 nav-bar-desktop">
      <div class="flex-1 min-w-0 flex justify-start">
        <button class="hamburger-btn hamburger-btn-desktop" @click="drawerOpen = !drawerOpen" aria-label="Menu">
          <svg
            class="hamburger-menu-svg"
            width="14"
            height="10"
            viewBox="0 0 14 10"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
            aria-hidden="true"
          >
            <path
              d="M1 1H13M1 5H13M1 9H13"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
          </svg>
        </button>
      </div>

      <!-- Desktop nav -->
      <ul class="desktop-nav">
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
          <NuxtLink to="/social" class="nav-link">{{ $t('nav.forum') }}</NuxtLink>
        </li>
        <li>
          <NuxtLink to="/perfil" class="nav-link">{{ $t('nav.profile') }}</NuxtLink>
        </li>
      </ul>

      <!-- Desktop: idioma + logout -->
      <div class="flex-1 min-w-0 flex justify-end">
        <div class="desktop-actions">
          <LanguageSwitcher />
          <button @click="handleLogout" class="logout-btn" :title="$t('nav.logout')">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
          </button>
        </div>
      </div>

    </nav>
  </header>

  <Teleport to="body">
    <div
      v-if="weatherCityModalOpen"
      class="fixed inset-0 z-[90] flex items-center justify-center p-4 bg-black/40 backdrop-blur-[2px]"
      role="dialog"
      aria-modal="true"
      @click.self="weatherCityModalOpen = false"
    >
      <div
        class="w-full max-w-sm rounded-2xl bg-white p-4 shadow-xl border border-gray-200 flex flex-col items-center"
        @click.stop
      >
        <div class="w-10 h-1 bg-gray-300 rounded-full mb-3"></div>
        <h2 class="text-sm font-bold text-[#949494] mb-3 text-center w-full">{{ $t('header.weather_modal_title') }}</h2>
        <input
          v-model="weatherCityInput"
          type="search"
          autocomplete="address-level2"
          class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
          :placeholder="$t('header.weather_city_placeholder')"
          @keydown.enter.prevent="confirmWeatherCity"
        />
        <div class="mt-4 flex gap-2 justify-end">
          <button
            type="button"
            class="rounded-xl px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-100"
            @click="weatherCityModalOpen = false"
          >
            {{ $t('header.weather_cancel') }}
          </button>
          <button
            type="button"
            class="rounded-xl px-4 py-2 text-sm font-bold bg-emerald-600 text-white hover:bg-emerald-700"
            @click="confirmWeatherCity"
          >
            {{ $t('header.weather_apply') }}
          </button>
        </div>
      </div>
    </div>
  </Teleport>

  <!-- Mobile drawer overlay -->
  <transition name="drawer-overlay">
    <div v-if="drawerOpen" class="drawer-overlay" @click="drawerOpen = false"></div>
  </transition>

  <!-- Mobile drawer panel -->
  <transition name="drawer-slide">
    <aside v-if="drawerOpen" class="drawer-panel">
      <div class="drawer-header">
        <button class="drawer-close" @click="drawerOpen = false" aria-label="Tancar">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- User info -->
      <div class="drawer-user">
        <div class="drawer-avatar">
          <img :src="logo" alt="" class="drawer-avatar-img" width="72" height="72" decoding="async" />
        </div>
        <p class="drawer-username">{{ userName }}</p>
      </div>

      <div class="drawer-divider"></div>

      <!-- Drawer nav: Inventari (Botiga va al footer / barra inferior) -->
      <nav class="drawer-nav">
        <NuxtLink to="/inventari" class="drawer-nav-link" @click="drawerOpen = false">
          <svg xmlns="http://www.w3.org/2000/svg" class="drawer-nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7H4a1 1 0 00-1 1v11a2 2 0 002 2h14a2 2 0 002-2V8a1 1 0 00-1-1zM8 7V5a4 4 0 018 0v2" />
          </svg>
          <span>{{ $t('nav.inventory') }}</span>
        </NuxtLink>
      </nav>

      <div class="drawer-divider"></div>

      <!-- Actions -->
      <div class="drawer-actions">
        <div class="drawer-action-row">
          <span class="drawer-action-label">{{ $t('nav.language') || 'Idioma' }}</span>
          <LanguageSwitcher />
        </div>
        <button class="drawer-logout" @click="handleLogout">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
          </svg>
          <span>{{ $t('nav.logout') }}</span>
        </button>
      </div>
    </aside>
  </transition>

  <!-- Mobile bottom tab bar -->
  <nav class="bottom-tab-bar" aria-label="Mobile navigation">
    <NuxtLink to="/home" class="tab-item">
      <svg xmlns="http://www.w3.org/2000/svg" class="tab-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1h-2z" />
      </svg>
      <span class="tab-label">{{ $t('nav.home') }}</span>
    </NuxtLink>
    <NuxtLink to="/plantilles" class="tab-item">
      <svg xmlns="http://www.w3.org/2000/svg" class="tab-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
      </svg>
      <span class="tab-label">{{ $t('nav.catalog') }}</span>
    </NuxtLink>
    <NuxtLink to="/social" class="tab-item">
      <svg xmlns="http://www.w3.org/2000/svg" class="tab-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
      </svg>
      <span class="tab-label">{{ $t('nav.forum') }}</span>
    </NuxtLink>
    <NuxtLink to="/shop" class="tab-item">
      <svg xmlns="http://www.w3.org/2000/svg" class="tab-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 10-8 0v4M5 9h14l-1.4 11.2A2 2 0 0115.6 22H8.4a2 2 0 01-1.99-1.8L5 9z" />
      </svg>
      <span class="tab-label">{{ $t('nav.shop') }}</span>
    </NuxtLink>
    <NuxtLink to="/perfil" class="tab-item">
      <svg xmlns="http://www.w3.org/2000/svg" class="tab-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
      </svg>
      <span class="tab-label">{{ $t('nav.profile') }}</span>
    </NuxtLink>
  </nav>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useRoute } from 'vue-router'
import logo from '~/assets/img/Icones/Icona_Logo_Perfil.png'
import coinIcon from '~/assets/img/Icones/Icona_Moneda.png'
import xpIcon from '~/assets/img/Icones/Icona_Experiencia.png'
import LanguageSwitcher from './LanguageSwitcher.vue'
import { useAuthStore } from '~/stores/useAuthStore'
import { useGameStore } from '~/stores/gameStore.js'
import { authFetch } from '~/composables/useApi.js'

const HEADER_WEATHER_EMOJI = {
  Clear: '☀️',
  Clouds: '⛅',
  Rain: '🌧️',
  Drizzle: '🌦️',
  Thunderstorm: '⛈️',
  Snow: '❄️',
  Mist: '🌫️',
  Fog: '🌫️',
  Haze: '🌫️'
}

const route = useRoute()
const isCalendarRoute = computed(() => String(route.path || '').startsWith('/calendar'))
const isSocialRoute = computed(() => route.path === '/social' || String(route.path || '').startsWith('/friends') || String(route.path || '').startsWith('/clans'))

const authStore = useAuthStore()
const gameStore = useGameStore()
const drawerOpen = ref(false)

const headerWeather = ref(null)
const headerWeatherLoading = ref(false)

const weatherCityModalOpen = ref(false)
const weatherCityInput = ref('')

const headerWeatherEmoji = computed(() => {
  const w = headerWeather.value?.weather
  if (!w) return '🌤️'
  return HEADER_WEATHER_EMOJI[w] || '🌤️'
})

const headerWeatherTemp = computed(() => {
  const t = headerWeather.value?.temp
  if (t === null || t === undefined || Number.isNaN(Number(t))) return '—'
  return String(Math.round(Number(t))) + '°'
})

const headerDateShort = computed(() => {
  var now = new Date()
  var day = String(now.getDate()).padStart(2, '0')
  var month = String(now.getMonth() + 1).padStart(2, '0')
  return day + '/' + month
})

/** Nom de la ciutat que retorna l'API (OpenWeather / reverse geocoding). */
const headerWeatherCity = computed(() => {
  const c = headerWeather.value?.city
  if (!c || String(c).trim() === '') return '—'
  return String(c).trim()
})

const userName = computed(() => {
  var u = authStore.user
  return u ? (u.nom || u.name || u.email || 'Usuari') : 'Usuari'
})

async function fetchWeatherByUrl(url) {
  try {
    const res = await authFetch(url, {})
    const data = await res.json()
    if (res.ok && data && data.ok === true) {
      headerWeather.value = data
    } else {
      headerWeather.value = null
    }
  } catch {
    headerWeather.value = null
  }
}

/**
 * Mateixa lògica per embolicar que home: manual > geo (si no denegat) > ciutat desada.
 */
async function loadHeaderWeather() {
  if (typeof window === 'undefined') {
    return
  }
  headerWeatherLoading.value = true
  try {
    var modeSat = localStorage.getItem('loopy_weather_mode')
    if (modeSat === 'manual') {
      var cManual = (localStorage.getItem('loopy_weather_city') || 'Barcelona').trim()
      await fetchWeatherByUrl('/api/external/weather?city=' + encodeURIComponent(cManual))
      return
    }
    if (typeof navigator !== 'undefined' && navigator.geolocation && modeSat !== 'denied') {
      try {
        var pos = await new Promise(function (resolve, reject) {
          navigator.geolocation.getCurrentPosition(resolve, reject, {
            timeout: 8000,
            maximumAge: 300000
          })
        })
        await fetchWeatherByUrl(
          '/api/external/weather?lat=' + pos.coords.latitude + '&lon=' + pos.coords.longitude
        )
      } catch {
        var cFb = (localStorage.getItem('loopy_weather_city') || 'Barcelona').trim()
        await fetchWeatherByUrl('/api/external/weather?city=' + encodeURIComponent(cFb))
      }
    } else {
      var cDefault = (localStorage.getItem('loopy_weather_city') || 'Barcelona').trim()
      await fetchWeatherByUrl('/api/external/weather?city=' + encodeURIComponent(cDefault))
    }
  } finally {
    headerWeatherLoading.value = false
  }
}

watch(
  isCalendarRoute,
  function (cal) {
    if (!cal) {
      loadHeaderWeather()
    }
  },
  { immediate: true }
)

function openWeatherCityModal() {
  if (typeof window !== 'undefined') {
    var desada = (localStorage.getItem('loopy_weather_city') || '').trim()
    weatherCityInput.value =
      desada ||
      (headerWeather.value && headerWeather.value.city ? String(headerWeather.value.city).trim() : '') ||
      'Barcelona'
  } else {
    weatherCityInput.value =
      (headerWeather.value && headerWeather.value.city ? String(headerWeather.value.city).trim() : '') ||
      'Barcelona'
  }
  weatherCityModalOpen.value = true
}

async function confirmWeatherCity() {
  var t = (weatherCityInput.value || '').trim()
  if (!t) {
    return
  }
  if (typeof window !== 'undefined') {
    localStorage.setItem('loopy_weather_city', t)
    localStorage.setItem('loopy_weather_mode', 'manual')
  }
  weatherCityModalOpen.value = false
  await loadHeaderWeather()
  if (typeof window !== 'undefined') {
    window.dispatchEvent(new CustomEvent('loopy-weather-city-changed'))
  }
}

const handleLogout = async () => {
  drawerOpen.value = false
  await authStore.logout()
  await navigateTo('/auth/login')
}
</script>

<style scoped>
header {
  width: 100%;
}
nav {
  width: 100%;
}

/* Desktop nav: hidden on mobile, flex on lg+ */
.desktop-nav {
  display: none;
  flex: 1;
  flex-direction: row;
  align-items: center;
  justify-content: center;
  gap: 1.5rem;
  list-style: none;
  margin: 0;
  padding: 0;
}
@media (min-width: 1024px) {
  .desktop-nav {
    display: flex;
  }
  .nav-bar-desktop .desktop-nav {
    flex: 0 1 auto;
  }
}
.desktop-nav li {
  margin: 0;
}

nav a {
  color: inherit;
  text-decoration: none;
}

/* Mobile stats bar (inline, right side) */
.mobile-stats-bar {
  display: flex;
  align-items: center;
  gap: 1rem;
}
@media (min-width: 1024px) {
  .mobile-stats-bar {
    display: none;
  }
}
.stat-item {
  display: flex;
  align-items: center;
  gap: 0.45rem;
}
.stat-icon {
  font-size: 1.35rem;
  line-height: 1;
}
.stat-icon-img {
  width: 1.4rem;
  height: 1.4rem;
  object-fit: contain;
  flex-shrink: 0;
  image-rendering: pixelated;
}
.stat-value {
  font-size: 1rem;
  font-weight: 700;
  color: #374151;
}

/* Desktop actions: hidden on mobile */
.desktop-actions {
  display: none;
  align-items: center;
  gap: 0.5rem;
}
@media (min-width: 1024px) {
  .desktop-actions {
    display: flex;
  }
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

/* Hamburger: sempre visible (mòbil + escriptori).
   El botó del nav mòbil està dins de <nav class="lg:hidden">, així
   queda ocult en pantalles grans. El botó del nav escriptori
   (.hamburger-btn-desktop) està dins de <nav class="hidden lg:flex">,
   així queda ocult en mòbil. Resultat: sempre hi ha exactament un
   botó hamburguesa visible. */
.hamburger-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  background: none;
  border: none;
  padding: 0.5rem;
  color: #faf9f9;
  cursor: pointer;
  border-radius: 0.375rem;
  transition: all 0.2s;
}

.hamburger-menu-svg {
  width: 1.5rem;
  height: auto;
  display: block;
}
.hamburger-btn:hover {
  background-color: rgba(243, 244, 246, 0.5);
}

/* ===== Drawer overlay + panel ===== */
.drawer-overlay {
  position: fixed;
  inset: 0;
  z-index: 60;
  background: rgba(0, 0, 0, 0.35);
}

.drawer-panel {
  position: fixed;
  top: 0;
  left: 0;
  bottom: 0;
  z-index: 70;
  width: 17rem;
  background: #fff;
  box-shadow: 4px 0 20px rgba(0, 0, 0, 0.1);
  display: flex;
  flex-direction: column;
  padding: 1rem;
  overflow-y: auto;
}

.drawer-header {
  display: flex;
  align-items: center;
  justify-content: flex-end;
}
.drawer-close {
  background: none;
  border: none;
  padding: 0.4rem;
  color: #6b7280;
  cursor: pointer;
  border-radius: 0.375rem;
  transition: all 0.15s;
}
.drawer-close:hover {
  background-color: #f3f4f6;
  color: #111827;
}

.drawer-user {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  padding: 1rem 0;
}
.drawer-avatar {
  width: 4.5rem;
  height: 4.5rem;
  border-radius: 50%;
  background: #f3f4f6;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  padding: 0.2rem;
}
.drawer-avatar-img {
  width: 100%;
  height: 100%;
  object-fit: contain;
  display: block;
}
.drawer-username {
  font-weight: 700;
  font-size: 1rem;
  color: #1f2937;
  text-align: center;
}

.drawer-divider {
  height: 1px;
  background: #e5e7eb;
  margin: 0.5rem 0;
}

.drawer-nav {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  padding: 0.25rem 0;
}
.drawer-nav-link {
  display: flex;
  align-items: center;
  gap: 0.7rem;
  padding: 0.625rem 0.75rem;
  border-radius: 0.75rem;
  color: #374151;
  font-weight: 600;
  font-size: 0.9rem;
  text-decoration: none;
  transition: background-color 0.15s, color 0.15s, transform 0.15s;
}
.drawer-nav-link:hover {
  background-color: #f3f4f6;
  transform: translateX(2px);
}
.drawer-nav-link.router-link-active {
  background-color: #ecfccb;
  color: #568039;
}
.drawer-nav-icon {
  width: 1.4rem;
  height: 1.4rem;
  flex-shrink: 0;
}
.drawer-nav-icon-img {
  width: 1.4rem;
  height: 1.4rem;
  object-fit: contain;
  flex-shrink: 0;
  image-rendering: pixelated;
}

.drawer-actions {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  padding-top: 0.5rem;
}
.drawer-action-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.5rem 0.25rem;
}
.drawer-action-label {
  font-size: 0.875rem;
  font-weight: 600;
  color: #374151;
}
.drawer-logout {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.625rem 0.75rem;
  border-radius: 0.75rem;
  background: none;
  border: none;
  color: #dc2626;
  font-weight: 600;
  font-size: 0.875rem;
  cursor: pointer;
  transition: background-color 0.15s;
  width: 100%;
}
.drawer-logout:hover {
  background-color: #fef2f2;
}

/* Drawer transitions */
.drawer-overlay-enter-active,
.drawer-overlay-leave-active {
  transition: opacity 0.25s ease;
}
.drawer-overlay-enter-from,
.drawer-overlay-leave-to {
  opacity: 0;
}

.drawer-slide-enter-active {
  transition: transform 0.25s ease-out;
}
.drawer-slide-leave-active {
  transition: transform 0.2s ease-in;
}
.drawer-slide-enter-from,
.drawer-slide-leave-to {
  transform: translateX(-100%);
}

/* ===== Bottom Tab Bar (mobile only) ===== */
.bottom-tab-bar {
  display: flex;
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  z-index: 50;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(12px);
  border-top: 1px solid rgba(229, 231, 235, 0.8);
  padding: 0.6rem 0.35rem;
  padding-bottom: calc(0.6rem + env(safe-area-inset-bottom));
  justify-content: space-around;
  align-items: center;
  box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
}
@media (min-width: 1024px) {
  .bottom-tab-bar {
    display: none;
  }
}

.tab-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.25rem;
  padding: 0.4rem 0.35rem;
  border-radius: 0.75rem;
  color: #6b7280;
  text-decoration: none;
  transition: color 0.15s;
  min-width: 2.85rem;
  flex: 1 1 0;
  min-height: 0;
}
.tab-item:hover,
.tab-item.router-link-active {
  color: #568039;
}

.tab-icon {
  width: 1.65rem;
  height: 1.65rem;
}

.tab-icon-img {
  width: 1.65rem;
  height: 1.65rem;
  object-fit: contain;
  flex-shrink: 0;
  image-rendering: pixelated;
}

.tab-label {
  font-size: 0.6875rem;
  font-weight: 600;
  line-height: 1.1;
  text-align: center;
}
</style>
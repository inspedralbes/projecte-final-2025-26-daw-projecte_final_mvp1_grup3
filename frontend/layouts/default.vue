<template>
  <div v-if="isFocusRoute" class="focus-route-only">
    <slot />
  </div>
  <div
    v-else
    class="global-app-container"
    :class="{
      'focus-route-layout': isFocusRoute,
      'shop-page-bg': isShopRoute,
      'calendar-page-bg': isCalendarRoute,
    }"
  >
    <div class="global-content-wrapper">
      <HeaderUser />
      <main
        class="mx-auto w-full min-w-0 pt-0 pb-28 lg:pb-8"
        :class="isShopRoute ? 'max-w-none px-0' : 'max-w-7xl px-3 sm:px-4'"
      >
        <slot />
      </main>
      <!-- Footer escriptori: accés ràpid a la Botiga (la barra inferior mòbil també inclou /shop) -->
      <footer class="app-footer-bar hidden lg:flex" aria-label="Peu de pàgina">
        <div class="app-footer-inner mx-auto w-full max-w-7xl px-4 py-3 flex items-center justify-center gap-2 border-t border-gray-200/80 bg-white/90 backdrop-blur-sm">
          <NuxtLink to="/shop" class="app-footer-shop-link">
            <svg xmlns="http://www.w3.org/2000/svg" class="app-footer-shop-svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
            </svg>
            <span>{{ $t('nav.shop') }}</span>
          </NuxtLink>
        </div>
      </footer>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from "vue";
import { useRoute } from "vue-router";

const route = useRoute();
const isFocusRoute = computed(() => route.path.startsWith("/focus/"));
const isShopRoute = computed(() => route.path === "/shop" || route.path.startsWith("/shop/"));
const isCalendarRoute = computed(() => route.path.startsWith("/calendar"));
</script>

<style scoped>
.focus-route-only {
  width: 100%;
  min-height: 100vh;
}

/* Ensure the container takes full height */
.global-app-container {
  min-height: 100vh;
}

.app-footer-shop-link {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  border-radius: 9999px;
  font-weight: 700;
  font-size: 0.9rem;
  color: #568039;
  text-decoration: none;
  background: rgba(236, 252, 203, 0.6);
  border: 1px solid rgba(134, 239, 172, 0.5);
  transition: background 0.15s ease, transform 0.15s ease;
}
.app-footer-shop-link:hover {
  background: rgba(220, 252, 231, 0.95);
  transform: translateY(-1px);
}
.app-footer-shop-svg {
  width: 1.25rem;
  height: 1.25rem;
  flex-shrink: 0;
}
.router-link-active.app-footer-shop-link {
  background: rgba(187, 247, 208, 0.85);
}
</style>

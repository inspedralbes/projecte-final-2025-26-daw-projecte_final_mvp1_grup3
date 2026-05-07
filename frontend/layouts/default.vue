<template>
  <div class="global-app-container" :class="{ 'focus-route-layout': isFocusRoute }">
    <div class="global-content-wrapper">
      <div class="layout-header-wrap">
        <HeaderUser />
      </div>
      <main :class="isFocusRoute ? 'focus-main' : 'mx-auto max-w-7xl px-4 py-6'">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from "vue";
import { useRoute } from "vue-router";

const route = useRoute();
const isFocusRoute = computed(() => route.path.startsWith("/focus/"));
</script>

<style scoped>
/* Ensure the container takes full height */
.global-app-container {
  min-height: 100vh;
}

@media (max-width: 767px) {
  .focus-route-layout .layout-header-wrap {
    display: none;
  }

  .focus-route-layout .focus-main {
    width: 100%;
    max-width: none;
    padding: 0;
    margin: 0;
  }
}
</style>

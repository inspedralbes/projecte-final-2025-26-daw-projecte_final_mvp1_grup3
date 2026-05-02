<template>
  <div class="global-app-container">
    <div class="global-content-wrapper">
      <HeaderUser />
      <main class="mx-auto max-w-7xl px-4 py-6">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
// No changes needed here for now
</script>

<style scoped>
/* Ensure the container takes full height */
.global-app-container {
  min-height: 100vh;
}
</style>

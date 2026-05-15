<template>
  <div class="app-transition-host">
    <NuxtLayout>
      <NuxtPage :transition="pageTransition" />
    </NuxtLayout>
  </div>
</template>

<script setup>
var route = useRoute();
var slideDir = useState('authSlideDir', function () {
  return null;
});

var pageTransition = computed(function () {
  var p = route.path;
  if ((p === '/auth/login' || p === '/auth/registre') && slideDir.value) {
    return {
      name: slideDir.value === 'forward' ? 'auth-forward' : 'auth-reverse',
      mode: 'default',
    };
  }
  return false;
});
</script>

<style scoped>
.app-transition-host {
  min-height: 100vh;
  position: relative;
  overflow-x: hidden;
}
</style>

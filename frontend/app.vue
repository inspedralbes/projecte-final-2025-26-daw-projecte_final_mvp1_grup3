<template>
  <div class="app-transition-host">
    <NuxtLayout>
      <NuxtPage :key="route.fullPath" :transition="pageTransition" />
    </NuxtLayout>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useState } from '#imports';

var route = useRoute();
var router = useRouter();

var slideDir = useState('authSlideDir', function () {
  return null;
});

var socialSlideDir = useState('socialSlideDir', function () {
  return null;
});

var socialOrder = {
  '/social': 1,
  '/friends': 2,
  '/clans': 3
};

router.beforeEach(function (to, from) {
  var fromKey = Object.keys(socialOrder).find(function(k) { return from.path === k || from.path.startsWith(k + '/'); });
  var toKey = Object.keys(socialOrder).find(function(k) { return to.path === k || to.path.startsWith(k + '/'); });
  
  if (fromKey && toKey && fromKey !== toKey) {
    if (socialOrder[toKey] > socialOrder[fromKey]) {
      socialSlideDir.value = 'forward';
    } else {
      socialSlideDir.value = 'reverse';
    }
  } else {
    socialSlideDir.value = null;
  }
});

var pageTransition = computed(function () {
  var p = route.path;
  if ((p === '/auth/login' || p === '/auth/registre') && slideDir.value) {
    return {
      name: slideDir.value === 'forward' ? 'auth-forward' : 'auth-reverse',
      mode: 'default',
    };
  }
  
  if (socialSlideDir.value) {
    return {
      name: socialSlideDir.value === 'forward' ? 'social-slide-left' : 'social-slide-right',
      mode: 'out-in'
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

<style>
.social-slide-left-enter-active,
.social-slide-left-leave-active,
.social-slide-right-enter-active,
.social-slide-right-leave-active {
  transition: all 0.3s cubic-bezier(0.25, 1, 0.5, 1);
}

.social-slide-left-enter-from {
  opacity: 0;
  transform: translateX(100%);
}
.social-slide-left-leave-to {
  opacity: 0;
  transform: translateX(-100%);
}

.social-slide-right-enter-from {
  opacity: 0;
  transform: translateX(-100%);
}
.social-slide-right-leave-to {
  opacity: 0;
  transform: translateX(100%);
}
</style>

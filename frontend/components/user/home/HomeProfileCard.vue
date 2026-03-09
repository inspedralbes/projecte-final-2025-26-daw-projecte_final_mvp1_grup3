<template>
  <div class="text-center">
    <div class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 mx-auto mb-3 flex items-center justify-center">
      <span class="text-3xl"></span>
    </div>
    <h3 class="font-bold text-gray-800 text-sm">{{ user ? user.nom : $t('home.user_name') }}</h3>
    <p class="text-xs text-gray-500 mb-2">{{ $t('home.user_tag') }}</p>
    <div class="flex justify-center items-center gap-1 text-xs text-gray-600">
      <span>{{ $t('home.level') }} {{ nivell }}</span>
      <div class="w-20 h-1 bg-gray-200 rounded-full overflow-hidden">
        <div class="h-1 bg-blue-500" :style="{ width: percentatgeNivell + '%' }"></div>
      </div>
      <span class="text-[10px] text-gray-500">{{ xpActualNivel }}/{{ xpObjetivoNivel }}</span>
    </div>
  </div>
</template>

<script>
export default {
  name: 'HomeProfileCard',
  props: {
    user: { type: Object, default: null },
    nivell: { type: Number, default: 1 },
    xpActualNivel: { type: Number, default: 0 },
    xpObjetivoNivel: { type: Number, default: 1000 },
    percentatgeNivell: { type: Number, default: 0 }
  }
};
</script>

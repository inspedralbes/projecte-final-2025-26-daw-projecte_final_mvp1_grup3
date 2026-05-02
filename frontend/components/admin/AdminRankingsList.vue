<template>
  <div class="space-y-4">
    <div v-for="(r, i) in rankings" :key="i" class="flex items-center justify-between p-4 rounded-2xl bg-white shadow-sm border border-gray-50">
      <span class="text-xs font-black text-gray-700">{{ i + 1 }}. {{ r.nom }}</span>
      <span class="text-[10px] font-black text-orange-500">{{ r.valor }}</span>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AdminRankingsList',
  props: { rankings: { type: Array, default: function () { return []; } } }
};
</script>

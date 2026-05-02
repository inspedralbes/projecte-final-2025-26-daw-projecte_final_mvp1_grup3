<template>
  <div
    class="bg-white rounded-[3rem] p-10 shadow-2xl border border-gray-100 hover:-translate-y-2 transition-all cursor-pointer group"
    @click="$emit('click')"
  >
    <div class="flex justify-between items-start mb-10">
      <div
        class="w-14 h-14 rounded-2xl flex items-center justify-center text-xs font-black uppercase"
        :class="iconClass"
      >
        {{ iconLabel }}
      </div>
      <span class="text-[10px] font-black uppercase tracking-widest" :class="badgeClass">{{ badge }}</span>
    </div>
    <h3 class="text-5xl font-black text-gray-900 tracking-tighter mb-2">
      <slot>{{ value }}</slot>
    </h3>
    <p class="text-[10px] text-gray-400 font-bold uppercase mt-4 group-hover:opacity-80 transition-colors">
      {{ subtitle }}
    </p>
  </div>
</template>

<script>
export default {
  name: 'AdminStatCard',
  props: {
    iconLabel: { type: String, default: '' },
    iconClass: { type: String, default: 'bg-blue-50 text-blue-600' },
    badge: { type: String, default: '' },
    badgeClass: { type: String, default: 'text-green-500' },
    value: { type: [String, Number], default: '' },
    subtitle: { type: String, default: '' }
  }
};
</script>

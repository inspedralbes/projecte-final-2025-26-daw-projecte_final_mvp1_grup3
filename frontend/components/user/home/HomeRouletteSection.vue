<template>
  <div
    class="bg-white flex items-center justify-center cursor-pointer transition"
    :class="[
      compact
        ? 'rounded-lg p-1.5 flex-col gap-0.5 w-full h-full min-h-[4rem] sm:min-h-[4.25rem] border border-gray-100 justify-center shadow-md'
        : 'rounded-2xl p-4 shadow-lg',
      classeIconaRuleta
    ]"
    @click="$emit('obrir-modal-ruleta')"
    :title="$t('home.roulette_daily')"
    role="button"
    tabindex="0"
    @keydown.enter="$emit('obrir-modal-ruleta')"
    @keydown.space.prevent="$emit('obrir-modal-ruleta')"
  >
    <span
      :class="compact ? 'text-xl leading-none select-none' : 'text-2xl leading-none select-none'"
      aria-hidden="true"
    >🎰</span>
    <span
      :class="
        compact
          ? 'text-[9px] font-bold text-center leading-tight text-gray-800'
          : 'text-sm font-bold'
      "
    >
      {{ $t('home.roulette') }}
    </span>
  </div>
</template>

<script>
export default {
  name: 'HomeRouletteSection',
  props: {
    classeIconaRuleta: { type: String, default: '' },
    compact: { type: Boolean, default: false }
  }
};
</script>

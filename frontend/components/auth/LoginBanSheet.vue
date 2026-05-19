<template>
  <div v-if="show" class="fixed inset-0 z-[200] flex flex-col justify-end pointer-events-auto">
    <div class="fixed inset-0 bg-black/50 transition-opacity" @click="close"></div>

    <div
      class="relative w-full rounded-t-[32px] overflow-hidden animate-ban-slide-up shadow-[0_-8px_30px_rgba(0,0,0,0.12)] flex flex-col ban-sheet"
      role="dialog"
      aria-labelledby="login-ban-title"
    >
      <div class="w-full flex justify-center pt-4 pb-2">
        <div class="w-12 h-1.5 bg-white/40 rounded-full"></div>
      </div>

      <div class="px-6 pb-8 pt-2 overflow-y-auto custom-scrollbar">
        <h2 id="login-ban-title" class="text-2xl font-['Bricolage_Grotesque'] font-bold text-white mb-2 text-center">
          {{ $t('login_ban_title') }}
        </h2>
        <p class="text-white/80 text-sm font-['Comfortaa'] font-bold text-center mb-5">
          {{ $t('login_ban_subtitle') }}
        </p>

        <div class="flex flex-col gap-2">
          <div class="ban-info-row">
            <div class="ban-info-icon" aria-hidden="true">&#9203;</div>
            <div class="flex flex-col gap-0.5 min-w-0">
              <span class="text-white/70 text-xs font-['Comfortaa'] font-bold uppercase tracking-wide">
                {{ $t('login_ban_duration_label') }}
              </span>
              <span class="text-white font-['Comfortaa'] font-bold text-base leading-snug">
                {{ duradaText }}
              </span>
            </div>
          </div>

          <div class="ban-info-row">
            <div class="ban-info-icon" aria-hidden="true">&#128203;</div>
            <div class="flex flex-col gap-0.5 min-w-0">
              <span class="text-white/70 text-xs font-['Comfortaa'] font-bold uppercase tracking-wide">
                {{ $t('login_ban_reason_label') }}
              </span>
              <span class="text-white font-['Comfortaa'] font-bold text-base leading-snug break-words">
                {{ motiuText }}
              </span>
            </div>
          </div>
        </div>

        <div class="mt-6">
          <button
            type="button"
            class="w-full bg-[#FFD166] hover:bg-[#ffc233] text-gray-900 font-['Comfortaa'] font-bold py-3 rounded-xl shadow-[0_4px_0_#d9a738] active:translate-y-[4px] active:shadow-none transition-all"
            @click="close"
          >
            {{ $t('login_ban_close') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  show: { type: Boolean, required: true },
  ban: { type: Object, default: null }
});

const emit = defineEmits(['close']);
const { t } = useI18n();

const duradaText = computed(function () {
  var b = props.ban;
  if (!b) return t('login_ban_unknown');
  if (b.permanent) return t('login_ban_permanent');
  if (b.durada_desconeguda) return t('login_ban_unknown');
  if (b.dies_restant === 1) return t('login_ban_one_day');
  return t('login_ban_days', { days: b.dies_restant || 0 });
});

const motiuText = computed(function () {
  if (props.ban && props.ban.motiu) return props.ban.motiu;
  return t('login_ban_reason_default');
});

function close() {
  emit('close');
}
</script>

<style scoped>
.ban-sheet { background-color: #ff8da6; max-height: 85vh; }
@keyframes ban-slide-up { from { transform: translateY(100%); } to { transform: translateY(0); } }
.animate-ban-slide-up { animation: ban-slide-up 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
.ban-info-row {
  display: flex; align-items: flex-start; gap: 12px;
  background-color: rgba(255, 255, 255, 0.15);
  border: 2px solid rgba(255, 255, 255, 0.2);
  padding: 14px 16px; border-radius: 16px;
}
.ban-info-icon {
  width: 28px; height: 28px; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.1rem; line-height: 1;
}
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: rgba(255, 255, 255, 0.1); border-radius: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.3); border-radius: 4px; }
</style>

<template>
  <div class="inventari-page min-h-screen overflow-x-hidden pb-24 lg:pb-8">
    <div class="max-w-5xl mx-auto px-3 sm:px-5 pt-4 sm:pt-6">
      <header class="bento-card mb-6 bg-white/95 backdrop-blur-md shadow-2xl border border-white/50">
        <h1 class="text-2xl sm:text-3xl font-black text-gray-800">{{ $t('inventory.title') }}</h1>
        <p class="text-sm text-gray-500 mt-1">{{ $t('inventory.subtitle') }}</p>
      </header>

      <section v-if="loading" class="bento-card bg-white/95 text-center py-10 text-gray-500">
        {{ $t('shop.loading') }}
      </section>

      <section v-else-if="skins.length === 0 && consumibles.length === 0" class="bento-card bg-white/95 text-center py-10">
        <p class="text-gray-500 mb-4">{{ $t('inventory.empty') }}</p>
        <NuxtLink to="/shop" class="inline-block px-5 py-2.5 rounded-2xl bg-gradient-to-br from-purple-500 to-indigo-500 text-white font-bold shadow-md hover:opacity-90">
          {{ $t('inventory.go_to_shop') }}
        </NuxtLink>
      </section>

      <template v-else>
        <section v-if="skins.length > 0" class="mb-8">
          <h2 class="text-lg font-bold text-gray-700 mb-3 px-1">{{ $t('inventory.skins_section') }}</h2>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
            <article
              v-for="ui in skins"
              :key="ui.id"
              class="inv-card bento-card flex flex-col items-center text-center bg-white/95 backdrop-blur-md shadow-xl border border-white/60"
              :class="{ 'inv-card-equipped': ui.equipat }"
            >
              <div class="inv-card-img-wrapper flex items-center justify-center w-32 h-32 rounded-3xl bg-gradient-to-br from-purple-50 to-indigo-100 mb-3 overflow-hidden">
                <img
                  v-if="ui.item && ui.item.imatge"
                  :src="ui.item.imatge"
                  :alt="ui.item.nom"
                  class="w-24 h-24 object-contain"
                  decoding="async"
                  draggable="false"
                  @error="onImageError"
                />
                <span v-else class="text-4xl">🎩</span>
              </div>
              <h3 class="text-base font-bold text-gray-800">{{ ui.item ? ui.item.nom : '' }}</h3>
              <span
                v-if="ui.equipat"
                class="mt-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-bold border border-emerald-200"
              >
                {{ $t('inventory.equipped') }}
              </span>
              <button
                type="button"
                class="inv-action-btn mt-3"
                :class="ui.equipat ? 'inv-btn-secondary' : 'inv-btn-primary'"
                :disabled="processant === ui.id"
                @click="alternarEquipament(ui)"
              >
                <span v-if="processant === ui.id">…</span>
                <span v-else-if="ui.equipat">{{ $t('inventory.unequip') }}</span>
                <span v-else>{{ $t('inventory.equip') }}</span>
              </button>
            </article>
          </div>
        </section>

        <section v-if="consumibles.length > 0">
          <h2 class="text-lg font-bold text-gray-700 mb-3 px-1">{{ $t('inventory.consumables_section') }}</h2>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
            <article
              v-for="ui in consumibles"
              :key="ui.id"
              class="inv-card bento-card flex flex-col items-center text-center bg-white/95 backdrop-blur-md shadow-xl border border-white/60"
            >
              <div class="inv-card-img-wrapper flex items-center justify-center w-32 h-32 rounded-3xl bg-gradient-to-br from-amber-50 to-rose-100 mb-3 overflow-hidden">
                <img
                  v-if="ui.item && ui.item.imatge"
                  :src="ui.item.imatge"
                  :alt="ui.item.nom"
                  class="w-24 h-24 object-contain"
                  decoding="async"
                  draggable="false"
                  @error="onImageError"
                />
                <span v-else class="text-4xl">💊</span>
              </div>
              <h3 class="text-base font-bold text-gray-800">{{ ui.item ? ui.item.nom : '' }}</h3>
              <p v-if="ui.item && ui.item.descripcio" class="text-xs text-gray-500 mt-1 mb-2 px-2">{{ ui.item.descripcio }}</p>

              <button
                type="button"
                class="inv-action-btn inv-btn-primary mt-2"
                :disabled="!potUsarConsumible || processant === ui.id"
                :title="raoBlocaConsumible"
                @click="usarObjecte(ui)"
              >
                <span v-if="processant === ui.id">…</span>
                <span v-else>{{ $t('inventory.use') }}</span>
              </button>
              <p v-if="!potUsarConsumible" class="text-[11px] text-gray-400 mt-2">{{ raoBlocaConsumible }}</p>
            </article>
          </div>
        </section>
      </template>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useGameStore } from '~/stores/gameStore.js';
import { useShopStore } from '~/stores/useShopStore.js';

const gameStore = useGameStore();
const shopStore = useShopStore();
const { $swal } = useNuxtApp();
const { t } = useI18n();

const processant = ref(null);

const loading = computed(function () { return shopStore.loading; });
const skins = computed(function () { return shopStore.skins; });
const consumibles = computed(function () { return shopStore.consumibles; });

const potUsarConsumible = computed(function () {
  return gameStore.ratxa === 0 && gameStore.ratxaMaxima > 0;
});

const raoBlocaConsumible = computed(function () {
  if (gameStore.ratxaMaxima <= 0) {
    return t('inventory.use_disabled_no_max_streak');
  }
  if (gameStore.ratxa > 0) {
    return t('inventory.use_disabled_no_streak_loss');
  }
  return '';
});

function onImageError(event) {
  if (event && event.target) {
    event.target.style.display = 'none';
  }
}

async function alternarEquipament(ui) {
  if (!ui || processant.value !== null) {
    return;
  }
  processant.value = ui.id;
  try {
    await shopStore.equiparItem(ui.id);
  } catch (e) {
    await $swal.fire({
      icon: 'error',
      title: 'Error',
      text: e && e.message ? e.message : 'Error',
      confirmButtonColor: '#7c3aed'
    });
  } finally {
    processant.value = null;
  }
}

async function usarObjecte(ui) {
  if (!ui || processant.value !== null) {
    return;
  }
  if (!potUsarConsumible.value) {
    return;
  }
  processant.value = ui.id;
  try {
    await shopStore.usarConsumible(ui.id);
    await $swal.fire({
      icon: 'success',
      title: t('inventory.use_success_title'),
      text: t('inventory.use_success_text'),
      timer: 2000,
      showConfirmButton: false
    });
  } catch (e) {
    await $swal.fire({
      icon: 'error',
      title: 'Error',
      text: e && e.message ? e.message : 'Error',
      confirmButtonColor: '#7c3aed'
    });
  } finally {
    processant.value = null;
  }
}

onMounted(async function () {
  await shopStore.carregarBotiga();
});
</script>

<style scoped>
.inventari-page {
  background: linear-gradient(135deg, #f5f3ff 0%, #fdf2f8 100%);
}
.inv-card {
  border-radius: 1.5rem;
  padding: 1.25rem 1rem;
  transition: transform 0.2s ease;
}
.inv-card:hover {
  transform: translateY(-3px);
}
.inv-card-equipped {
  border: 2px solid #10b981 !important;
}
.inv-action-btn {
  font-weight: 800;
  padding: 0.55rem 1.25rem;
  border-radius: 1rem;
  border: none;
  cursor: pointer;
  font-size: 0.875rem;
  transition: transform 0.15s ease, box-shadow 0.15s ease, opacity 0.15s ease;
  letter-spacing: 0.02em;
}
.inv-btn-primary {
  background: linear-gradient(135deg, #8b5cf6, #6366f1);
  color: white;
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
}
.inv-btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(99, 102, 241, 0.45);
}
.inv-btn-secondary {
  background: #f3f4f6;
  color: #4b5563;
  border: 1px solid #e5e7eb;
}
.inv-btn-secondary:hover:not(:disabled) {
  background: #e5e7eb;
}
.inv-action-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  background: linear-gradient(135deg, #cbd5e1, #94a3b8);
  box-shadow: none;
}
</style>

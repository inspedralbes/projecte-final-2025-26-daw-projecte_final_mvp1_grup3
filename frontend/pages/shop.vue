<template>
  <div class="shop-page min-h-screen overflow-x-hidden pb-24 lg:pb-8 flex flex-col">
    <!-- Espai superior reservat: deixa veure la mascota del fons -->
    <div class="shop-spacer flex-1" aria-hidden="true"></div>

    <!-- Grid d'items: cada objecte en el seu propi card blanc. Es queda a la
         part inferior de la pàgina, just damunt de la franja verda perquè no
         tapi la mascota del fons. Sempre 2 per fila. -->
    <div class="shop-items-wrap max-w-5xl mx-auto px-3 sm:px-5 w-full">
      <section v-if="!loading && items.length > 0" class="grid grid-cols-2 gap-3 sm:gap-6">
        <article
          v-for="item in items"
          :key="item.id"
          class="shop-card flex flex-col items-center text-center bg-white shadow-xl border border-white"
        >
          <div class="shop-card-img-wrapper flex items-center justify-center w-20 h-20 sm:w-32 sm:h-32 rounded-2xl sm:rounded-3xl bg-gradient-to-br from-purple-50 to-indigo-100 mb-2 sm:mb-3 overflow-hidden">
            <img
              v-if="item.imatge"
              :src="item.imatge"
              :alt="item.nom"
              class="w-16 h-16 sm:w-24 sm:h-24 object-contain"
              decoding="async"
              draggable="false"
              @error="onImageError"
            />
            <span v-else class="text-3xl sm:text-4xl">🎁</span>
          </div>
          <h3 class="text-sm sm:text-lg font-bold text-gray-800 leading-tight">{{ item.nom }}</h3>
          <p v-if="item.descripcio" class="text-[11px] sm:text-xs text-gray-500 mt-1 mb-2 sm:mb-3 px-1 sm:px-2 line-clamp-2">{{ item.descripcio }}</p>
          <div class="flex items-center gap-1.5 mb-3 sm:mb-4">
            <img :src="coinIcon" alt="" class="w-4 h-4 sm:w-5 sm:h-5 object-contain coin-pixel" width="20" height="20" />
            <span class="text-sm sm:text-base font-black text-amber-700 tabular-nums">{{ item.preu }}</span>
          </div>

          <span
            v-if="item.tipus === 'skin' && shopStore.posseeixItem(item.id)"
            class="px-4 py-2 rounded-2xl bg-emerald-100 text-emerald-700 text-sm font-bold border border-emerald-200"
          >
            {{ $t('shop.owned') }}
          </span>
          <button
            v-else
            type="button"
            class="shop-buy-btn"
            :disabled="comprant === item.id || monedes < item.preu"
            @click="confirmarCompra(item)"
          >
            <span v-if="comprant === item.id">…</span>
            <span v-else-if="monedes < item.preu">{{ $t('shop.insufficient_funds') }}</span>
            <span v-else>{{ $t('shop.buy') }}</span>
          </button>
        </article>
      </section>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import coinIcon from '~/assets/img/Icones/Icona_Moneda.png';
import { useGameStore } from '~/stores/gameStore.js';
import { useShopStore } from '~/stores/useShopStore.js';

const gameStore = useGameStore();
const shopStore = useShopStore();
const { $swal } = useNuxtApp();
const { t } = useI18n();

const comprant = ref(null);

const items = computed(function () { return shopStore.items; });
const loading = computed(function () { return shopStore.loading; });
const monedes = computed(function () { return gameStore.monedes; });

function onImageError(event) {
  if (event && event.target) {
    event.target.style.display = 'none';
  }
}

async function confirmarCompra(item) {
  if (!item || comprant.value !== null) {
    return;
  }
  if (gameStore.monedes < item.preu) {
    return;
  }
  const result = await $swal.fire({
    title: t('shop.confirm_title'),
    text: t('shop.confirm_text', { price: item.preu }),
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: t('shop.confirm_yes'),
    cancelButtonText: t('shop.confirm_no'),
    confirmButtonColor: '#7c3aed',
    cancelButtonColor: '#9ca3af'
  });
  if (!result || !result.isConfirmed) {
    return;
  }
  comprant.value = item.id;
  try {
    const dades = await shopStore.comprarItem(item.id);
    if (dades && typeof dades.monedes === 'number') {
      gameStore.monedes = dades.monedes;
    }
    await $swal.fire({
      icon: 'success',
      title: t('shop.purchase_success_title'),
      text: item.nom,
      timer: 1500,
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
    comprant.value = null;
  }
}

onMounted(async function () {
  await shopStore.carregarBotiga();
});
</script>

<style scoped>
/* Espai superior calculat a partir de l'aspect ratio real de la imatge de fons
   (402x681 → ~1.694). Així els items aterren just damunt de la franja verda
   de la part inferior de la imatge, sense tapar la mascota. */
.shop-spacer {
  min-height: calc(100vw * 1.45);
}
@media (min-width: 640px) {
  .shop-spacer {
    min-height: calc(100vw * 1.1);
  }
}
@media (min-width: 1024px) {
  .shop-spacer {
    min-height: 55vh;
  }
}

.shop-items-wrap {
  position: relative;
  z-index: 2;
}

.shop-card {
  border-radius: 1.5rem;
  padding: 1.25rem 0.75rem;
  transition: transform 0.2s ease;
}
@media (min-width: 640px) {
  .shop-card {
    padding: 1.5rem 1rem;
  }
}
.shop-card:hover {
  transform: translateY(-3px);
}
.shop-buy-btn {
  background: linear-gradient(135deg, #8b5cf6, #6366f1);
  color: white;
  font-weight: 800;
  padding: 0.625rem 1.5rem;
  border-radius: 1rem;
  border: none;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
  transition: transform 0.15s ease, box-shadow 0.15s ease, opacity 0.15s ease;
  font-size: 0.875rem;
  letter-spacing: 0.02em;
}
.shop-buy-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(99, 102, 241, 0.45);
}
.shop-buy-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  background: linear-gradient(135deg, #cbd5e1, #94a3b8);
  box-shadow: none;
}
.coin-pixel {
  image-rendering: pixelated;
}
.balance-pill {
  flex-shrink: 0;
}
</style>

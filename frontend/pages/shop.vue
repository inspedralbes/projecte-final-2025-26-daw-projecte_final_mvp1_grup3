<template>
  <div class="shop-page min-h-screen overflow-x-hidden pb-24 lg:pb-8 flex flex-col">
    <div class="shop-spacer flex-1" aria-hidden="true"></div>

    <div class="shop-items-wrap w-full px-4 sm:px-6">
      <section v-if="!loading && items.length > 0" class="shop-grid">
        <article
          v-for="item in items"
          :key="item.id"
          class="shop-card"
          :class="{
            'shop-card--owned': itemJaPossessionat(item),
            'shop-card--clickable': potComprar(item),
            'shop-card--insufficient': !itemJaPossessionat(item) && monedes < item.preu,
          }"
          :role="potComprar(item) ? 'button' : undefined"
          :tabindex="potComprar(item) ? 0 : undefined"
          @click="onCardClick(item)"
          @keydown.enter.prevent="onCardClick(item)"
          @keydown.space.prevent="onCardClick(item)"
        >
          <div class="shop-card-price" aria-label="Preu">
            <span class="shop-card-price-value">{{ item.preu }}</span>
            <img :src="coinIcon" alt="" class="shop-card-coin" width="22" height="22" />
          </div>

          <div class="shop-card-visual">
            <img
                v-if="item.imatge"
                :src="item.imatge"
                :alt="nomProducte(item)"
                class="shop-card-img"
                decoding="async"
                draggable="false"
                @error="onImageError"
              />
            <span v-else class="shop-card-emoji">🎁</span>
          </div>
          <h3 class="shop-card-title">{{ nomProducte(item) }}</h3>

          <span v-if="itemJaPossessionat(item)" class="shop-card-owned">
            {{ $t('shop.owned') }}
          </span>
          <span v-else-if="comprant === item.id" class="shop-card-owned">…</span>
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
import { nomProducteBotiga } from '~/utils/shopItemI18n.js';

const gameStore = useGameStore();
const shopStore = useShopStore();
const { $swal } = useNuxtApp();
const { t, te } = useI18n();

function nomProducte(item) {
  return nomProducteBotiga(item, t, te);
}

const comprant = ref(null);

const items = computed(function () { return shopStore.items; });
const loading = computed(function () { return shopStore.loading; });
const monedes = computed(function () { return gameStore.monedes; });

function itemJaPossessionat(item) {
  return item.tipus === 'skin' && shopStore.posseeixItem(item.id);
}

function potComprar(item) {
  if (comprant.value !== null) {
    return false;
  }
  if (itemJaPossessionat(item)) {
    return false;
  }
  return monedes.value >= item.preu;
}

function onCardClick(item) {
  if (!potComprar(item)) {
    return;
  }
  confirmarCompra(item);
}

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
      text: nomProducte(item),
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

.shop-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  align-items: end;
  width: 100%;
  max-width: 100%;
  column-gap: 0.75rem;
}

.shop-card {
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: stretch;
  width: min(46vw, 11.6875rem);
  max-width: 11.6875rem;
  aspect-ratio: 187 / 134;
  padding: 0 0 0.5rem;
  background: #ffffff;
  border-radius: 0.875rem;
  border: none;
  box-shadow: none;
  text-align: center;
  transition: transform 0.2s ease;
  overflow: hidden;
}

.shop-card:nth-child(odd) {
  justify-self: start;
}

.shop-card:nth-child(even) {
  justify-self: end;
}

@media (min-width: 640px) {
  .shop-grid {
    column-gap: 1.25rem;
  }

  .shop-card {
    width: min(42vw, 11.6875rem);
    padding: 0 0 0.55rem;
    border-radius: 1rem;
  }
}

.shop-card--clickable {
  cursor: pointer;
}

.shop-card--clickable:hover {
  transform: translateY(-2px);
}

.shop-card--clickable:focus-visible {
  outline: 2px solid #6fbc58;
  outline-offset: 2px;
}

.shop-card--insufficient {
  cursor: not-allowed;
}

.shop-card--insufficient .shop-card-img {
  opacity: 0.55;
}

.shop-card-price {
  position: absolute;
  top: 0.55rem;
  right: 0.55rem;
  display: flex;
  align-items: center;
  gap: 0.25rem;
  z-index: 2;
}

.shop-card-price-value {
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 0.9375rem;
  font-weight: 800;
  line-height: 1;
  color: #1a1a1a;
  font-variant-numeric: tabular-nums;
}

@media (min-width: 640px) {
  .shop-card-price-value {
    font-size: 1rem;
  }
}

.shop-card-coin {
  width: 1.1rem;
  height: 1.1rem;
  object-fit: contain;
  flex-shrink: 0;
  image-rendering: pixelated;
}

.shop-card-visual {
  position: relative;
  display: flex;
  flex: 1 1 auto;
  align-items: center;
  justify-content: center;
  width: 100%;
  min-height: 0;
  margin: 0;
  padding: 0.2rem 0;
  overflow: hidden;
}

.shop-card-img {
  display: block;
  width: auto;
  height: auto;
  max-width: 82%;
  max-height: 100%;
  margin: 0 auto;
  object-fit: contain;
  object-position: center center;
}

.shop-card-emoji {
  font-size: 2.25rem;
  line-height: 1;
}

.shop-card-title {
  flex-shrink: 0;
  margin: 0;
  padding: 0.15rem 0.4rem 0.4rem;
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 0.9375rem;
  font-weight: 800;
  line-height: 1.15;
  color: #111111;
}

@media (min-width: 640px) {
  .shop-card-title {
    font-size: 1rem;
  }
}

.shop-card-owned {
  flex-shrink: 0;
  margin-top: 0.1rem;
  padding: 0 0.4rem 0.35rem;
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 0.6875rem;
  font-weight: 700;
  line-height: 1.2;
  color: #15803d;
}
</style>

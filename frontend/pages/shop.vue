<template>
  <div class="shop-page min-h-screen overflow-x-hidden pb-24 lg:pb-8 flex flex-col">
    <div class="shop-spacer flex-1" aria-hidden="true"></div>

    <div class="shop-items-wrap w-full">
      <div v-if="!loading && items.length > 0" class="shop-sections">
        <div
          v-for="bloc in blocsBotiga"
          :key="bloc.id"
          class="shop-section"
        >
          <div class="moment-divider moment-divider--shop" role="presentation">
            <span class="moment-divider__line" aria-hidden="true"></span>
            <span class="moment-divider__text">{{ $t(bloc.labelKey) }}</span>
            <span class="moment-divider__line" aria-hidden="true"></span>
          </div>

          <section class="shop-grid">
            <button
              v-for="item in bloc.items"
              :key="item.id"
              type="button"
              class="shop-card"
              :class="{
                'shop-card--owned': itemJaPossessionat(item),
                'shop-card--clickable': potInteractuar(item),
                'shop-card--insufficient': !itemJaPossessionat(item) && !teSaldo(item),
              }"
              :disabled="!potInteractuar(item)"
              @click="onCardClick(item)"
            >
          <div class="shop-card-price" aria-label="Preu">
            <span class="shop-card-price-value">{{ item.preu }}</span>
            <img :src="coinIcon" alt="" class="shop-card-coin" width="22" height="22" />
          </div>

          <div class="shop-card-row">
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

            <div class="shop-card-body">
              <h3 class="shop-card-title">{{ nomProducte(item) }}</h3>
              <span v-if="itemJaPossessionat(item)" class="shop-card-owned">
                {{ $t('shop.owned') }}
              </span>
              <span v-else-if="comprant === item.id" class="shop-card-owned">…</span>
            </div>
          </div>
            </button>
          </section>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import coinIcon from '~/assets/img/Icones/Icona_Moneda.png';
import { useGameStore } from '~/stores/gameStore.js';
import { useShopStore } from '~/stores/useShopStore.js';
import { nomProducteBotiga, categoriaProducteBotiga } from '~/utils/shopItemI18n.js';

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

const blocsBotiga = computed(function () {
  var ordre = ['fons', 'skin', 'inventari'];
  return ordre.map(function (id) {
    return {
      id: id,
      labelKey: 'shop.divider_' + id,
      items: items.value.filter(function (it) {
        return categoriaProducteBotiga(it) === id;
      }),
    };
  }).filter(function (bloc) {
    return bloc.items.length > 0;
  });
});

function itemJaPossessionat(item) {
  return item.tipus === 'skin' && shopStore.posseeixItem(item.id);
}

function preuItem(item) {
  return Number(item && item.preu != null ? item.preu : 0);
}

function teSaldo(item) {
  return monedes.value >= preuItem(item);
}

function potInteractuar(item) {
  if (comprant.value !== null || shopStore.loading) {
    return false;
  }
  return !itemJaPossessionat(item);
}

function onCardClick(item) {
  if (!potInteractuar(item)) {
    return;
  }
  if (!teSaldo(item)) {
    $swal.fire({
      icon: 'warning',
      title: t('shop.insufficient_funds'),
      confirmButtonColor: '#7c3aed',
    });
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
  if (gameStore.monedes < preuItem(item)) {
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
  flex-shrink: 0;
  min-height: calc(100vw * 1.45);
  pointer-events: none;
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

.shop-page {
  width: 100%;
  max-width: 100%;
}

.shop-items-wrap {
  position: relative;
  z-index: 5;
  box-sizing: border-box;
  width: 100%;
  max-width: 100%;
  padding-inline: 0.625rem;
}

@media (min-width: 640px) {
  .shop-items-wrap {
    padding-inline: 0.75rem;
  }
}

.shop-sections {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.shop-section {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.moment-divider--shop {
  margin-top: 0.25rem;
}

.moment-divider {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  width: 100%;
}

.moment-divider__text {
  flex-shrink: 0;
  color: #faf9f9;
  font-size: 15px;
  line-height: 1.2;
  white-space: nowrap;
}

.moment-divider__line {
  flex: 1 1 0;
  min-width: 0;
  height: 3px;
  background: #faf9f9;
  border-radius: 999px;
}

.shop-grid {
  display: flex;
  flex-direction: column;
  align-items: stretch;
  width: 100%;
  max-width: 100%;
  gap: 1rem;
}

.shop-card {
  position: relative;
  display: block;
  box-sizing: border-box;
  width: 100%;
  max-width: 100%;
  min-height: 5.375rem;
  padding: 0.75rem 0.875rem;
  padding-right: 3.25rem;
  background: #ffffff;
  border-radius: 0.875rem;
  border: none;
  box-shadow: none;
  text-align: left;
  font: inherit;
  color: inherit;
  appearance: none;
  -webkit-tap-highlight-color: transparent;
  touch-action: manipulation;
  transition: transform 0.2s ease;
  overflow: hidden;
}

.shop-card:disabled {
  cursor: default;
}

.shop-card-row {
  display: flex;
  flex-direction: row;
  align-items: center;
  gap: 0.75rem;
  width: 100%;
  min-width: 0;
}

@media (min-width: 640px) {
  .shop-grid {
    gap: 1.25rem;
  }

  .shop-card {
    min-height: 5.75rem;
    padding: 0.875rem 1rem;
    padding-right: 3.5rem;
    border-radius: 1rem;
  }

  .shop-card-row {
    gap: 1rem;
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

.shop-card--insufficient:not(:disabled) {
  cursor: pointer;
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
  display: flex;
  flex: 0 0 auto;
  align-items: center;
  justify-content: center;
  width: 3.5rem;
  height: 4.5rem;
  overflow: hidden;
}

.shop-card-img {
  display: block;
  width: 100%;
  height: 100%;
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
  object-position: center center;
}

.shop-card-emoji {
  font-size: 2rem;
  line-height: 1;
}

.shop-card-body {
  display: flex;
  flex: 1 1 auto;
  flex-direction: column;
  justify-content: center;
  align-items: flex-start;
  gap: 0.2rem;
  min-width: 0;
}

.shop-card-title {
  margin: 0;
  padding: 0;
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 0.9375rem;
  font-weight: 800;
  line-height: 1.2;
  color: #111111;
}

@media (min-width: 640px) {
  .shop-card-title {
    font-size: 1rem;
  }
}

.shop-card-owned {
  margin: 0;
  padding: 0;
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 0.6875rem;
  font-weight: 700;
  line-height: 1.2;
  color: #15803d;
}

@media (min-width: 640px) {
  .shop-card-visual {
    width: 3.75rem;
    height: 4.75rem;
  }
}
</style>

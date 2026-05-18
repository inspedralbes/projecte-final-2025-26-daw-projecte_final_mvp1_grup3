<template>
  <div class="shop-page min-h-screen overflow-x-hidden pb-24 lg:pb-8 flex flex-col">
    <div class="shop-spacer flex-1" aria-hidden="true"></div>

    <div class="shop-items-wrap w-full px-4 sm:px-6">
      <div v-if="!loading && items.length > 0" class="shop-sections-container">
        
        <!-- Categoría Inventario (Consumibles) -->
        <section v-if="consumibles.length > 0" class="shop-category-section">
          <div class="moment-divider mt-1 mb-4" role="presentation">
            <span class="moment-divider__line" aria-hidden="true"></span>
            <span class="moment-divider__text divider-text--capitalize">{{ $t('shop.divider_inventari') }}</span>
            <span class="moment-divider__line" aria-hidden="true"></span>
          </div>
          <div class="shop-grid">
            <article
              v-for="item in consumibles"
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
              <div class="template-card__mark" aria-hidden="true">
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

              <div class="template-card__content">
                <h3 class="template-card__title">{{ nomProducte(item) }}</h3>
                <div class="template-card__meta">
                  <span v-if="itemJaPossessionat(item)" class="shop-card-owned flex items-center gap-1 text-green-700 font-bold">
                    <svg width="14" height="10" viewBox="0 0 14 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path d="M1 5L4.5 8.5L13 1.5" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    {{ $t('shop.owned') }}
                  </span>
                  <span v-else-if="comprant === item.id" class="shop-card-owned">…</span>
                  <span v-else class="template-card__meta-item flex items-center">
                    <span class="shop-card-price-value text-gray-800 font-bold mr-1">{{ item.preu }}</span>
                    <img :src="coinIcon" alt="" class="shop-card-coin" width="18" height="18" />
                  </span>
                </div>
              </div>
            </article>
          </div>
        </section>

        <!-- Categoría skin (Skins excepto fondos) -->
        <section v-if="skins.length > 0" class="shop-category-section">
          <div class="moment-divider mt-1 mb-4" role="presentation">
            <span class="moment-divider__line" aria-hidden="true"></span>
            <span class="moment-divider__text divider-text--lowercase">{{ $t('shop.divider_skin') }}</span>
            <span class="moment-divider__line" aria-hidden="true"></span>
          </div>
          <div class="shop-grid">
            <article
              v-for="item in skins"
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
              <div class="template-card__mark" aria-hidden="true">
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

              <div class="template-card__content">
                <h3 class="template-card__title">{{ nomProducte(item) }}</h3>
                <div class="template-card__meta">
                  <span v-if="itemJaPossessionat(item)" class="shop-card-owned flex items-center gap-1 text-green-700 font-bold">
                    <svg width="14" height="10" viewBox="0 0 14 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path d="M1 5L4.5 8.5L13 1.5" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    {{ $t('shop.owned') }}
                  </span>
                  <span v-else-if="comprant === item.id" class="shop-card-owned">…</span>
                  <span v-else class="template-card__meta-item flex items-center">
                    <span class="shop-card-price-value text-gray-800 font-bold mr-1">{{ item.preu }}</span>
                    <img :src="coinIcon" alt="" class="shop-card-coin" width="18" height="18" />
                  </span>
                </div>
              </div>
            </article>
          </div>
        </section>

        <!-- Categoría fondos -->
        <section v-if="fondos.length > 0" class="shop-category-section">
          <div class="moment-divider mt-1 mb-4" role="presentation">
            <span class="moment-divider__line" aria-hidden="true"></span>
            <span class="moment-divider__text divider-text--lowercase">{{ $t('shop.divider_fons') }}</span>
            <span class="moment-divider__line" aria-hidden="true"></span>
          </div>
          <div class="shop-grid">
            <article
              v-for="item in fondos"
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
              <div class="template-card__mark" aria-hidden="true">
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

              <div class="template-card__content">
                <h3 class="template-card__title">{{ nomProducte(item) }}</h3>
                <div class="template-card__meta">
                  <span v-if="itemJaPossessionat(item)" class="shop-card-owned flex items-center gap-1 text-green-700 font-bold">
                    <svg width="14" height="10" viewBox="0 0 14 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path d="M1 5L4.5 8.5L13 1.5" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    {{ $t('shop.owned') }}
                  </span>
                  <span v-else-if="comprant === item.id" class="shop-card-owned">…</span>
                  <span v-else class="template-card__meta-item flex items-center">
                    <span class="shop-card-price-value text-gray-800 font-bold mr-1">{{ item.preu }}</span>
                    <img :src="coinIcon" alt="" class="shop-card-coin" width="18" height="18" />
                  </span>
                </div>
              </div>
            </article>
          </div>
        </section>

      </div>
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

// Computed properties para separar por categorías
const consumibles = computed(function () {
  return items.value.filter(function (it) {
    return it && it.tipus === 'consumible';
  });
});

const skins = computed(function () {
  return items.value.filter(function (it) {
    return it && it.tipus === 'skin' && (!it.metadata || (it.metadata.slot !== 'fons' && it.metadata.slot !== 'fondo'));
  });
});

const fondos = computed(function () {
  return items.value.filter(function (it) {
    return it && it.tipus === 'skin' && it.metadata && (it.metadata.slot === 'fons' || it.metadata.slot === 'fondo');
  });
});

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

.shop-sections-container {
  display: flex;
  flex-direction: column;
  gap: 2rem;
  width: 100%;
}

.shop-category-section {
  display: flex;
  flex-direction: column;
  align-items: center;
  width: 100%;
}

/* Divisor de categorías idéntico al de Plantilles.vue */
.moment-divider {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 30px;
  width: 100%;
  margin: 1.5rem 0 1.25rem;
}

.moment-divider__text {
  flex-shrink: 0;
  color: #faf9f9;
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 15px;
  font-weight: 700;
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

.divider-text--capitalize {
  text-transform: capitalize;
}

.divider-text--lowercase {
  text-transform: lowercase;
}

/* Grid de una sola columna y centrado */
.shop-grid {
  display: flex;
  flex-direction: column;
  align-items: center;
  width: 100%;
  max-width: 32rem;
  margin: 0 auto;
  row-gap: 1rem;
}

/* Tarjeta horizontal idéntica a .template-card de Plantilles.vue */
.shop-card {
  position: relative;
  display: grid;
  grid-template-columns: 57px minmax(0, 1fr);
  column-gap: 23px;
  align-items: center;
  width: 100%;
  min-height: 86px;
  padding: 16px 18px;
  background-color: #faf9f9;
  border-radius: 10px;
  border: none;
  box-shadow: none;
  transition: transform 0.2s ease, background-color 0.2s ease;
  text-align: left;
  overflow: hidden;
}

.shop-card--clickable {
  cursor: pointer;
}

.shop-card--clickable:hover {
  transform: translateY(-2px);
  background-color: #f3f2f2;
}

.shop-card--clickable:focus-visible {
  outline: 2px solid #79d45d;
  outline-offset: 2px;
}

.shop-card--insufficient {
  cursor: not-allowed;
  opacity: 0.85;
}

.shop-card--insufficient:hover {
  transform: none;
  background-color: #faf9f9;
}

.shop-card--insufficient .shop-card-img {
  opacity: 0.55;
}

/* Contenedor de la Imagen */
.template-card__mark {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 57px;
  height: 54px;
}

.shop-card-img {
  display: block;
  width: auto;
  height: auto;
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
}

.shop-card-emoji {
  font-size: 1.75rem;
  line-height: 1;
}

/* Contenido del lado derecho */
.template-card__content {
  display: flex;
  flex-direction: column;
  justify-content: center;
  gap: 6px;
}

.template-card__title {
  margin: 0;
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 18px;
  font-weight: 700;
  line-height: 1.15;
  color: #2b2d42;
}

@media (min-width: 640px) {
  .template-card__title {
    font-size: 20px;
  }
}

.template-card__meta {
  display: flex;
  align-items: center;
  gap: 16px;
  color: #707070;
  font-size: 13px;
  font-weight: 600;
  line-height: 1;
}

.template-card__meta-item {
  display: inline-flex;
  align-items: center;
  color: #707070;
  line-height: 1;
}

.shop-card-price-value {
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 0.9375rem;
  font-weight: 800;
  line-height: 1;
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

.shop-card-owned {
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 0.8125rem;
  line-height: 1;
}
</style>

<template>
  <div class="shop-page min-h-screen overflow-x-hidden pb-24 lg:pb-8 flex flex-col">
    <div class="shop-icons-row">
      <span class="shop-icons-row__spacer"></span>
      <button type="button" ref="inventariIconRef" class="shop-icon-btn" @click="navigateTo('/inventari')" title="Inventari">
        <img :src="inventariIcon" alt="Inventari" class="shop-icon-btn__img" />
      </button>
    </div>

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

    <Teleport to="body">
      <Transition name="shop-sheet-backdrop">
        <div v-if="sheetItem" class="shop-sheet-overlay" @click.self="tancarSheet"></div>
      </Transition>
      <Transition name="shop-sheet-panel">
        <div v-if="sheetItem" class="shop-sheet">
          <div class="shop-sheet__handle"><div class="shop-sheet__bar"></div></div>
          <div class="shop-sheet__body">
            <div class="shop-sheet__img-wrap">
              <img v-if="sheetItem.imatge" :src="sheetItem.imatge" class="shop-sheet__img" />
              <span v-else class="shop-sheet__emoji">🎁</span>
            </div>
            <h3 class="shop-sheet__name">{{ nomProducte(sheetItem) }}</h3>

            <div v-if="sheetItem.tipus !== 'skin'" class="shop-sheet__qty-row">
              <button type="button" class="shop-sheet__qty-btn" :disabled="sheetQty <= 1" @click="sheetQty--">−</button>
              <span class="shop-sheet__qty-val">{{ sheetQty }}</span>
              <button type="button" class="shop-sheet__qty-btn" @click="sheetQty++">+</button>
            </div>

            <div class="shop-sheet__total-row">
              <span class="shop-sheet__total-label">Total:</span>
              <span class="shop-sheet__total-value">{{ sheetTotal }}</span>
              <img :src="coinIcon" alt="" class="shop-sheet__total-coin" />
            </div>

            <div class="shop-sheet__actions">
              <button type="button" class="shop-sheet__btn shop-sheet__btn--cancel" @click="tancarSheet">Enrere</button>
              <button
                type="button"
                class="shop-sheet__btn shop-sheet__btn--buy"
                :disabled="monedes < sheetTotal || comprant !== null"
                @click="executarCompra"
              >
                {{ comprant ? '...' : 'Comprar' }}
              </button>
            </div>
            <p v-if="monedes < sheetTotal" class="shop-sheet__insufficient">No tens prou monedes</p>
          </div>
        </div>
      </Transition>
    </Teleport>

    <div v-if="animatingImg" class="shop-fly-img" :style="flyStyle">
      <img :src="animatingImg" class="shop-fly-img__inner" />
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, nextTick } from 'vue';
import coinIcon from '~/assets/img/Icones/Icona_Moneda.png';
import inventariIcon from '~/assets/img/Icones/Icona_Inventari.png';
import { useGameStore } from '~/stores/gameStore.js';
import { useShopStore } from '~/stores/useShopStore.js';
import { nomProducteBotiga } from '~/utils/shopItemI18n.js';

const gameStore = useGameStore();
const shopStore = useShopStore();
const { t, te } = useI18n();

function nomProducte(item) {
  return nomProducteBotiga(item, t, te);
}

const comprant = ref(null);
const sheetItem = ref(null);
const sheetQty = ref(1);
const inventariIconRef = ref(null);
const itemImgRefs = reactive({});
const animatingImg = ref(null);
const flyStyle = ref({});

const items = computed(function () { return shopStore.items; });
const loading = computed(function () { return shopStore.loading; });
const monedes = computed(function () { return gameStore.monedes; });

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

const sheetTotal = computed(function () {
  if (!sheetItem.value) return 0;
  return sheetItem.value.preu * sheetQty.value;
});

function itemJaPossessionat(item) {
  return item.tipus === 'skin' && shopStore.posseeixItem(item.id);
}

function onCardClick(item) {
  if (itemJaPossessionat(item)) return;
  sheetItem.value = item;
  sheetQty.value = 1;
}

function tancarSheet() {
  sheetItem.value = null;
  sheetQty.value = 1;
}

function onImageError(event) {
  if (event && event.target) {
    event.target.style.display = 'none';
  }
}

async function executarCompra() {
  if (!sheetItem.value || comprant.value !== null) return;
  if (gameStore.monedes < sheetTotal.value) return;

  var item = sheetItem.value;
  var qty = sheetQty.value;
  comprant.value = item.id;

  try {
    for (var i = 0; i < qty; i++) {
      var dades = await shopStore.comprarItem(item.id);
      if (dades && typeof dades.monedes === 'number') {
        gameStore.monedes = dades.monedes;
      }
    }
    tancarSheet();
    await nextTick();
    startFlyAnimation(item);
  } catch (e) {
    alert(e && e.message ? e.message : 'Error');
  } finally {
    comprant.value = null;
  }
}

function startFlyAnimation(item) {
  if (!item.imatge) return;
  var imgEl = itemImgRefs[item.id];
  var iconEl = inventariIconRef.value;
  if (!imgEl || !iconEl) return;

  var imgRect = imgEl.getBoundingClientRect();
  var iconRect = iconEl.getBoundingClientRect();

  animatingImg.value = item.imatge;
  flyStyle.value = {
    position: 'fixed',
    top: imgRect.top + 'px',
    left: imgRect.left + 'px',
    width: imgRect.width + 'px',
    height: imgRect.height + 'px',
    zIndex: '99999',
    transition: 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)',
    opacity: '1',
    pointerEvents: 'none',
  };

  requestAnimationFrame(function () {
    requestAnimationFrame(function () {
      flyStyle.value = {
        position: 'fixed',
        top: iconRect.top + iconRect.height / 2 - 10 + 'px',
        left: iconRect.left + iconRect.width / 2 - 10 + 'px',
        width: '20px',
        height: '20px',
        zIndex: '99999',
        transition: 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)',
        opacity: '0',
        pointerEvents: 'none',
      };
    });
  });

  setTimeout(function () {
    animatingImg.value = null;
    flyStyle.value = {};
  }, 650);
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

.shop-card--owned {
  background: #79d45d;
  border: 2px solid #6bc24d;
}

.shop-card--owned .shop-card-title {
  color: #faf9f9;
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
  color: rgba(250, 249, 249, 0.9);
}

.shop-card-tick {
  position: absolute;
  top: 0.5rem;
  left: 0.5rem;
  z-index: 3;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: #faf9f9;
  color: #79d45d;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  font-weight: 800;
  line-height: 1;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.15);
}

.shop-icons-row {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  width: 100%;
  padding: 4px 12px 0;
}

.shop-icons-row__spacer {
  flex: 1;
}

.shop-icon-btn {
  width: 4rem;
  height: 4rem;
  flex-shrink: 0;
  border: none;
  background: transparent;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: transform 0.15s;
}
.shop-icon-btn:hover {
  transform: scale(1.05);
}
.shop-icon-btn:active {
  transform: scale(0.95);
}
.shop-icon-btn__img {
  max-width: 100%;
  max-height: 100%;
  width: auto;
  height: auto;
  object-fit: contain;
}

.shop-sheet-overlay {
  position: fixed;
  inset: 0;
  z-index: 9998;
  background: rgba(0,0,0,0.4);
}

.shop-sheet {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  z-index: 9999;
  background: #FFF8E1;
  border-radius: 24px 24px 0 0;
  box-shadow: 0 -4px 30px rgba(0,0,0,0.15);
  overflow: hidden;
  animation: shop-sheet-up 0.3s ease;
}

.shop-sheet__handle {
  display: flex;
  justify-content: center;
  padding: 14px 0 8px;
}
.shop-sheet__bar {
  width: 48px;
  height: 5px;
  background: #E6B800;
  border-radius: 4px;
}

.shop-sheet__body {
  padding: 0 24px 36px;
  text-align: center;
}

.shop-sheet__img-wrap {
  width: 140px;
  height: 140px;
  margin: 0 auto 16px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.shop-sheet__img {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
}
.shop-sheet__emoji {
  font-size: 2.5rem;
}

.shop-sheet__name {
  margin: 0 0 14px;
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 20px;
  font-weight: 700;
  color: #1a1a1a;
}

.shop-sheet__qty-row {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 16px;
  margin-bottom: 14px;
}
.shop-sheet__qty-btn {
  width: 36px;
  height: 36px;
  border: 2px solid #E6B800;
  border-radius: 50%;
  background: #fff;
  font-size: 20px;
  font-weight: 700;
  color: #E6B800;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.15s;
}
.shop-sheet__qty-btn:hover:not(:disabled) {
  background: #FFF8E1;
}
.shop-sheet__qty-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}
.shop-sheet__qty-val {
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 24px;
  font-weight: 800;
  color: #1a1a1a;
  min-width: 30px;
}

.shop-sheet__total-row {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  margin-bottom: 18px;
}
.shop-sheet__total-label {
  font-family: "Comfortaa", system-ui, sans-serif;
  font-size: 14px;
  font-weight: 600;
  color: #6b7280;
}
.shop-sheet__total-value {
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 22px;
  font-weight: 800;
  color: #1a1a1a;
}
.shop-sheet__total-coin {
  width: 20px;
  height: 20px;
  object-fit: contain;
}

.shop-sheet__actions {
  display: flex;
  gap: 10px;
}
.shop-sheet__btn {
  flex: 1;
  border: none;
  border-radius: 12px;
  padding: 13px 10px;
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 15px;
  font-weight: 700;
  cursor: pointer;
  transition: filter 0.15s;
}
.shop-sheet__btn--buy {
  background: #E6B800;
  color: #fff;
}
.shop-sheet__btn--buy:hover:not(:disabled) {
  filter: brightness(0.93);
}
.shop-sheet__btn--buy:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
.shop-sheet__btn--cancel {
  background: #f3f4f6;
  color: #6b7280;
}
.shop-sheet__btn--cancel:hover {
  background: #e5e7eb;
}

.shop-sheet__insufficient {
  margin: 10px 0 0;
  font-family: "Comfortaa", system-ui, sans-serif;
  font-size: 12px;
  font-weight: 600;
  color: #dc2626;
}

.shop-fly-img {
  pointer-events: none;
}
.shop-fly-img__inner {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.shop-sheet-backdrop-enter-active,
.shop-sheet-backdrop-leave-active {
  transition: opacity 0.25s;
}
.shop-sheet-backdrop-enter-from,
.shop-sheet-backdrop-leave-to {
  opacity: 0;
}

.shop-sheet-panel-enter-active {
  animation: shop-sheet-up 0.3s ease;
}
.shop-sheet-panel-leave-active {
  animation: shop-sheet-up 0.2s ease reverse;
}

@keyframes shop-sheet-up {
  from { transform: translateY(100%); }
  to { transform: translateY(0); }
}
</style>

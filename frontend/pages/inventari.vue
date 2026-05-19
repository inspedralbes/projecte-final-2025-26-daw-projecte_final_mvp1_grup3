<!--
  Component o pagina Nuxt: inventari.
  Comentaris de codi: agents/frontend/AgentNuxt.md + AgentJavascript.md
-->
<template>
  <div class="inventari-page" :class="{ 'inventari-page--fons-platja': fonsPlatjaActiu }">
    <header class="inventari-page__topbar">
      <button
        type="button"
        class="inventari-page__icon-btn"
        aria-label="Tornar enrere"
        @click="tornar"
      >
        <svg
          class="inventari-page__chevron"
          width="73"
          height="73"
          viewBox="0 0 73 73"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
          aria-hidden="true"
        >
          <path
            d="M42.5834 54.75L24.3334 36.5L42.5834 18.25L46.8417 22.5083L32.85 36.5L46.8417 50.4917L42.5834 54.75Z"
            fill="currentColor"
          />
        </svg>
      </button>

      <h1 class="inventari-page__title">{{ $t('inventory.title') }}</h1>

      <div class="inventari-page__topbar-spacer" aria-hidden="true" />
    </header>

    <p class="inventari-page__subtitle">{{ $t('inventory.subtitle') }}</p>

    <div class="inventari-page__content">
      <section v-if="loading" class="inventari-page__loading">
        {{ $t('inventory.loading') }}
      </section>

      <section v-else-if="skins.length === 0 && consumibles.length === 0 && fonsItems.length === 0" class="inventari-page__empty">
        <p class="inventari-page__empty-text">{{ $t('inventory.empty') }}</p>
        <NuxtLink to="/shop" class="inventari-page__shop-btn">
          {{ $t('inventory.go_to_shop') }}
        </NuxtLink>
      </section>

      <template v-else>
        <section v-if="skins.length > 0" class="inventari-page__section">
          <div class="inventari-divider">
            <span class="inventari-divider__line"></span>
            <span class="inventari-divider__text">{{ $t('inventory.skins_section') }}</span>
            <span class="inventari-divider__line"></span>
          </div>
          <div class="inventari-page__grid">
            <article
              v-for="ui in skins"
              :key="ui.id"
              class="inventari-card"
              :class="{ 'inventari-card--equipped': ui.equipat }"
            >
              <div class="inventari-card__visual inventari-card__visual--skin">
                <img
                  v-if="ui.item && ui.item.imatge"
                  :src="ui.item.imatge"
                  :alt="nomProducte(ui.item)"
                  class="inventari-card__img"
                  decoding="async"
                  draggable="false"
                  @error="onImageError"
                />
                <span v-else class="inventari-card__emoji">🎩</span>
              </div>
              <h3 class="inventari-card__name">{{ ui.item ? nomProducte(ui.item) : '' }}</h3>
              <span
                v-if="ui.equipat"
                class="inventari-card__badge"
              >
                {{ $t('inventory.equipped') }}
              </span>
              <button
                type="button"
                class="inventari-card__btn"
                :class="ui.equipat ? 'inventari-card__btn--secondary' : 'inventari-card__btn--primary'"
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

        <section v-if="consumibles.length > 0" class="inventari-page__section">
          <div class="inventari-divider">
            <span class="inventari-divider__line"></span>
            <span class="inventari-divider__text">{{ $t('inventory.consumables_section') }}</span>
            <span class="inventari-divider__line"></span>
          </div>
          <div class="inventari-page__grid">
            <article
              v-for="ui in consumibles"
              :key="ui.id"
              class="inventari-card"
            >
              <div class="inventari-card__visual inventari-card__visual--consumable">
                <img
                  v-if="ui.item && ui.item.imatge"
                  :src="ui.item.imatge"
                  :alt="nomProducte(ui.item)"
                  class="inventari-card__img"
                  decoding="async"
                  draggable="false"
                  @error="onImageError"
                />
                <span v-else class="inventari-card__emoji">💊</span>
              </div>
              <h3 class="inventari-card__name">{{ ui.item ? nomProducte(ui.item) : '' }}</h3>
              <p v-if="ui.item && ui.item.descripcio" class="inventari-card__desc">{{ ui.item.descripcio }}</p>

              <button
                type="button"
                class="inventari-card__btn inventari-card__btn--primary"
                :disabled="!potUsarConsumible || processant === ui.id"
                :title="raoBlocaConsumible"
                @click="usarObjecte(ui)"
              >
                <span v-if="processant === ui.id">…</span>
                <span v-else>{{ $t('inventory.use') }}</span>
              </button>
              <p v-if="!potUsarConsumible" class="inventari-card__hint">{{ raoBlocaConsumible }}</p>
            </article>
          </div>
        </section>

        <section v-if="fonsItems.length > 0" class="inventari-page__section">
          <div class="inventari-divider">
            <span class="inventari-divider__line"></span>
            <span class="inventari-divider__text">{{ $t('inventory.backgrounds_section') }}</span>
            <span class="inventari-divider__line"></span>
          </div>
          <div class="inventari-page__grid">
            <article
              v-for="ui in fonsItems"
              :key="ui.id"
              class="inventari-card"
              :class="{ 'inventari-card--equipped': ui.equipat }"
            >
              <div class="inventari-card__visual inventari-card__visual--skin">
                <img
                  v-if="ui.item && ui.item.imatge"
                  :src="ui.item.imatge"
                  :alt="nomProducte(ui.item)"
                  class="inventari-card__img"
                  decoding="async"
                  draggable="false"
                  @error="onImageError"
                />
                <span v-else class="inventari-card__emoji">🖼️</span>
              </div>
              <h3 class="inventari-card__name">{{ ui.item ? nomProducte(ui.item) : '' }}</h3>
              <span
                v-if="ui.equipat"
                class="inventari-card__badge"
              >
                {{ $t('inventory.equipped') }}
              </span>
              <button
                type="button"
                class="inventari-card__btn"
                :class="ui.equipat ? 'inventari-card__btn--secondary' : 'inventari-card__btn--primary'"
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
      </template>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
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

const processant = ref(null);

/** Mateix to que la botiga (#2b2d42) quan el fons platja està equipat */
const fonsPlatjaActiu = computed(function () {
  var fonsKey = shopStore.fonsEquipat || gameStore.fonsKey;
  return fonsKey === 'fons_platja';
});

const loading = computed(function () { return shopStore.loading; });
const skins = computed(function () { return shopStore.skins; });
const consumibles = computed(function () { return shopStore.consumibles; });
const fonsItems = computed(function () { return shopStore.fonsItems; });

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

function tornar() {
  navigateTo('/home');
}

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
  min-height: 100vh;
  box-sizing: border-box;
  padding: 18px 16px calc(32px + env(safe-area-inset-bottom, 0px));
  background: transparent;
  color: #1f2937;
  max-width: 430px;
  margin: 0 auto;
}

.inventari-page__topbar {
  display: grid;
  grid-template-columns: 44px 1fr 44px;
  align-items: center;
  width: 100%;
  margin-bottom: 12px;
}

.inventari-page__icon-btn {
  width: 44px;
  height: 44px;
  padding: 0;
  border: none;
  border-radius: 50%;
  background: transparent;
  color: #faf9f9;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.inventari-page__icon-btn:focus-visible {
  outline: 2px solid #79d45d;
  outline-offset: 2px;
}

.inventari-page__chevron {
  width: 32px;
  height: 32px;
  display: block;
  filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.35));
}

.inventari-page__title {
  margin: 0;
  text-align: center;
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-weight: 600;
  font-size: 36px;
  line-height: 1.1;
  letter-spacing: -0.02em;
  color: #faf9f9;
  text-shadow: 0 1px 3px rgba(0, 0, 0, 0.35);
}

.inventari-page__topbar-spacer {
  width: 44px;
  height: 44px;
}

.inventari-page__subtitle {
  margin: 0 0 20px;
  text-align: center;
  font-family: "Comfortaa", system-ui, sans-serif;
  font-weight: 600;
  font-size: 13px;
  color: rgba(250, 249, 249, 0.75);
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.25);
}

.inventari-page__content {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.inventari-page__loading {
  text-align: center;
  font-family: "Comfortaa", system-ui, sans-serif;
  font-weight: 600;
  font-size: 15px;
  color: #faf9f9;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.35);
  padding: 2rem 0;
}

.inventari-page__empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
  padding: 2.5rem 1rem;
  border-radius: 1.5rem;
  background: rgba(255, 255, 255, 0.92);
  backdrop-filter: blur(8px);
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.06);
}

.inventari-page__empty-text {
  margin: 0;
  font-family: "Comfortaa", system-ui, sans-serif;
  font-size: 15px;
  font-weight: 600;
  color: #6b7280;
  text-align: center;
}

.inventari-page__shop-btn {
  display: inline-block;
  padding: 0.65rem 1.5rem;
  border-radius: 9999px;
  font-family: "Comfortaa", system-ui, sans-serif;
  font-weight: 700;
  font-size: 0.875rem;
  color: #568039;
  text-decoration: none;
  background: rgba(236, 252, 203, 0.7);
  border: 1px solid rgba(134, 239, 172, 0.5);
  transition: background 0.15s ease, transform 0.15s ease;
}
.inventari-page__shop-btn:hover {
  background: rgba(220, 252, 231, 0.95);
  transform: translateY(-1px);
}

.inventari-page__section {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.inventari-divider {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 30px;
  width: 100%;
  margin-bottom: 4px;
}

.inventari-divider__text {
  flex-shrink: 0;
  color: #faf9f9;
  font-family: "Comfortaa", system-ui, sans-serif;
  font-size: 15px;
  line-height: 1.2;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
}

.inventari-divider__line {
  flex: 1 1 0;
  min-width: 0;
  height: 3px;
  background: #faf9f9;
  border-radius: 999px;
}

.inventari-page__grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

.inventari-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  padding: 1rem 0.75rem 0.85rem;
  border-radius: 0.875rem;
  background: #ffffff;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  transition: transform 0.2s ease;
  border: 2px solid transparent;
  overflow: hidden;
}
.inventari-card:hover {
  transform: translateY(-2px);
}

.inventari-card--equipped {
  background: #79d45d;
  border-color: #6bc24d;
}
.inventari-card--equipped .inventari-card__name {
  color: #faf9f9;
}

.inventari-card__visual {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 5.5rem;
  height: 5.5rem;
  border-radius: 1.25rem;
  margin-bottom: 0.5rem;
  overflow: hidden;
}
.inventari-card__visual--skin {
  background: linear-gradient(135deg, #f3e8ff 0%, #e0e7ff 100%);
}
.inventari-card__visual--consumable {
  background: linear-gradient(135deg, #fffbeb 0%, #ffe4e6 100%);
}

.inventari-card__img {
  width: 4rem;
  height: 4rem;
  object-fit: contain;
}

.inventari-card__emoji {
  font-size: 2rem;
  line-height: 1;
}

.inventari-card__name {
  margin: 0;
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 0.875rem;
  font-weight: 700;
  line-height: 1.2;
  color: #1a1a1a;
}

.inventari-card__desc {
  margin: 4px 0 6px;
  font-family: "Comfortaa", system-ui, sans-serif;
  font-size: 0.6875rem;
  font-weight: 400;
  color: #6b7280;
  line-height: 1.3;
}
.inventari-card--equipped .inventari-card__desc {
  color: rgba(250, 249, 249, 0.8);
}

.inventari-card__badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  margin-top: 6px;
  padding: 3px 12px;
  border-radius: 9999px;
  font-family: "Comfortaa", system-ui, sans-serif;
  font-size: 0.625rem;
  font-weight: 700;
  color: #79d45d;
  background: #faf9f9;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12);
}

.inventari-card__btn {
  margin-top: 8px;
  padding: 0.45rem 1.1rem;
  border: none;
  border-radius: 9999px;
  font-family: "Comfortaa", system-ui, sans-serif;
  font-weight: 700;
  font-size: 0.75rem;
  cursor: pointer;
  transition: transform 0.15s ease, box-shadow 0.15s ease, opacity 0.15s ease;
  letter-spacing: 0.02em;
}

.inventari-card__btn--primary {
  background-color: #79d45d;
  color: #fff;
  border: 2px solid #6fbc58;
  box-shadow: 0 3px 0 0 #6fbc58;
}
.inventari-card__btn--primary:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 4px 0 0 #6fbc58;
}
.inventari-card__btn--primary:active:not(:disabled) {
  transform: translateY(1px);
  box-shadow: 0 1px 0 0 #6fbc58;
}

.inventari-card__btn--secondary {
  background: #faf9f9;
  color: #4b5563;
  border: 2px solid #e5e7eb;
  box-shadow: 0 2px 0 0 #d1d5db;
}
.inventari-card__btn--secondary:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 3px 0 0 #d1d5db;
}
.inventari-card__btn--secondary:active:not(:disabled) {
  transform: translateY(1px);
  box-shadow: 0 0px 0 0 #d1d5db;
}

.inventari-card__btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  background: #cbd5e1;
  color: #64748b;
  border-color: #94a3b8;
  box-shadow: none;
}

.inventari-card__hint {
  margin: 6px 0 0;
  font-family: "Comfortaa", system-ui, sans-serif;
  font-size: 0.625rem;
  font-weight: 500;
  color: rgba(250, 249, 249, 0.6);
}

/* Fons platja equipat: capçalera i separadors al blau fosc (com la botiga) */
.inventari-page--fons-platja .inventari-page__icon-btn {
  color: #2b2d42;
}

.inventari-page--fons-platja .inventari-page__title,
.inventari-page--fons-platja .inventari-page__subtitle,
.inventari-page--fons-platja .inventari-page__loading {
  color: #2b2d42;
  text-shadow: none;
}

.inventari-page--fons-platja .inventari-page__subtitle {
  color: rgba(43, 45, 66, 0.75);
}

.inventari-page--fons-platja .inventari-divider__text {
  color: #2b2d42;
  text-shadow: none;
}

.inventari-page--fons-platja .inventari-divider__line {
  background: #2b2d42;
}

.inventari-page--fons-platja .inventari-card__hint {
  color: rgba(43, 45, 66, 0.65);
}

@media (max-width: 380px) {
  .inventari-page__title {
    font-size: clamp(24px, 9vw, 36px);
  }
  .inventari-page__grid {
    gap: 8px;
  }
  .inventari-card {
    padding: 0.75rem 0.5rem 0.65rem;
  }
  .inventari-card__visual {
    width: 4.5rem;
    height: 4.5rem;
  }
  .inventari-card__img {
    width: 3.25rem;
    height: 3.25rem;
  }
}
</style>

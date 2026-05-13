<template>
  <div class="home-page-root perfil-page relative w-full min-h-screen pb-24 lg:pb-12 overflow-y-auto">
    <div class="max-w-7xl mx-auto px-3 sm:px-6 flex flex-col gap-8 lg:gap-6 pb-16 lg:pb-20">
      <!-- 1. Monstre: mateix patró que home (imatge sobre el fons global en mòbil; bento en desktop) -->
      <div class="space-y-4 lg:space-y-0">
        <div class="lg:hidden relative w-full flex justify-center px-2 pt-0 pb-1 overflow-visible">
          <img
            v-if="imatgeMascota"
            :src="imatgeMascota"
            alt="El teu monstre"
            width="500"
            height="500"
            class="w-[500px] max-w-full h-auto max-h-[500px] object-contain object-bottom drop-shadow-[0_14px_28px_rgba(0,0,0,0.35)] select-none -translate-y-3 sm:-translate-y-4"
            decoding="async"
            draggable="false"
          />
        </div>

        <div class="hidden lg:flex bento-card rounded-[10px] p-8 flex-col items-center relative w-full min-h-0 bg-white/95 backdrop-blur-md shadow-2xl border border-white/50 shrink-0">
          <div class="flex shrink-0 items-center justify-between w-full mb-6 relative z-10">
            <div>
              <h2 class="text-2xl font-black text-gray-800 tracking-tight">
                {{ $t('home.monster_title') }}
              </h2>
              <div class="flex items-center gap-2 mt-1">
                <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded-[10px] text-[10px] font-black uppercase tracking-wider">{{ $t('home.level') }} {{ user ? user.nivell : '—' }}</span>
              </div>
            </div>
            <UserHomeHomeStreakSection
              :ratxa="user ? user.ratxa_actual : 0"
              :ratxa-maxima="user ? user.ratxa_maxima : 0"
              :xp-total="user ? user.xp_total : 0"
              :monedes="user ? user.monedes : 0"
            />
          </div>

          <div class="w-full flex flex-col items-center justify-start relative pt-2 shrink-0">
            <div class="flex justify-center w-full px-2 pb-2 -mt-1">
              <img
                v-if="imatgeMascota"
                :src="imatgeMascota"
                alt="El teu monstre"
                width="500"
                height="500"
                class="w-[500px] max-w-full h-auto max-h-[500px] object-contain object-bottom drop-shadow-[0_20px_20px_rgba(0,0,0,0.28)] -translate-y-3 lg:-translate-y-5"
                decoding="async"
                draggable="false"
              />
            </div>
          </div>

          <p class="text-center text-gray-500 font-medium text-sm mt-6 max-w-sm shrink-0">
            {{ $t('home.monster_subtitle') }}
          </p>
        </div>
      </div>
      <!-- 2. Dades del perfil: separadors + panells -->
      <div class="space-y-3">
        <div class="moment-divider moment-divider--perfil" role="presentation">
          <span class="moment-divider__line" aria-hidden="true"></span>
          <span class="moment-divider__text">{{ $t('perfil.divider_profile_data') }}</span>
          <span class="moment-divider__line" aria-hidden="true"></span>
        </div>

        <div
          v-if="loading"
          class="bento-card bg-white/95 backdrop-blur-md rounded-[10px] p-6 sm:p-8 shadow-xl border border-white/50 animate-pulse space-y-4"
        >
          <div class="h-6 bg-gray-200 rounded-[10px] w-2/3"></div>
          <div class="h-12 bg-gray-100 rounded-[10px] w-full"></div>
          <div class="h-12 bg-gray-100 rounded-[10px] w-full"></div>
          <div class="h-10 bg-gray-100 rounded-[10px] w-full"></div>
        </div>

        <template v-else-if="user">
          <!-- 1) Nom, correu, contrasenya + Desar (mateixes etiquetes que formulari hàbit) -->
          <div
            class="bento-card habit-form bg-white/95 backdrop-blur-md rounded-[10px] p-6 sm:p-8 shadow-xl border border-white/50"
          >
            <div class="space-y-5">
              <div>
                <label class="habit-form-label" for="perfil-nom">{{ $t('perfil.field_username') }}</label>
                <input
                  id="perfil-nom"
                  v-model="formNom"
                  type="text"
                  autocomplete="username"
                  class="habit-form-field-surface w-full bg-gray-50/50 border-gray-100 focus:outline-none focus:ring-4 focus:ring-green-500/10 focus:border-green-500 focus:bg-white transition-all"
                />
              </div>
              <div>
                <label class="habit-form-label" for="perfil-email">{{ $t('perfil.field_gmail') }}</label>
                <input
                  id="perfil-email"
                  v-model="formEmail"
                  type="text"
                  autocomplete="email"
                  class="habit-form-field-surface w-full bg-gray-50/50 border-gray-100 focus:outline-none focus:ring-4 focus:ring-green-500/10 focus:border-green-500 focus:bg-white transition-all"
                />
              </div>
              <div>
                <label class="habit-form-label" for="perfil-pw">{{ $t('perfil.field_password') }}</label>
                <input
                  id="perfil-pw"
                  v-model="formPassword"
                  type="text"
                  autocomplete="new-password"
                  :placeholder="$t('perfil.password_placeholder')"
                  class="habit-form-field-surface w-full bg-gray-50/50 border-gray-100 focus:outline-none focus:ring-4 focus:ring-green-500/10 focus:border-green-500 focus:bg-white transition-all"
                />
              </div>

              <button
                type="button"
                class="perfil-btn-guardar w-full min-w-0 rounded-[10px] border-2 border-[#6FBC58] bg-[#79D45D] py-2.5 text-center text-base font-normal text-white transition hover:brightness-[0.97] disabled:opacity-50 disabled:pointer-events-none"
                :disabled="guardantCompte"
                @click="guardarCompte"
              >
                {{ guardantCompte ? $t('perfil.save_loading') : $t('habits.save') }}
              </button>
            </div>
          </div>

          <div class="moment-divider moment-divider--perfil" role="presentation">
            <span class="moment-divider__line" aria-hidden="true"></span>
            <span class="moment-divider__text">{{ $t('perfil.divider_level_coins_experience') }}</span>
            <span class="moment-divider__line" aria-hidden="true"></span>
          </div>

          <div class="space-y-3">
            <div class="perfil-level-xp-stack">
              <div class="perfil-level-xp-stack__pills">
                <div class="perfil-stat-pill perfil-stat-pill--nivell">
                  <p class="perfil-stat-pill__label">{{ $t('home.level') }}</p>
                  <div class="perfil-stat-pill__icon-wrap" aria-hidden="true">
                    <img :src="xpIcon" alt="" class="perfil-stat-pill__icon" width="125" height="125" decoding="async" />
                  </div>
                  <p class="perfil-stat-pill__value">{{ user.nivell }}</p>
                </div>
                <div class="perfil-stat-pill perfil-stat-pill--monedes">
                  <p class="perfil-stat-pill__label">{{ $t('home.coins') }}</p>
                  <div class="perfil-stat-pill__icon-wrap" aria-hidden="true">
                    <img :src="coinLoopy" alt="" class="perfil-stat-pill__icon coin-pixel" width="125" height="125" decoding="async" />
                  </div>
                  <p class="perfil-stat-pill__value">{{ user.monedes }}</p>
                </div>
              </div>

              <div class="perfil-xp-panel rounded-[10px] p-4 sm:p-5">
                <h3 class="perfil-xp-panel__title">{{ $t('perfil.experience_section_title') }}</h3>
                <p class="perfil-xp-panel__frac">{{ xpActualNivell }} / {{ xpObjectiuNivell }} XP</p>
                <div class="perfil-xp-bar" role="progressbar" :aria-valuenow="xpActualNivell" :aria-valuemin="0" :aria-valuemax="xpObjectiuNivell" aria-label="XP">
                  <div class="perfil-xp-bar__fill" :style="{ width: xpBarPercent + '%' }"></div>
                </div>
              </div>
            </div>

            <div class="moment-divider moment-divider--perfil" role="presentation">
              <span class="moment-divider__line" aria-hidden="true"></span>
              <span class="moment-divider__text">{{ $t('perfil.divider_streaks') }}</span>
              <span class="moment-divider__line" aria-hidden="true"></span>
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div class="perfil-streak-pill perfil-streak-pill--actual">
                <p class="perfil-streak-pill__label perfil-streak-pill__label--actual">{{ $t('perfil.streak_current_title') }}</p>
                <div class="perfil-streak-pill__media">
                  <img :src="ratxaIcon" alt="" class="perfil-streak-pill__icon" width="125" height="125" decoding="async" />
                  <p class="perfil-streak-pill__num">{{ user.ratxa_actual != null ? user.ratxa_actual : 0 }}</p>
                </div>
              </div>
              <div class="perfil-streak-pill perfil-streak-pill--max">
                <p class="perfil-streak-pill__label perfil-streak-pill__label--max">{{ $t('perfil.streak_max_title') }}</p>
                <div class="perfil-streak-pill__media">
                  <img :src="ratxaIcon" alt="" class="perfil-streak-pill__icon" width="125" height="125" decoding="async" />
                  <p class="perfil-streak-pill__num">{{ user.ratxa_maxima != null ? user.ratxa_maxima : 0 }}</p>
                </div>
              </div>
            </div>
          </div>
        </template>
      </div>

      <div v-if="user" class="space-y-3">
        <div class="moment-divider moment-divider--perfil" role="presentation">
          <span class="moment-divider__line" aria-hidden="true"></span>
          <span class="moment-divider__text">{{ $t('perfil.divider_achievements') }}</span>
          <span class="moment-divider__line" aria-hidden="true"></span>
        </div>

        <div class="perfil-logros-section space-y-3">
          <div v-if="user.logros && user.logros.length > 0" class="space-y-3">
            <p class="perfil-logros-hint">{{ $t('perfil.showcase_max_hint') }}</p>
            <div class="space-y-4 px-2 py-1 sm:px-3">
            <button
              v-for="(logro, idx) in user.logros"
              :key="logro.id"
              type="button"
              class="perfil-logro-card"
              :class="idx % 2 === 0 ? 'perfil-logro-card--tilt-left' : 'perfil-logro-card--tilt-right'"
              :aria-pressed="showcaseLogros.includes(logro.id) ? 'true' : 'false'"
              @click="toggleShowcaseLogro(logro.id)"
            >
              <div class="perfil-logro-card__icon-square" aria-hidden="true">
                <img :src="logrosIcon" alt="" class="perfil-logro-card__icon-img" width="34" height="34" decoding="async" draggable="false" />
              </div>
              <div class="perfil-logro-card__text">
                <p class="perfil-logro-card__title">{{ logro.nom }}</p>
                <p class="perfil-logro-card__subtitle">{{ logro.descripcio }}</p>
              </div>
              <span class="perfil-logro-card__dots" aria-hidden="true">
                <span></span><span></span><span></span>
              </span>
              <span v-if="showcaseLogros.includes(logro.id)" class="perfil-logro-card__pin" aria-hidden="true">⭐</span>
            </button>
            </div>
          </div>

          <div v-else class="perfil-logro-empty flex flex-col items-center justify-center py-12 text-gray-300">
            <div class="text-[40px] mb-2 opacity-20">🏆</div>
            <p class="text-xs font-black uppercase tracking-widest">{{ $t('perfil.no_achievements') }}</p>
          </div>

          <button
            v-if="showcaseChanged"
            type="button"
            @click="guardarShowcase"
            :disabled="guardantShowcase"
            class="w-full py-3 px-6 rounded-[10px] font-bold text-white transition-all"
            :class="guardantShowcase ? 'bg-gray-400 cursor-wait' : 'bg-purple-600 hover:bg-purple-700'"
          >
            {{ guardantShowcase ? 'Guardant…' : 'Desar selecció' }}
          </button>
        </div>
      </div>

      <!-- Historial diari: separador + llista estil hàbit -->
      <div class="space-y-3">
        <div class="moment-divider moment-divider--perfil" role="presentation">
          <span class="moment-divider__line" aria-hidden="true"></span>
          <span class="moment-divider__text">{{ $t('perfil.divider_daily_history') }}</span>
          <span class="moment-divider__line" aria-hidden="true"></span>
        </div>

        <div v-if="loadingLogs" class="space-y-3" role="list" aria-busy="true">
          <div v-for="i in 4" :key="'hist-skel-' + i" class="perfil-history-card perfil-history-card--skeleton animate-pulse" />
        </div>

        <div v-else class="space-y-3" role="list">
          <template v-if="logs.length > 0">
            <div
              v-for="(log, idx) in logs.slice(0, 12)"
              :key="'hist-' + idx + '-' + (log.habit_id || 0) + '-' + String(log.dia || '')"
              class="perfil-history-card"
              role="listitem"
            >
              <div class="perfil-history-card__mark" aria-hidden="true">
                <svg class="perfil-history-card__blob" width="56" height="40" viewBox="0 0 56 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path
                    d="M1.64885 13.8624C4.80033 5.5202 12.7867 0 21.7043 0H46.8857C51.8563 0 55.8857 4.02944 55.8857 9V18.1149C55.8857 20.9967 55.1982 23.8369 53.8802 26.3997L53.3295 27.4705C49.3729 35.1639 41.4476 40 32.7964 40H18.4113C11.3613 40 4.93035 35.9742 1.85018 29.6327C-0.361252 25.0797 -0.600734 19.8171 1.18804 15.0821L1.64885 13.8624Z"
                    :fill="colorMarcaHistorial(log)"
                  />
                </svg>
                <span class="perfil-history-card__icona">{{ (log.icona && String(log.icona).trim()) ? log.icona : '✨' }}</span>
              </div>
              <div class="perfil-history-card__text">
                <p class="perfil-history-card__title">{{ log.titol }}</p>
                <p class="perfil-history-card__subtitle">{{ historialMetaText(log) }}</p>
              </div>
            </div>
          </template>
          <div v-else class="perfil-history-empty py-10 text-center text-gray-300">
            <p class="text-xs font-black uppercase tracking-widest">{{ $t('perfil.no_history') }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import mascotaImg from "~/assets/img/Mascota.png";
import coinLoopy from "~/assets/img/Icones/Icona_Moneda.png";
import xpIcon from "~/assets/img/Icones/Icona_Experiencia.png";
import ratxaIcon from "~/assets/img/Icones/Icona_Ratxa.png";
import logrosIcon from "~/assets/img/Icones/Icona_Logros.png";
import { getDefaultColorForCategoryId } from "~/utils/habitCategoryColor.js";
import { normalizeHex } from "~/utils/colorSpace.js";
import { authFetch, getBaseUrl } from "~/composables/useApi.js";

var user = ref(null);
var loading = ref(true);
var logs = ref([]);
var loadingLogs = ref(true);
var showcaseLogros = ref([]);
var guardantShowcase = ref(false);
var showcaseChanged = ref(false);
var originalShowcase = ref([]);

var formNom = ref("");
var formEmail = ref("");
var formPassword = ref("");
var guardantCompte = ref(false);

var imatgeMascota = mascotaImg;

var xpActualNivell = computed(function () {
  if (!user.value) return 0;
  var v = user.value.xp_actual_nivel;
  if (v === undefined || v === null) return 0;
  return Number(v);
});

var xpObjectiuNivell = computed(function () {
  if (!user.value) return 1000;
  var v = user.value.xp_objetivo_nivel;
  if (v === undefined || v === null || Number(v) <= 0) return 1000;
  return Number(v);
});

var xpBarPercent = computed(function () {
  var obj = xpObjectiuNivell.value;
  if (obj <= 0) return 0;
  var pct = (xpActualNivell.value / obj) * 100;
  if (pct < 0) return 0;
  if (pct > 100) return 100;
  return pct;
});

function syncFormFromUser() {
  if (!user.value) {
    return;
  }
  formNom.value = user.value.nom || "";
  formEmail.value = user.value.email || "";
  formPassword.value = "";
}

var nuxtApp = useNuxtApp();
var t = useI18n().t;

function colorMarcaHistorial(log) {
  if (!log) {
    return getDefaultColorForCategoryId(1);
  }
  var c = log.color;
  if (c && String(c).trim()) {
    try {
      return normalizeHex(String(c).trim());
    } catch (_e) {
      return getDefaultColorForCategoryId(1);
    }
  }
  return getDefaultColorForCategoryId(1);
}

function historialMetaText(log) {
  if (!log) {
    return "";
  }
  var parts = [];
  if (log.dia) {
    parts.push(String(log.dia));
  }
  var obj = log.objectiu_vegades != null ? Number(log.objectiu_vegades) : 0;
  if (obj > 0) {
    parts.push(String(log.progreso_diario != null ? log.progreso_diario : 0) + "/" + obj);
  }
  parts.push(log.completado ? t("perfil.completat") : t("perfil.incomplet"));
  return parts.join(" · ");
}

function guardarCompte() {
  if (!user.value) {
    return;
  }
  guardantCompte.value = true;
  var body = {
    nom: formNom.value.trim(),
    email: formEmail.value.trim(),
  };
  if (formPassword.value.trim() !== "") {
    body.password = formPassword.value;
  }
  authFetch(getBaseUrl() + "/api/users/self/account", {
    method: "PUT",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(body),
  })
    .then(function(r) {
      return r.json().then(function(d) {
        return { ok: r.ok, d: d };
      });
    })
    .then(function(res) {
      guardantCompte.value = false;
      if (res.ok && res.d && res.d.success) {
        user.value.nom = res.d.data.nom;
        user.value.email = res.d.data.email;
        syncFormFromUser();
        if (nuxtApp.$swal) {
          nuxtApp.$swal.fire({
            icon: "success",
            title: t("perfil.save_success_title"),
            text: t("perfil.save_success_text"),
          });
        } else {
          alert(t("perfil.save_success_text"));
        }
        return;
      }
      var errMsg = t("perfil.save_error_generic");
      if (res.d && res.d.errors) {
        var e = res.d.errors;
        if (e.email === "taken") {
          errMsg = t("perfil.save_error_email_taken");
        } else if (e.email === "invalid") {
          errMsg = t("perfil.save_error_email_invalid");
        } else if (e.password === "min_6") {
          errMsg = t("perfil.save_error_password_min");
        } else if (e.nom === "required" || e.email === "required") {
          errMsg = t("perfil.save_error_required");
        }
      }
      if (nuxtApp.$swal) {
        nuxtApp.$swal.fire({ icon: "error", title: t("perfil.save_error_title"), text: errMsg });
      } else {
        alert(errMsg);
      }
    })
    .catch(function(err) {
      guardantCompte.value = false;
      console.error("Error guardant compte:", err);
      if (nuxtApp.$swal) {
        nuxtApp.$swal.fire({ icon: "error", title: t("perfil.save_error_title"), text: t("perfil.save_error_generic") });
      } else {
        alert(t("perfil.save_error_generic"));
      }
    });
}

onMounted(function() {
  loading.value = true;
  loadingLogs.value = true;
  var profilePromise = authFetch(getBaseUrl() + '/api/user/profile')
    .then(function(r) { return r.json(); })
    .then(function(d) { 
      user.value = d.data || d; 
      syncFormFromUser();
      if (user.value.logros_showcase) {
        originalShowcase.value = user.value.logros_showcase.map(function(l) { return l.id; });
        showcaseLogros.value = [].concat(originalShowcase.value);
      }
    });
  var logsPromise = authFetch(getBaseUrl() + '/api/habits/logs')
    .then(function(r) { return r.json(); })
    .then(function(d) { logs.value = d.data || d || []; });
  Promise.all([profilePromise, logsPromise])
    .then(function() {
      loading.value = false;
      loadingLogs.value = false;
    })
    .catch(function(err) {
      console.error("Error carregant perfil:", err);
      loading.value = false;
      loadingLogs.value = false;
    });
});

function toggleShowcaseLogro(logroId) {
  var idx = showcaseLogros.value.indexOf(logroId);
  if (idx > -1) {
    showcaseLogros.value.splice(idx, 1);
  } else if (showcaseLogros.value.length < 3) {
    showcaseLogros.value.push(logroId);
  }
  showcaseChanged.value = true;
}

function guardarShowcase() {
  guardantShowcase.value = true;
  authFetch(getBaseUrl() + '/api/users/self/showcase', {
    method: 'PUT',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ logros: showcaseLogros.value }),
  })
    .then(function(r) { return r.json(); })
    .then(function(d) {
      guardantShowcase.value = false;
      if (d.success) {
        alert('¡Logros guardados!');
      } else {
        alert(d.error || 'Error al guardar');
      }
    })
    .catch(function(err) {
      guardantShowcase.value = false;
      console.error("Error guardant showcase:", err);
      alert('Error al guardar');
    });
}
</script>

<style scoped>
.coin-pixel {
  image-rendering: pixelated;
}

/* Sobreescriu el rounded-2xl global de .habit-form-field-surface dins aquesta pàgina */
.perfil-page :deep(.habit-form-field-surface) {
  border-radius: 10px;
}

/* Separador com a home / plantilles */
.moment-divider--perfil {
  margin-top: 2px;
}

.moment-divider {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 30px;
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

.perfil-stat-pill {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.625rem;
  padding: 0.875rem 0.5rem 1rem;
  border-radius: 12px;
  text-align: center;
  min-width: 0;
}

.perfil-stat-pill--nivell {
  background: #94bef0;
}

.perfil-stat-pill--monedes {
  background: #dfa632;
}

.perfil-stat-pill__label {
  margin: 0;
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 30px;
  font-weight: 600;
  color: #faf9f9;
  line-height: 1.15;
}

.perfil-stat-pill__value {
  margin: 0;
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 30px;
  font-weight: 600;
  color: #faf9f9;
  line-height: 1.1;
}

.perfil-stat-pill__icon-wrap {
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  line-height: 0;
}

.perfil-stat-pill__icon {
  width: 125px;
  height: 125px;
  max-width: min(125px, 46vw);
  max-height: min(125px, 46vw);
  object-fit: contain;
}

.perfil-xp-panel {
  background: #faf9f9;
  border: 1px solid rgba(43, 45, 66, 0.06);
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
}

.perfil-xp-panel__title {
  margin: 0 0 0.5rem;
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 20px;
  font-weight: 600;
  color: #2b2d42;
  line-height: 1.25;
}

.perfil-xp-panel__frac {
  margin: 0 0 0.625rem;
  font-family: "Comfortaa", system-ui, sans-serif;
  font-size: 10px;
  font-weight: 700;
  color: #3189f2;
  line-height: 1.3;
}

.perfil-xp-bar {
  width: 100%;
  height: 5px;
  border-radius: 9999px;
  overflow: hidden;
  background: rgba(148, 190, 240, 0.38);
}

.perfil-xp-bar__fill {
  height: 100%;
  background: #3189f2;
  border-radius: 9999px;
  transition: width 0.45s ease;
}

/* Ratxes (sobre experiència del nivell) */
.perfil-streak-pill {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: flex-start;
  gap: 0.625rem;
  padding: 0.875rem 0.5rem 1rem;
  border-radius: 12px;
  text-align: center;
  min-width: 0;
}

.perfil-streak-pill--actual {
  background: #fadd66;
}

.perfil-streak-pill--max {
  background: #ff4c4c;
}

.perfil-streak-pill__label {
  margin: 0;
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 30px;
  font-weight: 600;
  line-height: 1.15;
}

.perfil-streak-pill__label--actual {
  color: #2b2d42;
}

.perfil-streak-pill__label--max {
  color: #faf9f9;
}

.perfil-streak-pill__media {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  line-height: 0;
}

.perfil-streak-pill__icon {
  width: 125px;
  height: 125px;
  max-width: min(125px, 46vw);
  max-height: min(125px, 46vw);
  object-fit: contain;
}

.perfil-streak-pill__num {
  position: absolute;
  top: 60%;
  left: 50%;
  transform: translate(-50%, -50%);
  margin: 0;
  display: inline-block;
  padding: 0 6px;
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 48px;
  font-weight: 600;
  line-height: 1;
  color: #2b2d42;
  text-shadow:
    0 1px 2px rgba(0, 0, 0, 0.85),
    0 0 8px rgba(0, 0, 0, 0.55);
  pointer-events: none;
}

/* Nivell + monedes + experiència: mateix gap entre nivell↔monedes i fila↔panell XP */
.perfil-level-xp-stack {
  --perfil-level-xp-gap: 1.25rem;
  display: flex;
  flex-direction: column;
  gap: var(--perfil-level-xp-gap);
}

.perfil-level-xp-stack__pills {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: var(--perfil-level-xp-gap);
}

/* Logros: targetes estil ruleta (tirada-card) + inclinació alternada */
.perfil-logros-hint {
  margin: 0;
  text-align: center;
  font-family: "Comfortaa", system-ui, sans-serif;
  font-size: 12px;
  font-weight: 500;
  color: rgba(250, 249, 249, 0.9);
}

.perfil-logro-card {
  position: relative;
  display: flex;
  align-items: center;
  width: 100%;
  min-height: 70px;
  padding: 6px 40px 6px 66px;
  margin: 0;
  border: none;
  border-radius: 10px;
  background-color: #faf9f9;
  box-sizing: border-box;
  cursor: pointer;
  text-align: left;
  transition: transform 0.18s ease, box-shadow 0.18s ease;
}

.perfil-logro-card--tilt-left {
  transform: rotate(-2.5deg);
}

.perfil-logro-card--tilt-right {
  transform: rotate(2.5deg);
}

.perfil-logro-card:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.perfil-logro-card--tilt-left:hover {
  transform: rotate(-1deg) translateY(-2px);
}

.perfil-logro-card--tilt-right:hover {
  transform: rotate(1deg) translateY(-2px);
}

.perfil-logro-card:focus {
  outline: none;
}

.perfil-logro-card:focus-visible {
  box-shadow: 0 0 0 2px rgba(121, 212, 93, 0.45);
}

.perfil-logro-card[aria-pressed="true"] {
  box-shadow: 0 0 0 2px rgba(168, 85, 247, 0.5);
}

.perfil-logro-card__icon-square {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  width: 43px;
  height: 43px;
  border-radius: 10px;
  background: #dfa632;
  display: flex;
  align-items: center;
  justify-content: center;
}

.perfil-logro-card__icon-img {
  display: block;
  width: 34px;
  height: 34px;
  object-fit: contain;
}

.perfil-logro-card__text {
  min-width: 0;
  flex: 1 1 0;
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.perfil-logro-card__title {
  margin: 0;
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 20px;
  font-weight: 700;
  line-height: 1.1;
  color: #2b2d42;
}

.perfil-logro-card__subtitle {
  margin: 0;
  font-family: "Comfortaa", system-ui, sans-serif;
  font-size: 11px;
  font-weight: 500;
  line-height: 1.25;
  color: #707070;
  display: -webkit-box;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 2;
  overflow: hidden;
}

.perfil-logro-card__dots {
  position: absolute;
  top: 8px;
  right: 10px;
  display: flex;
  align-items: center;
  gap: 3px;
  pointer-events: none;
}

.perfil-logro-card__dots span {
  width: 6px;
  height: 6px;
  border-radius: 999px;
  background-color: #d9d9d9;
}

.perfil-logro-card__pin {
  position: absolute;
  right: 10px;
  bottom: 8px;
  font-size: 15px;
  line-height: 1;
  pointer-events: none;
}

/* Historial diari: mateix patró visual que HomeHabitCard (blob + icona + títol) */
.perfil-history-card {
  position: relative;
  display: flex;
  align-items: center;
  width: 100%;
  min-height: 86px;
  padding: 18px 18px 18px 88px;
  background-color: #faf9f9;
  border-radius: 10px;
  box-sizing: border-box;
}

.perfil-history-card__mark {
  position: absolute;
  left: 18px;
  top: 50%;
  transform: translateY(-50%);
  width: 56px;
  height: 40px;
}

.perfil-history-card__blob {
  display: block;
  width: 56px;
  height: 40px;
}

.perfil-history-card__icona {
  position: absolute;
  left: 50%;
  top: 50%;
  transform: translate(-50%, -52%);
  z-index: 1;
  width: 2rem;
  text-align: center;
  font-size: 1.35rem;
  line-height: 1;
  pointer-events: none;
  text-shadow: 0 0 2px rgba(255, 255, 255, 0.85), 0 1px 2px rgba(0, 0, 0, 0.12);
}

.perfil-history-card__text {
  min-width: 0;
  flex: 1 1 0;
}

.perfil-history-card__title {
  margin: 0;
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 20px;
  font-weight: 700;
  line-height: 1.1;
  color: #2b2d42;
}

.perfil-history-card__subtitle {
  margin: 0.2rem 0 0;
  font-family: "Comfortaa", system-ui, sans-serif;
  font-size: 11px;
  font-weight: 500;
  line-height: 1.35;
  color: #707070;
}

.perfil-history-card--skeleton {
  min-height: 86px;
  padding: 0;
  background-color: rgba(250, 249, 249, 0.45);
}
</style>

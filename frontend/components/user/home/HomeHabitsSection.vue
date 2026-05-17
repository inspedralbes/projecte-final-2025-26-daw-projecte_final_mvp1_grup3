<template>
  <div class="space-y-0 lg:space-y-6">
    <div class="hidden lg:flex items-center justify-between">
      <h2 class="text-lg font-bold text-gray-800">{{ $t('home.habits_title') }}</h2>
      <NuxtLink v-if="!readOnly" to="/habits" class="text-blue-500 text-xs font-semibold hover:underline">
        {{ $t('home.see_all') }}
      </NuxtLink>
      <span v-else class="text-xs font-semibold text-gray-400">{{ $t('home.historical_readonly_hint') }}</span>
    </div>
    <div class="space-y-3">
      <div v-if="errorMissatge" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
        <span class="block sm:inline">{{ errorMissatge }}</span>
        <button class="absolute top-0 bottom-0 right-0 px-4 py-3" @click="$emit('netejar-error')">✕</button>
      </div>
      <div v-if="estaCarregant" class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded text-center">
        <span>{{ $t('home.loading_habits') }}</span>
      </div>
      <template v-else>
        <div class="daily-progress-card">
          <p class="daily-progress-card__title">Progrés Diari</p>
          <div class="daily-progress-card__bars" role="presentation" aria-hidden="true">
            <span
              v-for="index in barresProgress"
              :key="'daily-progress-' + index"
              class="daily-progress-card__bar"
              :class="index <= habitsCompletatsAvui ? 'daily-progress-card__bar--complete' : ''"
            ></span>
          </div>
        </div>

        <slot name="below-daily-progress"></slot>

        <div class="daily-progress-card__footer">
          <span class="daily-progress-card__list-icon" aria-hidden="true">
            <svg width="14" height="10" viewBox="0 0 14 10" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M4.33333 1H13M4.33333 5H13M4.33333 9H13M1 1H1.00667M1 5H1.00667M1 9H1.00667" stroke="#FAF9F9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </span>
          <span class="daily-progress-card__remaining">{{ habitsRestants }} habits restants!</span>
        </div>

        <div class="moment-divider" role="presentation">
          <span class="moment-divider__line" aria-hidden="true"></span>
          <span class="moment-divider__text">durant tot el dia</span>
          <span class="moment-divider__line" aria-hidden="true"></span>
        </div>
        <HomeCreateHabitDropdown v-if="!readOnly" ref="createHabitDropdown" @habit-creat="$emit('habit-creat')" />
        <template v-if="habitsSenseMoment.length > 0">
          <div
            v-for="h in habitsSenseMoment"
            :key="'tot-dia-' + h.id"
            class="habit-expandable"
            :class="[isHabitExpandit(h) ? 'habit-expandable--active' : '', habitCompletatAvui(h.id) ? 'habit-expandable--completed' : '']"
          >
            <UserHomeHomeHabitCard
              :habit="h"
              :progress="obtenirProgres(h.id)"
              :completat-avui="habitCompletatAvui(h.id)"
              :esta-processant="estaProcessant(h.id)"
              :clima-advers="esClimaAdversPerHabit(h)"
              :read-only="readOnly"
              @obrir-modal="$emit('obrir-modal-habit', $event)"
              @obrir-detalls="obrirHabitExpandit"
            />
            <div v-if="isHabitExpandit(h)" class="habit-expand-inline">
              <div class="habit-expand-top">
                <button class="habit-expand-close" type="button" @click="tancarHabitExpandit">
                  <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M11.2917 6.54167L6.54167 11.2917M6.54167 6.54167L11.2917 11.2917M16.8333 8.91667C16.8333 13.2889 13.2889 16.8333 8.91667 16.8333C4.54441 16.8333 1 13.2889 1 8.91667C1 4.54441 4.54441 1 8.91667 1C13.2889 1 16.8333 4.54441 16.8333 8.91667Z" stroke="#FAF9F9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                </button>
                <button class="habit-expand-edit" type="button" @click="editarHabitExpandit">
                  <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M7 2.41421H2.33333C1.97971 2.41421 1.64057 2.55469 1.39052 2.80474C1.14048 3.05479 1 3.39392 1 3.74755V13.0809C1 13.4345 1.14048 13.7736 1.39052 14.0237C1.64057 14.2737 1.97971 14.4142 2.33333 14.4142H11.6667C12.0203 14.4142 12.3594 14.2737 12.6095 14.0237C12.8595 13.7736 13 13.4345 13 13.0809V8.41421M12 1.41421C12.2652 1.149 12.6249 1 13 1C13.3751 1 13.7348 1.149 14 1.41421C14.2652 1.67943 14.4142 2.03914 14.4142 2.41421C14.4142 2.78929 14.2652 3.149 14 3.41421L7.66667 9.74755L5 10.4142L5.66667 7.74755L12 1.41421Z" stroke="#FAF9F9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                  <span>Editar Habit</span>
                </button>
              </div>
              <div class="habit-expand-panel">
                <Transition name="habit-done">
                  <div v-if="isJustCompleted(h.id) || habitCompletatAvui(h.id)" class="habit-done-overlay">
                    <span class="habit-done-check">✓</span>
                    <span class="habit-done-text">Completat!</span>
                    <span class="habit-done-label">+{{ recompensaXP }} XP &nbsp; +{{ recompensaMonedes }} monedes</span>
                    <span v-if="isJustCompleted(h.id)" class="habit-done-burst habit-done-burst--1"></span>
                    <span v-if="isJustCompleted(h.id)" class="habit-done-burst habit-done-burst--2"></span>
                    <span v-if="isJustCompleted(h.id)" class="habit-done-burst habit-done-burst--3"></span>
                    <span v-if="isJustCompleted(h.id)" class="habit-done-burst habit-done-burst--4"></span>
                  </div>
                </Transition>
                <div class="habit-expand-info">
                  <div class="habit-expand-meta">
                    <p><span class="meta-icon" :style="{ color: colorHabitExpandit }">↻</span>{{ textRepeticioHabit }}</p>
                    <p><span class="meta-icon" :style="{ color: colorHabitExpandit }">☆</span>{{ textDificultatHabit }}</p>
                    <button class="meta-priority" type="button" @click="togglePrioritari(habitExpanditActual)">
                      <span class="meta-icon" :style="{ color: colorHabitExpandit }">{{ esPrioritari(habitExpanditActual) ? '♥' : '♡' }}</span>
                      <span>Prioritari</span>
                    </button>
                    <p>
                      <span class="meta-icon" :style="{ color: colorHabitExpandit }">◎</span>
                      {{ habitExpanditActual.objectiuVegades || 1 }}
                    </p>
                  </div>
                  <div class="habit-expand-rewards">
                    <div class="reward-square reward-square--coins">
                      <img :src="coinIcon" alt="Monedes" class="reward-square__icon" />
                      <span class="reward-square__value">+{{ recompensaMonedes }}</span>
                    </div>
                    <div class="reward-square reward-square--xp">
                      <img :src="xpIcon" alt="XP" class="reward-square__icon" />
                      <span class="reward-square__value">+{{ recompensaXP }}</span>
                    </div>
                  </div>
                </div>
                <button class="focus-btn" type="button" @click="$emit('start-focus-habit', habitExpanditActual)">Mode Focus</button>
                <div class="habit-expand-controls">
                  <button type="button" class="habit-expand-action" @click="$emit('decrementar-habit', habitExpanditActual)">−</button>
                  <span class="habit-expand-count">{{ obtenirProgres(habitExpanditActual.id) }}</span>
                  <button type="button" class="habit-expand-action" @click="onIncrementarHabit(habitExpanditActual)">+</button>
                </div>
              </div>
            </div>
          </div>
        </template>
        <template v-if="habitsMatins.length > 0">
          <div class="moment-divider" role="presentation">
            <span class="moment-divider__line" aria-hidden="true"></span>
            <span class="moment-divider__text">per començar el dia</span>
            <span class="moment-divider__line" aria-hidden="true"></span>
          </div>
          <div
            v-for="h in habitsMatins"
            :key="'mati-' + h.id"
            class="habit-expandable"
            :class="[isHabitExpandit(h) ? 'habit-expandable--active' : '', habitCompletatAvui(h.id) ? 'habit-expandable--completed' : '']"
          >
            <UserHomeHomeHabitCard
              :habit="h"
              :progress="obtenirProgres(h.id)"
              :completat-avui="habitCompletatAvui(h.id)"
              :esta-processant="estaProcessant(h.id)"
              :clima-advers="esClimaAdversPerHabit(h)"
              :read-only="readOnly"
              @obrir-modal="$emit('obrir-modal-habit', $event)"
              @obrir-detalls="obrirHabitExpandit"
            />
            <div v-if="isHabitExpandit(h)" class="habit-expand-inline">
              <div class="habit-expand-top">
                <button class="habit-expand-close" type="button" @click="tancarHabitExpandit">
                  <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M11.2917 6.54167L6.54167 11.2917M6.54167 6.54167L11.2917 11.2917M16.8333 8.91667C16.8333 13.2889 13.2889 16.8333 8.91667 16.8333C4.54441 16.8333 1 13.2889 1 8.91667C1 4.54441 4.54441 1 8.91667 1C13.2889 1 16.8333 4.54441 16.8333 8.91667Z" stroke="#FAF9F9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
                <button class="habit-expand-edit" type="button" @click="editarHabitExpandit">
                  <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7 2.41421H2.33333C1.97971 2.41421 1.64057 2.55469 1.39052 2.80474C1.14048 3.05479 1 3.39392 1 3.74755V13.0809C1 13.4345 1.14048 13.7736 1.39052 14.0237C1.64057 14.2737 1.97971 14.4142 2.33333 14.4142H11.6667C12.0203 14.4142 12.3594 14.2737 12.6095 14.0237C12.8595 13.7736 13 13.4345 13 13.0809V8.41421M12 1.41421C12.2652 1.149 12.6249 1 13 1C13.3751 1 13.7348 1.149 14 1.41421C14.2652 1.67943 14.4142 2.03914 14.4142 2.41421C14.4142 2.78929 14.2652 3.149 14 3.41421L7.66667 9.74755L5 10.4142L5.66667 7.74755L12 1.41421Z" stroke="#FAF9F9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                  <span>Editar Habit</span>
                </button>
              </div>
              <div class="habit-expand-panel">
                <Transition name="habit-done">
                  <div v-if="isJustCompleted(h.id) || habitCompletatAvui(h.id)" class="habit-done-overlay">
                    <span class="habit-done-check">✓</span>
                    <span class="habit-done-text">Completat!</span>
                    <span class="habit-done-label">+{{ recompensaXP }} XP &nbsp; +{{ recompensaMonedes }} monedes</span>
                    <span v-if="isJustCompleted(h.id)" class="habit-done-burst habit-done-burst--1"></span>
                    <span v-if="isJustCompleted(h.id)" class="habit-done-burst habit-done-burst--2"></span>
                    <span v-if="isJustCompleted(h.id)" class="habit-done-burst habit-done-burst--3"></span>
                    <span v-if="isJustCompleted(h.id)" class="habit-done-burst habit-done-burst--4"></span>
                  </div>
                </Transition>
                <div class="habit-expand-info">
                  <div class="habit-expand-meta">
                    <p><span class="meta-icon" :style="{ color: colorHabitExpandit }">↻</span>{{ textRepeticioHabit }}</p>
                    <p><span class="meta-icon" :style="{ color: colorHabitExpandit }">☆</span>{{ textDificultatHabit }}</p>
                    <button class="meta-priority" type="button" @click="togglePrioritari(habitExpanditActual)">
                      <span class="meta-icon" :style="{ color: colorHabitExpandit }">{{ esPrioritari(habitExpanditActual) ? '♥' : '♡' }}</span>
                      <span>Prioritari</span>
                    </button>
                    <p>
                      <span class="meta-icon" :style="{ color: colorHabitExpandit }">◎</span>
                      {{ habitExpanditActual.objectiuVegades || 1 }}
                    </p>
                  </div>
                  <div class="habit-expand-rewards">
                    <div class="reward-square reward-square--coins">
                      <img :src="coinIcon" alt="Monedes" class="reward-square__icon" />
                      <span class="reward-square__value">+{{ recompensaMonedes }}</span>
                    </div>
                    <div class="reward-square reward-square--xp">
                      <img :src="xpIcon" alt="XP" class="reward-square__icon" />
                      <span class="reward-square__value">+{{ recompensaXP }}</span>
                    </div>
                  </div>
                </div>
                <button class="focus-btn" type="button" @click="$emit('start-focus-habit', habitExpanditActual)">Mode Focus</button>
                <div class="habit-expand-controls">
                  <button type="button" class="habit-expand-action" @click="$emit('decrementar-habit', habitExpanditActual)">−</button>
                  <span class="habit-expand-count">{{ obtenirProgres(habitExpanditActual.id) }}</span>
                  <button type="button" class="habit-expand-action" @click="onIncrementarHabit(habitExpanditActual)">+</button>
                </div>
              </div>
            </div>
          </div>
        </template>

        <template v-if="habitsTarda.length > 0">
          <div class="moment-divider" role="presentation">
            <span class="moment-divider__line" aria-hidden="true"></span>
            <span class="moment-divider__text">el focus del dia</span>
            <span class="moment-divider__line" aria-hidden="true"></span>
          </div>
          <div
            v-for="h in habitsTarda"
            :key="'tarda-' + h.id"
            class="habit-expandable"
            :class="[isHabitExpandit(h) ? 'habit-expandable--active' : '', habitCompletatAvui(h.id) ? 'habit-expandable--completed' : '']"
          >
            <UserHomeHomeHabitCard
              :habit="h"
              :progress="obtenirProgres(h.id)"
              :completat-avui="habitCompletatAvui(h.id)"
              :esta-processant="estaProcessant(h.id)"
              :clima-advers="esClimaAdversPerHabit(h)"
              :read-only="readOnly"
              @obrir-modal="$emit('obrir-modal-habit', $event)"
              @obrir-detalls="obrirHabitExpandit"
            />
            <div v-if="isHabitExpandit(h)" class="habit-expand-inline">
              <div class="habit-expand-top">
                <button class="habit-expand-close" type="button" @click="tancarHabitExpandit"><svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M11.2917 6.54167L6.54167 11.2917M6.54167 6.54167L11.2917 11.2917M16.8333 8.91667C16.8333 13.2889 13.2889 16.8333 8.91667 16.8333C4.54441 16.8333 1 13.2889 1 8.91667C1 4.54441 4.54441 1 8.91667 1C13.2889 1 16.8333 4.54441 16.8333 8.91667Z" stroke="#FAF9F9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
                <button class="habit-expand-edit" type="button" @click="editarHabitExpandit"><svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7 2.41421H2.33333C1.97971 2.41421 1.64057 2.55469 1.39052 2.80474C1.14048 3.05479 1 3.39392 1 3.74755V13.0809C1 13.4345 1.14048 13.7736 1.39052 14.0237C1.64057 14.2737 1.97971 14.4142 2.33333 14.4142H11.6667C12.0203 14.4142 12.3594 14.2737 12.6095 14.0237C12.8595 13.7736 13 13.4345 13 13.0809V8.41421M12 1.41421C12.2652 1.149 12.6249 1 13 1C13.3751 1 13.7348 1.149 14 1.41421C14.2652 1.67943 14.4142 2.03914 14.4142 2.41421C14.4142 2.78929 14.2652 3.149 14 3.41421L7.66667 9.74755L5 10.4142L5.66667 7.74755L12 1.41421Z" stroke="#FAF9F9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg><span>Editar Habit</span></button>
              </div>
              <div class="habit-expand-panel">
                <Transition name="habit-done">
                  <div v-if="isJustCompleted(h.id) || habitCompletatAvui(h.id)" class="habit-done-overlay">
                    <span class="habit-done-check">✓</span>
                    <span class="habit-done-text">Completat!</span>
                    <span class="habit-done-label">+{{ recompensaXP }} XP &nbsp; +{{ recompensaMonedes }} monedes</span>
                    <span v-if="isJustCompleted(h.id)" class="habit-done-burst habit-done-burst--1"></span>
                    <span v-if="isJustCompleted(h.id)" class="habit-done-burst habit-done-burst--2"></span>
                    <span v-if="isJustCompleted(h.id)" class="habit-done-burst habit-done-burst--3"></span>
                    <span v-if="isJustCompleted(h.id)" class="habit-done-burst habit-done-burst--4"></span>
                  </div>
                </Transition>
                <div class="habit-expand-info">
                  <div class="habit-expand-meta">
                    <p><span class="meta-icon" :style="{ color: colorHabitExpandit }">↻</span>{{ textRepeticioHabit }}</p>
                    <p><span class="meta-icon" :style="{ color: colorHabitExpandit }">☆</span>{{ textDificultatHabit }}</p>
                    <button class="meta-priority" type="button" @click="togglePrioritari(habitExpanditActual)">
                      <span class="meta-icon" :style="{ color: colorHabitExpandit }">{{ esPrioritari(habitExpanditActual) ? '♥' : '♡' }}</span>
                      <span>Prioritari</span>
                    </button>
                    <p>
                      <span class="meta-icon" :style="{ color: colorHabitExpandit }">◎</span>
                      {{ habitExpanditActual.objectiuVegades || 1 }}
                    </p>
                  </div>
                  <div class="habit-expand-rewards">
                    <div class="reward-square reward-square--coins">
                      <img :src="coinIcon" alt="Monedes" class="reward-square__icon" />
                      <span class="reward-square__value">+{{ recompensaMonedes }}</span>
                    </div>
                    <div class="reward-square reward-square--xp">
                      <img :src="xpIcon" alt="XP" class="reward-square__icon" />
                      <span class="reward-square__value">+{{ recompensaXP }}</span>
                    </div>
                  </div>
                </div>
                <button class="focus-btn" type="button" @click="$emit('start-focus-habit', habitExpanditActual)">Mode Focus</button>
                <div class="habit-expand-controls">
                  <button type="button" class="habit-expand-action" @click="$emit('decrementar-habit', habitExpanditActual)">−</button>
                  <span class="habit-expand-count">{{ obtenirProgres(habitExpanditActual.id) }}</span>
                  <button type="button" class="habit-expand-action" @click="onIncrementarHabit(habitExpanditActual)">+</button>
                </div>
              </div>
            </div>
          </div>
        </template>

        <template v-if="habitsNit.length > 0">
          <div class="moment-divider" role="presentation">
            <span class="moment-divider__line" aria-hidden="true"></span>
            <span class="moment-divider__text">tancar el dia</span>
            <span class="moment-divider__line" aria-hidden="true"></span>
          </div>
          <div
            v-for="h in habitsNit"
            :key="'nit-' + h.id"
            class="habit-expandable"
            :class="[isHabitExpandit(h) ? 'habit-expandable--active' : '', habitCompletatAvui(h.id) ? 'habit-expandable--completed' : '']"
          >
            <UserHomeHomeHabitCard
              :habit="h"
              :progress="obtenirProgres(h.id)"
              :completat-avui="habitCompletatAvui(h.id)"
              :esta-processant="estaProcessant(h.id)"
              :clima-advers="esClimaAdversPerHabit(h)"
              :read-only="readOnly"
              @obrir-modal="$emit('obrir-modal-habit', $event)"
              @obrir-detalls="obrirHabitExpandit"
            />
            <div v-if="isHabitExpandit(h)" class="habit-expand-inline">
              <div class="habit-expand-top">
                <button class="habit-expand-close" type="button" @click="tancarHabitExpandit"><svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M11.2917 6.54167L6.54167 11.2917M6.54167 6.54167L11.2917 11.2917M16.8333 8.91667C16.8333 13.2889 13.2889 16.8333 8.91667 16.8333C4.54441 16.8333 1 13.2889 1 8.91667C1 4.54441 4.54441 1 8.91667 1C13.2889 1 16.8333 4.54441 16.8333 8.91667Z" stroke="#FAF9F9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
                <button class="habit-expand-edit" type="button" @click="editarHabitExpandit"><svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7 2.41421H2.33333C1.97971 2.41421 1.64057 2.55469 1.39052 2.80474C1.14048 3.05479 1 3.39392 1 3.74755V13.0809C1 13.4345 1.14048 13.7736 1.39052 14.0237C1.64057 14.2737 1.97971 14.4142 2.33333 14.4142H11.6667C12.0203 14.4142 12.3594 14.2737 12.6095 14.0237C12.8595 13.7736 13 13.4345 13 13.0809V8.41421M12 1.41421C12.2652 1.149 12.6249 1 13 1C13.3751 1 13.7348 1.149 14 1.41421C14.2652 1.67943 14.4142 2.03914 14.4142 2.41421C14.4142 2.78929 14.2652 3.149 14 3.41421L7.66667 9.74755L5 10.4142L5.66667 7.74755L12 1.41421Z" stroke="#FAF9F9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg><span>Editar Habit</span></button>
              </div>
              <div class="habit-expand-panel">
                <Transition name="habit-done">
                  <div v-if="isJustCompleted(h.id) || habitCompletatAvui(h.id)" class="habit-done-overlay">
                    <span class="habit-done-check">✓</span>
                    <span class="habit-done-text">Completat!</span>
                    <span class="habit-done-label">+{{ recompensaXP }} XP &nbsp; +{{ recompensaMonedes }} monedes</span>
                    <span v-if="isJustCompleted(h.id)" class="habit-done-burst habit-done-burst--1"></span>
                    <span v-if="isJustCompleted(h.id)" class="habit-done-burst habit-done-burst--2"></span>
                    <span v-if="isJustCompleted(h.id)" class="habit-done-burst habit-done-burst--3"></span>
                    <span v-if="isJustCompleted(h.id)" class="habit-done-burst habit-done-burst--4"></span>
                  </div>
                </Transition>
                <div class="habit-expand-info">
                  <div class="habit-expand-meta">
                    <p><span class="meta-icon" :style="{ color: colorHabitExpandit }">↻</span>{{ textRepeticioHabit }}</p>
                    <p><span class="meta-icon" :style="{ color: colorHabitExpandit }">☆</span>{{ textDificultatHabit }}</p>
                    <button class="meta-priority" type="button" @click="togglePrioritari(habitExpanditActual)">
                      <span class="meta-icon" :style="{ color: colorHabitExpandit }">{{ esPrioritari(habitExpanditActual) ? '♥' : '♡' }}</span>
                      <span>Prioritari</span>
                    </button>
                    <p>
                      <span class="meta-icon" :style="{ color: colorHabitExpandit }">◎</span>
                      {{ habitExpanditActual.objectiuVegades || 1 }}
                    </p>
                  </div>
                  <div class="habit-expand-rewards">
                    <div class="reward-square reward-square--coins">
                      <img :src="coinIcon" alt="Monedes" class="reward-square__icon" />
                      <span class="reward-square__value">+{{ recompensaMonedes }}</span>
                    </div>
                    <div class="reward-square reward-square--xp">
                      <img :src="xpIcon" alt="XP" class="reward-square__icon" />
                      <span class="reward-square__value">+{{ recompensaXP }}</span>
                    </div>
                  </div>
                </div>
                <button class="focus-btn" type="button" @click="$emit('start-focus-habit', habitExpanditActual)">Mode Focus</button>
                <div class="habit-expand-controls">
                  <button type="button" class="habit-expand-action" @click="$emit('decrementar-habit', habitExpanditActual)">−</button>
                  <span class="habit-expand-count">{{ obtenirProgres(habitExpanditActual.id) }}</span>
                  <button type="button" class="habit-expand-action" @click="onIncrementarHabit(habitExpanditActual)">+</button>
                </div>
              </div>
            </div>
          </div>
        </template>
      </template>
    </div>
  </div>
</template>

<script>
import UserHomeHomeHabitCard from "~/components/user/home/HomeHabitCard.vue";
import HomeCreateHabitDropdown from "~/components/user/home/HomeCreateHabitDropdown.vue";
import coinIcon from "~/assets/img/Icones/Icona_Moneda.png";
import xpIcon from "~/assets/img/Icones/Icona_Experiencia.png";

function parseHoraRecordatori(recordatori) {
  if (!recordatori || String(recordatori).trim() === "") {
    return null;
  }
  var valor = String(recordatori);
  var parts = valor.split(":");
  var hora = parseInt(parts[0], 10);
  if (isNaN(hora) || hora < 0 || hora > 23) {
    return null;
  }
  return hora;
}

/** Agrupa per moment del dia: API `moment_dia` / `momentDia`, o fallback per hora de recordatori. */
function bucketMomentDelDia(habit) {
  var raw = habit.momentDia != null && habit.momentDia !== ""
    ? habit.momentDia
    : (habit.moment_dia != null && habit.moment_dia !== "" ? habit.moment_dia : "");
  if (raw === "mati" || raw === "tarda" || raw === "nit" || raw === "tot_dia") {
    return raw;
  }
  var hora = parseHoraRecordatori(habit.recordatori);
  if (hora === null) {
    return "tot_dia";
  }
  if (hora >= 5 && hora < 12) {
    return "mati";
  }
  if (hora >= 12 && hora < 19) {
    return "tarda";
  }
  return "nit";
}

export default {
  name: 'HomeHabitsSection',
  components: {
    UserHomeHomeHabitCard: UserHomeHomeHabitCard,
    HomeCreateHabitDropdown: HomeCreateHabitDropdown
  },
  data: function () {
    return {
      habitExpanditId: null,
      prioritatsLocals: {},
      coinIcon: coinIcon,
      xpIcon: xpIcon,
      completedAnimHabitId: null,
      _completedAnimTimer: null
    };
  },
  props: {
    habits:             { type: Array,    default: function () { return []; } },
    estaCarregant:      { type: Boolean,  default: false },
    errorMissatge:      { type: String,   default: '' },
    obtenirProgres:     { type: Function, required: true },
    habitCompletatAvui: { type: Function, required: true },
    estaProcessant:     { type: Function, default: function () { return false; } },
    weatherGlobal:      { type: Object,   default: null },
    readOnly:           { type: Boolean,  default: false }
  },
  computed: {
    barresProgress: function () {
      return this.habits.length;
    },
    habitsCompletatsAvui: function () {
      var total = 0;
      var i;
      for (i = 0; i < this.habits.length; i++) {
        if (this.habitCompletatAvui(this.habits[i].id)) {
          total += 1;
        }
      }
      return total;
    },
    habitsRestants: function () {
      var restants = this.habits.length - this.habitsCompletatsAvui;
      return restants > 0 ? restants : 0;
    },
    habitsSenseMoment: function () {
      var llista = (this.habits || []).filter(function (habit) {
        return bucketMomentDelDia(habit) === "tot_dia";
      });
      return this.ordenarPerPrioritat(llista);
    },
    habitsMatins: function () {
      var llista = (this.habits || []).filter(function (habit) {
        return bucketMomentDelDia(habit) === "mati";
      });
      return this.ordenarPerPrioritat(llista);
    },
    habitsTarda: function () {
      var llista = (this.habits || []).filter(function (habit) {
        return bucketMomentDelDia(habit) === "tarda";
      });
      return this.ordenarPerPrioritat(llista);
    },
    habitsNit: function () {
      var llista = (this.habits || []).filter(function (habit) {
        return bucketMomentDelDia(habit) === "nit";
      });
      return this.ordenarPerPrioritat(llista);
    },
    textRepeticioHabit: function () {
      if (!this.habitExpanditActual) return '';
      if (this.habitExpanditActual.frequenciaTipus === 'diaria') return 'Diariament';
      if (this.habitExpanditActual.frequenciaTipus === 'semanal') return 'Setmanalment';
      if (this.habitExpanditActual.frequenciaTipus === 'mensual') return 'Mensualment';
      return 'Diariament';
    },
    textDificultatHabit: function () {
      if (!this.habitExpanditActual || !this.habitExpanditActual.dificultat) return 'Facil';
      if (this.habitExpanditActual.dificultat === 'dificil') return 'Dificil';
      if (this.habitExpanditActual.dificultat === 'mitja' || this.habitExpanditActual.dificultat === 'media') return 'Mitja';
      return 'Facil';
    },
    colorHabitExpandit: function () {
      if (!this.habitExpanditActual || !this.habitExpanditActual.categoriaId) return '#79D45D';
      var colors = { 1: '#79D45D', 2: '#5BA5FF', 3: '#F4A629', 4: '#A78BFA', 5: '#FF6B8A', 6: '#F4A629', 7: '#79D45D', 8: '#5BA5FF' };
      return colors[this.habitExpanditActual.categoriaId] || '#79D45D';
    },
    habitExpanditActual: function () {
      if (!this.habitExpanditId) return null;
      var i;
      for (i = 0; i < this.habits.length; i++) {
        if (this.habits[i].id === this.habitExpanditId) {
          return this.habits[i];
        }
      }
      return null;
    },
    recompensaXP: function () {
      if (!this.habitExpanditActual) return 0;
      var dif = String(this.habitExpanditActual.dificultat || "facil").toLowerCase();
      if (dif === "dificil") return 400;
      if (dif === "mitja" || dif === "media") return 250;
      return 100;
    },
    recompensaMonedes: function () {
      if (!this.habitExpanditActual) return 0;
      var dif = String(this.habitExpanditActual.dificultat || "facil").toLowerCase();
      if (dif === "dificil") return 10;
      if (dif === "mitja" || dif === "media") return 5;
      return 2;
    }
  },
  methods: {
    obrirHabitExpandit: function (habit) {
      if (this.readOnly) return;
      this.habitExpanditId = habit && habit.id ? habit.id : null;
    },
    tancarHabitExpandit: function () {
      this.habitExpanditId = null;
    },
    isHabitExpandit: function (habit) {
      return !!(habit && this.habitExpanditId && habit.id === this.habitExpanditId);
    },
    editarHabitExpandit: function () {
      if (this.readOnly) return;
      if (!this.habitExpanditActual) {
        return;
      }
      var dropdown = this.$refs.createHabitDropdown;
      if (dropdown && typeof dropdown.obrirPerEdicio === "function") {
        dropdown.obrirPerEdicio(this.habitExpanditActual);
        this.tancarHabitExpandit();
      }
    },
    esPrioritari: function (habit) {
      if (!habit || !habit.id) return false;
      if (this.prioritatsLocals.hasOwnProperty(habit.id)) {
        return this.prioritatsLocals[habit.id] === true;
      }
      if (habit.prioritari === true || habit.priority === true || habit.esPrioritari === true) {
        return true;
      }
      return !!(habit.metadata && habit.metadata.prioritari === true);
    },
    togglePrioritari: function (habit) {
      if (!habit || !habit.id) return;
      this.prioritatsLocals = Object.assign({}, this.prioritatsLocals, {
        [habit.id]: !this.esPrioritari(habit)
      });
    },
    ordenarPerPrioritat: function (llista) {
      var self = this;
      return (llista || []).slice().sort(function (a, b) {
        var aPrioritari = self.esPrioritari(a) ? 1 : 0;
        var bPrioritari = self.esPrioritari(b) ? 1 : 0;
        return bPrioritari - aPrioritari;
      });
    },
    esClimaAdversPerHabit: function (habit) {
      var CATEGORIES_EXTERIOR = [1, 7, 8];
      if (!this.weatherGlobal || this.weatherGlobal.suitable !== false) {
        return false;
      }
      return CATEGORIES_EXTERIOR.indexOf(habit.categoriaId) >= 0;
    },
    onIncrementarHabit: function (habit) {
      var self = this;
      var progresActual = self.obtenirProgres(habit.id);
      var objectiu = habit.objectiuVegades || 1;
      var willComplete = (progresActual + 1 >= objectiu);
      self.$emit('incrementar-habit', habit);
      if (willComplete) {
        self.completedAnimHabitId = habit.id;
        if (self._completedAnimTimer) {
          clearTimeout(self._completedAnimTimer);
        }
        self._completedAnimTimer = setTimeout(function () {
          self.completedAnimHabitId = null;
          self._completedAnimTimer = null;
        }, 2200);
        setTimeout(function () {
          self.$emit('completar-habit', habit);
        }, 350);
      }
    },
    isJustCompleted: function (habitId) {
      return this.completedAnimHabitId === habitId;
    }
  }
};
</script>

<style scoped>
.habit-expand-overlay {
  position: fixed;
  inset: 0;
  z-index: 80;
  background: rgba(0, 0, 0, 0.54);
  display: flex;
  align-items: flex-end;
}

.habit-expand-shell {
  width: 100%;
  padding: 14px 10px 10px;
  animation: habit-sheet-up 0.25s ease-out;
}

.habit-expandable {
  overflow: hidden;
  border-radius: 10px;
  max-height: 92px;
  transition: max-height 0.28s ease, background-color 0.2s ease, padding 0.2s ease;
}

.habit-expandable--active {
  background: rgba(0, 0, 0, 0.54);
  padding: 10px;
  max-height: 560px;
}

.habit-expand-inline {
  animation: habit-sheet-up 0.22s ease-out;
  margin-top: 8px;
}

.habit-expand-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 8px;
}

.habit-expand-close,
.habit-expand-edit {
  border: 0;
  background: transparent;
  color: #FAF9F9;
}

.habit-expand-edit {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 18px;
  font-weight: 400;
}

.habit-expand-panel {
  position: relative;
  overflow: hidden;
  background: #FAF9F9;
  border-radius: 14px;
  padding: 12px;
}

.habit-expand-title-row {
  display: flex;
  align-items: center;
  gap: 10px;
}

.habit-expand-blob {
  width: 64px;
  height: 40px;
  border-radius: 14px 28px 28px 14px;
  display: inline-block;
}

.habit-expand-title {
  margin: 0;
  font-size: 36px;
  color: #2B2D42;
  flex: 1;
}

.habit-expand-info {
  display: flex;
  justify-content: flex-start;
  align-items: center;
  gap: 5px;
  margin-bottom: 4px;
}

.habit-expand-meta {
  display: grid;
  gap: 8px;
}

.habit-expand-meta p,
.meta-priority {
  margin: 0;
  border: 0;
  background: transparent;
  color: #5B5B5B;
  display: inline-flex;
  align-items: center;
  gap: 10px;
  font-size: 16px;
}

.meta-icon {
  font-size: 18px;
  width: 24px;
  height: 24px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.focus-btn {
  width: 100%;
  margin-top: 12px;
  border: 0;
  background: #79D45D;
  color: #FAF9F9;
  border-radius: 12px;
  height: 44px;
  font-size: 18px;
  font-weight: 600;
  cursor: pointer;
  transition: transform 0.1s;
}

.focus-btn:active {
  transform: scale(0.98);
}

.habit-expand-controls {
  margin-top: 12px;
  display: grid;
  grid-template-columns: 1fr 72px 1fr;
  gap: 12px;
  align-items: center;
}

.habit-expand-action {
  height: 54px;
  border: 0;
  border-radius: 16px;
  background: #E6E6E6;
  color: #2B2D42;
  font-size: 32px;
  line-height: 1;
}

.habit-expand-count {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  height: 54px;
  border-radius: 16px;
  background: #F1F1F1;
  color: #2B2D42;
  font-size: 28px;
  font-weight: 700;
}

.habit-card__dots {
  display: flex;
  gap: 3px;
}

.habit-card__dots span {
  width: 6px;
  height: 6px;
  border-radius: 999px;
  background-color: #D9D9D9;
}

.habit-expand-rewards {
  display: flex;
  gap: 12px;
  margin-left: 15px;
}

.reward-square {
  width: 76px;
  height: 76px;
  border-radius: 20px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 4px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  transition: transform 0.2s;
}

.reward-square:hover {
  transform: scale(1.05);
}

.reward-square--coins {
  background: #dfa632;
  transform: rotate(-5deg);
}

.reward-square--xp {
  background: #94bef0;
  transform: rotate(5deg);
}

.reward-square__icon {
  width: 38px;
  height: 38px;
  object-fit: contain;
}

.reward-square__value {
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 20px;
  font-weight: 800;
  color: #faf9f9;
  line-height: 1;
}

@keyframes habit-sheet-up {
  from { transform: translateY(22px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}

.daily-progress-card {
  background: rgba(8, 8, 8, 0.32);
  border-radius: 10px;
  padding: 18px;
}

.daily-progress-card__title {
  margin: 0 0 8px 0;
  color: #FAF9F9;
  font-size: 14px;
  line-height: 1.2;
}

.daily-progress-card__bars {
  display: flex;
  align-items: center;
  gap: 6px;
}

.daily-progress-card__footer {
  display: flex;
  align-items: center;
}

.daily-progress-card__bar {
  flex: 1 1 0;
  height: 6px;
  border-radius: 999px;
  background: rgba(0, 0, 0, 0.28);
}

.daily-progress-card__bar--complete {
  background: #FAF9F9;
}

.daily-progress-card__list-icon {
  display: inline-flex;
  align-items: center;
}

.daily-progress-card__remaining {
  color: #FAF9F9;
  font-size: 14px;
  line-height: 1.2;
  padding-left: 10px;
}

.moment-divider {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 30px;
  width: 100%;
  margin-top: 2px;
}

.moment-divider__text {
  flex-shrink: 0;
  color: #FAF9F9;
  font-size: 15px;
  line-height: 1.2;
  white-space: nowrap;
}

.moment-divider__line {
  flex: 1 1 0;
  min-width: 0;
  height: 3px;
  background: #FAF9F9;
  border-radius: 999px;
}

.habit-expandable {
  position: relative;
}



.habit-done-overlay {
  position: absolute;
  inset: 0;
  z-index: 10;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background: rgba(121, 212, 93, 0.94);
  border-radius: 10px;
  gap: 4px;
  overflow: hidden;
  pointer-events: none;
}

.habit-done-check {
  font-size: 36px;
  font-weight: 900;
  color: #fff;
  line-height: 1;
  animation: habit-done-pop 0.45s cubic-bezier(0.34, 1.56, 0.64, 1) both;
}

.habit-done-text {
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 18px;
  font-weight: 800;
  color: #fff;
  letter-spacing: 0.03em;
  animation: habit-done-slide-up 0.4s 0.12s cubic-bezier(0.22, 1, 0.36, 1) both;
}

.habit-done-label {
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 14px;
  font-weight: 700;
  color: rgba(255, 255, 255, 0.9);
  animation: habit-done-slide-up 0.4s 0.25s cubic-bezier(0.22, 1, 0.36, 1) both;
}

.habit-done-burst {
  position: absolute;
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: #fff;
  opacity: 0;
}

.habit-done-burst--1 {
  top: 8px;
  left: 20%;
  animation: habit-burst 0.7s 0.1s ease-out both;
}

.habit-done-burst--2 {
  top: 10px;
  right: 18%;
  animation: habit-burst 0.7s 0.2s ease-out both;
}

.habit-done-burst--3 {
  bottom: 10px;
  left: 28%;
  animation: habit-burst 0.7s 0.15s ease-out both;
}

.habit-done-burst--4 {
  bottom: 8px;
  right: 25%;
  animation: habit-burst 0.7s 0.25s ease-out both;
}

@keyframes habit-done-pop {
  0% { transform: scale(0); opacity: 0; }
  60% { transform: scale(1.3); opacity: 1; }
  100% { transform: scale(1); opacity: 1; }
}

@keyframes habit-done-slide-up {
  0% { transform: translateY(8px); opacity: 0; }
  100% { transform: translateY(0); opacity: 1; }
}

@keyframes habit-burst {
  0% { transform: scale(0); opacity: 0.9; }
  50% { transform: scale(1.8); opacity: 0.6; }
  100% { transform: scale(2.5) translateY(-6px); opacity: 0; }
}

.habit-done-enter-active {
  transition: opacity 0.2s ease, transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.habit-done-leave-active {
  transition: opacity 0.35s ease, transform 0.35s ease;
}

.habit-done-enter-from {
  opacity: 0;
  transform: scale(0.85);
}

.habit-done-leave-to {
  opacity: 0;
  transform: scale(0.92);
}
</style>

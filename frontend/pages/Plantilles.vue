<!--
  Component o pagina Nuxt: plantilles.
  Comentaris de codi: agents/frontend/AgentNuxt.md + AgentJavascript.md
-->
<template>
  <div class="templates-page min-h-screen bg-transparent p-6">
    <div class="max-w-7xl mx-auto">

      <div class="templates-filter-wrap mb-6" :class="{ 'templates-filter-wrap--searching': searchVisible }">
        <div class="templates-filter-row">
          <div class="templates-filter-search" :class="{ 'templates-filter-search--active': searchVisible }">
            <button
              type="button"
              class="templates-filter-decor"
              :aria-label="searchVisible ? 'Tancar cerca' : 'Obrir cerca'"
              @click="toggleSearch"
            >
              <svg
                class="templates-filter-decor__lupa"
                width="33"
                height="33"
                viewBox="0 0 33 33"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M28.875 28.875L22.8937 22.8937M26.125 15.125C26.125 21.2001 21.2001 26.125 15.125 26.125C9.04987 26.125 4.125 21.2001 4.125 15.125C4.125 9.04987 9.04987 4.125 15.125 4.125C21.2001 4.125 26.125 9.04987 26.125 15.125Z"
                  :stroke="searchVisible ? '#79D45D' : '#d8d8d8'"
                  stroke-width="4"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
              </svg>
            </button>
            <input
              v-if="searchVisible"
              ref="searchInput"
              v-model="searchQuery"
              type="text"
              class="templates-filter-search-input"
              :placeholder="$t('templates.filter_label')"
            />
          </div>

          <div class="templates-filter-card">
            <label for="filterTemplates" class="sr-only">{{ $t('templates.filter_label') }}</label>
            <select
              id="filterTemplates"
              v-model="selectedFilter"
              class="templates-filter-select"
            >
              <option value="all">{{ $t('templates.filter_all') || 'Totes' }}</option>
              <option value="default">{{ $t('templates.default') || 'Predefinides' }}</option>
              <option value="public">{{ $t('templates.public') || 'Públiques' }}</option>
              <option value="personals">{{ $t('templates.personal') || 'Personals' }}</option>
              <option value="friends">{{ $t('templates.friends') || 'Amics' }}</option>
              <option value="saved">{{ $t('templates.saved') || 'Guardades' }}</option>
            </select>
            <span class="templates-filter-chevron" aria-hidden="true"></span>
          </div>
        </div>
      </div>

      <!-- Estats de càrrega i error -->
      <div v-if="plantillaStore.loading" class="text-center py-10">
        <p class="text-gray-500">{{ $t('templates.loading') }}</p>
      </div>

      <div v-else-if="plantillaStore.error" class="text-center py-10 text-red-500">
        <p>{{ $t('templates.error_prefix') }}{{ plantillaStore.error }}</p>
      </div>

      <div v-else-if="baseFilteredPlantilles.length === 0" class="text-center py-6">
        <EmptyState
          title="No hi ha cap plantilla"
          description="Comença creant la teva primera plantilla per organitzar els teus hàbits preferits!"
          icon="logo"
        >
          <template #action>
            <button
              type="button"
              class="mt-4 inline-flex items-center justify-center rounded-xl border-2 border-[#6FBC58] bg-[#79D45D] px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:brightness-[0.97] cursor-pointer"
              @click="obrirSheetCrearPlantilla"
            >
              {{ $t('templates.create_sheet_heading') }}
            </button>
          </template>
        </EmptyState>
      </div>

      <div v-else class="space-y-6">
        <!-- 0. SECCIÓ PREDEFINIDES -->
        <div v-if="selectedFilter === 'all' || selectedFilter === 'default'" class="template-section">
          <div class="moment-divider mt-1 mb-4" role="presentation">
            <span class="moment-divider__line" aria-hidden="true"></span>
            <span class="moment-divider__text">{{ $t('templates.default') || 'Predefinides' }}</span>
            <span class="moment-divider__line" aria-hidden="true"></span>
          </div>

          <div v-if="plantillesDefault.length === 0" class="text-center py-6 text-white text-sm">
            No hi ha plantilles predefinides disponibles.
          </div>
          <div v-else class="templates-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
            <!-- Llista de targetes de plantilles predefinides -->
            <div
              v-for="plantilla in plantillesDefault"
              :key="plantilla.id"
              class="template-expandable"
              :class="isPlantillaExpandida(plantilla.id) ? 'template-expandable--active' : ''"
            >
              <button
                type="button"
                class="template-card w-full text-left"
                @click="togglePlantillaExpandida(plantilla.id)"
              >
                <div class="template-card__mark" aria-hidden="true">
                  <svg class="template-card__blob" width="57" height="54" viewBox="0 0 57 54" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M1.33093 20.8883C4.84845 8.52703 16.1404 0 28.9924 0H47.2749C52.2455 0 56.2749 4.02944 56.2749 9V25.1665C56.2749 28.2757 55.6987 31.358 54.5756 34.2573L54.3455 34.8512C50.0838 45.8525 39.4989 53.1035 27.701 53.1035H24.1663C14.0216 53.1035 4.95681 46.7675 1.4712 37.2404C-0.281291 32.4504 -0.473285 27.2287 0.922704 22.3229L1.33093 20.8883Z" :fill="getHabitColor({ categoria_id: plantilla.categoria })" />
                  </svg>
                  <span class="template-card__icona">{{ getCategoryIcon(plantilla.categoria) }}</span>
                </div>

                <div class="template-card__content">
                  <p class="template-card__title">{{ plantilla.titol }}</p>
                  <div class="template-card__meta">
                    <span class="template-card__meta-item">
                      <span aria-hidden="true">
                        <svg width="14" height="10" viewBox="0 0 14 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                          <path d="M4.33333 1H13M4.33333 5H13M4.33333 9H13M1 1H1.00667M1 5H1.00667M1 9H1.00667" stroke="#707070" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                      </span>
                      {{ (plantilla.habits && plantilla.habits.length) || 0 }} hàbits
                    </span>
                    <span class="template-card__meta-item">
                      <span aria-hidden="true">
                        <svg width="16" height="13" viewBox="0 0 16 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                          <path d="M0.666748 6.33333C0.666748 6.33333 3.33341 1 8.00008 1C12.6667 1 15.3334 6.33333 15.3334 6.33333C15.3334 6.33333 12.6667 11.6667 8.00008 11.6667C3.33341 11.6667 0.666748 6.33333 0.666748 6.33333Z" stroke="#707070" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                          <path d="M8.00008 8.33333C9.10465 8.33333 10.0001 7.4379 10.0001 6.33333C10.0001 5.22876 9.10465 4.33333 8.00008 4.33333C6.89551 4.33333 6.00008 5.22876 6.00008 6.33333C6.00008 7.4379 6.89551 8.33333 8.00008 8.33333Z" stroke="#707070" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                      </span>
                      {{ plantilla.esPublica ? 'Pública' : 'Privada' }}
                    </span>
                  </div>
                </div>
              </button>

              <!-- DESPLEGABLE EXPANDIT -->
              <div v-if="isPlantillaExpandida(plantilla.id)" class="template-expand-inline">
                <div class="template-expand-top">
                  <button class="template-expand-close" type="button" @click="tancarPlantillaExpandida">
                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path d="M11.2917 6.54167L6.54167 11.2917M6.54167 6.54167L11.2917 11.2917M16.8333 8.91667C16.8333 13.2889 13.2889 16.8333 8.91667 16.8333C4.54441 16.8333 1 13.2889 1 8.91667C1 4.54441 4.54441 1 8.91667 1C13.2889 1 16.8333 4.54441 16.8333 8.91667Z" stroke="#FAF9F9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </button>
                  <!-- No pot editar-se, no es mostra botó d'editar -->
                </div>

                <div class="template-expand-panel">
                  <div class="template-spec-card">
                    <div class="template-expand-actions">
                      <button type="button" class="template-expand-btn template-expand-btn--import" @click="obrirModalImportarHabits(plantilla)">Importar</button>
                      <button type="button" class="template-expand-btn template-expand-btn--delete" @click="eliminarPlantilla(plantilla.id)">Eliminar</button>
                    </div>
                  </div>

                  <div class="moment-divider moment-divider--expanded-habits" role="presentation">
                    <span class="moment-divider__line" aria-hidden="true"></span>
                    <span class="moment-divider__text">{{ $t('templates.expanded_habits_section') }}</span>
                    <span class="moment-divider__line" aria-hidden="true"></span>
                  </div>

                  <div class="template-habits-stack">
                    <p v-if="!plantilla.habits || plantilla.habits.length === 0" class="template-habits-list__empty">{{ $t('templates.no_habits_to_select') }}</p>
                    <button v-for="habit in plantilla.habits" :key="habit.id" type="button" class="template-habit-card">
                      <span class="template-habit-card__mark" aria-hidden="true">
                        <svg class="template-habit-card__blob" width="56" height="40" viewBox="0 0 56 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                          <path d="M1.64885 13.8624C4.80033 5.5202 12.7867 0 21.7043 0H46.8857C51.8563 0 55.8857 4.02944 55.8857 9V18.1149C55.8857 20.9967 55.1982 23.8369 53.8802 26.3997L53.3295 27.4705C49.3729 35.1639 41.4476 40 32.7964 40H18.4113C11.3613 40 4.93035 35.9742 1.85018 29.6327C-0.361252 25.0797 -0.600734 19.8171 1.18804 15.0821L1.64885 13.8624Z" :fill="habit.color || '#79D45D'" />
                        </svg>
                        <span class="template-habit-card__icona">{{ habit.icona || '✨' }}</span>
                      </span>
                      <span class="template-habit-card__content">
                        <span class="template-habit-card__title">{{ habit.nom || habit.titol }}</span>
                      </span>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- 1. SECCIÓ PÚBLIQUES -->
        <div v-if="selectedFilter === 'all' || selectedFilter === 'public'" class="template-section">
          <div class="moment-divider mt-1 mb-4" role="presentation">
            <span class="moment-divider__line" aria-hidden="true"></span>
            <span class="moment-divider__text">{{ $t('templates.public') || 'Públiques' }}</span>
            <span class="moment-divider__line" aria-hidden="true"></span>
          </div>

          <div v-if="plantillesPubliques.length === 0" class="text-center py-6 text-white text-sm">
            No hi ha plantilles públiques disponibles.
          </div>
          <div v-else class="templates-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
            <button
              v-if="selectedFilter === 'public' || selectedFilter === 'all'"
              type="button"
              class="create-category-trigger create-category-trigger--grid"
              :aria-expanded="plantillaSheetObert ? 'true' : 'false'"
              @click="obrirSheetCrearPlantilla"
            >
              <span class="create-category-trigger__icon" aria-hidden="true">
                <svg width="33" height="33" viewBox="0 0 33 33" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <line x1="17" y1="2" x2="17" y2="31" stroke="white" stroke-width="4" stroke-linecap="round"/>
                  <line x1="2" y1="16" x2="31" y2="16" stroke="white" stroke-width="4" stroke-linecap="round"/>
                </svg>
              </span>
            </button>

            <!-- Llista de targetes de plantilles públiques -->
            <div
              v-for="plantilla in plantillesPubliques"
              :key="plantilla.id"
              class="template-expandable"
              :class="isPlantillaExpandida(plantilla.id) ? 'template-expandable--active' : ''"
            >
              <button
                type="button"
                class="template-card w-full text-left"
                @click="togglePlantillaExpandida(plantilla.id)"
              >
                <div class="template-card__mark" aria-hidden="true">
                  <svg class="template-card__blob" width="57" height="54" viewBox="0 0 57 54" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M1.33093 20.8883C4.84845 8.52703 16.1404 0 28.9924 0H47.2749C52.2455 0 56.2749 4.02944 56.2749 9V25.1665C56.2749 28.2757 55.6987 31.358 54.5756 34.2573L54.3455 34.8512C50.0838 45.8525 39.4989 53.1035 27.701 53.1035H24.1663C14.0216 53.1035 4.95681 46.7675 1.4712 37.2404C-0.281291 32.4504 -0.473285 27.2287 0.922704 22.3229L1.33093 20.8883Z" :fill="getHabitColor({ categoria_id: plantilla.categoria })" />
                  </svg>
                  <span class="template-card__icona">{{ getCategoryIcon(plantilla.categoria) }}</span>
                </div>

                <div class="template-card__content">
                  <p class="template-card__title">{{ plantilla.titol }}</p>
                  <div class="template-card__meta">
                    <span class="template-card__meta-item">
                      <span aria-hidden="true">
                        <svg width="14" height="10" viewBox="0 0 14 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                          <path d="M4.33333 1H13M4.33333 5H13M4.33333 9H13M1 1H1.00667M1 5H1.00667M1 9H1.00667" stroke="#707070" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                      </span>
                      {{ (plantilla.habits && plantilla.habits.length) || 0 }} hàbits
                    </span>
                    <span class="template-card__meta-item">
                      <span aria-hidden="true">
                        <svg width="16" height="13" viewBox="0 0 16 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                          <path d="M0.666748 6.33333C0.666748 6.33333 3.33341 1 8.00008 1C12.6667 1 15.3334 6.33333 15.3334 6.33333C15.3334 6.33333 12.6667 11.6667 8.00008 11.6667C3.33341 11.6667 0.666748 6.33333 0.666748 6.33333Z" stroke="#707070" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                          <path d="M8.00008 8.33333C9.10465 8.33333 10.0001 7.4379 10.0001 6.33333C10.0001 5.22876 9.10465 4.33333 8.00008 4.33333C6.89551 4.33333 6.00008 5.22876 6.00008 6.33333C6.00008 7.4379 6.89551 8.33333 8.00008 8.33333Z" stroke="#707070" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                      </span>
                      {{ plantilla.esPublica ? 'Pública' : 'Privada' }}
                    </span>
                  </div>
                </div>
              </button>

              <!-- DESPLEGABLE EXPANDIT -->
              <div v-if="isPlantillaExpandida(plantilla.id)" class="template-expand-inline">
                <div class="template-expand-top">
                  <button class="template-expand-close" type="button" @click="tancarPlantillaExpandida">
                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path d="M11.2917 6.54167L6.54167 11.2917M6.54167 6.54167L11.2917 11.2917M16.8333 8.91667C16.8333 13.2889 13.2889 16.8333 8.91667 16.8333C4.54441 16.8333 1 13.2889 1 8.91667C1 4.54441 4.54441 1 8.91667 1C13.2889 1 16.8333 4.54441 16.8333 8.91667Z" stroke="#FAF9F9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </button>
                  <button class="template-expand-edit" type="button" @click="editarPlantilla(plantilla.id)">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path d="M7 2.41421H2.33333C1.97971 2.41421 1.64057 2.55469 1.39052 2.80474C1.14048 3.05479 1 3.39392 1 3.74755V13.0809C1 13.4345 1.14048 13.7736 1.39052 14.0237C1.64057 14.2737 1.97971 14.4142 2.33333 14.4142H11.6667C12.0203 14.4142 12.3594 14.2737 12.6095 14.0237C12.8595 13.7736 13 13.4345 13 13.0809V8.41421M12 1.41421C12.2652 1.149 12.6249 1 13 1C13.3751 1 13.7348 1.149 14 1.41421C14.2652 1.67943 14.4142 2.03914 14.4142 2.41421C14.4142 2.7892: 14.2652 3.149 14 3.41421L7.66667 9.74755L5 10.4142L5.66667 7.74755L12 1.41421Z" stroke="#FAF9F9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span>Editar Plantilla</span>
                  </button>
                </div>

                <div class="template-expand-panel">
                  <div class="template-spec-card">
                    <div class="template-expand-actions">
                      <button type="button" class="template-expand-btn template-expand-btn--forum" @click="exportarAForum(plantilla)">Exportar</button>
                      <button type="button" class="template-expand-btn template-expand-btn--import" @click="obrirModalImportarHabits(plantilla)">Importar</button>
                      <button type="button" class="template-expand-btn template-expand-btn--delete" @click="eliminarPlantilla(plantilla.id)">Eliminar</button>
                    </div>
                  </div>

                  <div class="moment-divider moment-divider--expanded-habits" role="presentation">
                    <span class="moment-divider__line" aria-hidden="true"></span>
                    <span class="moment-divider__text">{{ $t('templates.expanded_habits_section') }}</span>
                    <span class="moment-divider__line" aria-hidden="true"></span>
                  </div>

                  <div class="template-habits-stack">
                    <p v-if="!plantilla.habits || plantilla.habits.length === 0" class="template-habits-list__empty">{{ $t('templates.no_habits_to_select') }}</p>
                    <button v-for="habit in plantilla.habits" :key="habit.id" type="button" class="template-habit-card">
                      <span class="template-habit-card__mark" aria-hidden="true">
                        <svg class="template-habit-card__blob" width="56" height="40" viewBox="0 0 56 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                          <path d="M1.64885 13.8624C4.80033 5.5202 12.7867 0 21.7043 0H46.8857C51.8563 0 55.8857 4.02944 55.8857 9V18.1149C55.8857 20.9967 55.1982 23.8369 53.8802 26.3997L53.3295 27.4705C49.3729 35.1639 41.4476 40 32.7964 40H18.4113C11.3613 40 4.93035 35.9742 1.85018 29.6327C-0.361252 25.0797 -0.600734 19.8171 1.18804 15.0821L1.64885 13.8624Z" :fill="habit.color || '#79D45D'" />
                        </svg>
                        <span class="template-habit-card__icona">{{ habit.icona || '✨' }}</span>
                      </span>
                      <span class="template-habit-card__content">
                        <span class="template-habit-card__title">{{ habit.nom || habit.titol }}</span>
                      </span>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- 2. SECCIÓ PERSONALS -->
        <div v-if="selectedFilter === 'all' || selectedFilter === 'personals'" class="template-section">
          <div class="moment-divider mt-1 mb-4" role="presentation">
            <span class="moment-divider__line" aria-hidden="true"></span>
            <span class="moment-divider__text">{{ $t('templates.personal') || 'Personals' }}</span>
            <span class="moment-divider__line" aria-hidden="true"></span>
          </div>

          <div v-if="plantillesPersonals.length === 0" class="text-center py-6 text-white text-sm">
            No tens plantilles personals creades.
          </div>
          <div v-else class="templates-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
            <button
              v-if="selectedFilter === 'personals'"
              type="button"
              class="create-category-trigger create-category-trigger--grid"
              :aria-expanded="plantillaSheetObert ? 'true' : 'false'"
              @click="obrirSheetCrearPlantilla"
            >
              <span class="create-category-trigger__icon" aria-hidden="true">
                <svg width="33" height="33" viewBox="0 0 33 33" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <line x1="17" y1="2" x2="17" y2="31" stroke="white" stroke-width="4" stroke-linecap="round"/>
                  <line x1="2" y1="16" x2="31" y2="16" stroke="white" stroke-width="4" stroke-linecap="round"/>
                </svg>
              </span>
            </button>

            <!-- Llista de targetes de plantilles personals -->
            <div
              v-for="plantilla in plantillesPersonals"
              :key="plantilla.id"
              class="template-expandable"
              :class="isPlantillaExpandida(plantilla.id) ? 'template-expandable--active' : ''"
            >
              <button type="button" class="template-card w-full text-left" @click="togglePlantillaExpandida(plantilla.id)">
                <div class="template-card__mark" aria-hidden="true">
                  <svg class="template-card__blob" width="57" height="54" viewBox="0 0 57 54" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M1.33093 20.8883C4.84845 8.52703 16.1404 0 28.9924 0H47.2749C52.2455 0 56.2749 4.02944 56.2749 9V25.1665C56.2749 28.2757 55.6987 31.358 54.5756 34.2573L54.3455 34.8512C50.0838 45.8525 39.4989 53.1035 27.701 53.1035H24.1663C14.0216 53.1035 4.95681 46.7675 1.4712 37.2404C-0.281291 32.4504 -0.473285 27.2287 0.922704 22.3229L1.33093 20.8883Z" :fill="getHabitColor({ categoria_id: plantilla.categoria })" />
                  </svg>
                  <span class="template-card__icona">{{ getCategoryIcon(plantilla.categoria) }}</span>
                </div>

                <div class="template-card__content">
                  <p class="template-card__title">{{ plantilla.titol }}</p>
                  <div class="template-card__meta">
                    <span class="template-card__meta-item">
                      <span aria-hidden="true">
                        <svg width="14" height="10" viewBox="0 0 14 10" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4.33333 1H13M4.33333 5H13M4.33333 9H13M1 1H1.00667M1 5H1.00667M1 9H1.00667" stroke="#707070" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                      </span>
                      {{ (plantilla.habits && plantilla.habits.length) || 0 }} hàbits
                    </span>
                    <span class="template-card__meta-item">
                      <span aria-hidden="true">
                        <svg width="16" height="13" viewBox="0 0 16 13" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M0.666748 6.33333C0.666748 6.33333 3.33341 1 8.00008 1C12.6667 1 15.3334 6.33333 15.3334 6.33333C15.3334 6.33333 12.6667 11.6667 8.00008 11.6667C3.33341 11.6667 0.666748 6.33333 0.666748 6.33333Z" stroke="#707070" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M8.00008 8.33333C9.10465 8.33333 10.0001 7.4379 10.0001 6.33333C10.0001 5.22876 9.10465 4.33333 8.00008 4.33333C6.89551 4.33333 6.00008 5.22876 6.00008 6.33333C6.00008 7.4379 6.89551 8.33333 8.00008 8.33333Z" stroke="#707070" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                      </span>
                      {{ plantilla.esPublica ? 'Pública' : 'Privada' }}
                    </span>
                  </div>
                </div>
              </button>

              <div v-if="isPlantillaExpandida(plantilla.id)" class="template-expand-inline">
                <div class="template-expand-top">
                  <button class="template-expand-close" type="button" @click="tancarPlantillaExpandida">
                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M11.2917 6.54167L6.54167 11.2917M6.54167 6.54167L11.2917 11.2917M16.8333 8.91667C16.8333 13.2889 13.2889 16.8333 8.91667 16.8333C4.54441 16.8333 1 13.2889 1 8.91667C1 4.54441 4.54441 1 8.91667 1C13.2889 1 16.8333 4.54441 16.8333 8.91667Z" stroke="#FAF9F9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                  </button>
                  <button class="template-expand-edit" type="button" @click="editarPlantilla(plantilla.id)">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7 2.41421H2.33333C1.97971 2.41421 1.64057 2.55469 1.39052 2.80474C1.14048 3.05479 1 3.39392 1 3.74755V13.0809C1 13.4345 1.14048 13.7736 1.39052 14.0237C1.64057 14.2737 1.97971 14.4142 2.33333 14.4142H11.6667C12.0203 14.4142 12.3594 14.2737 12.6095 14.0237C12.8595 13.7736 13 13.4345 13 13.0809V8.41421M12 1.41421C12.2652 1.149 12.6249 1 13 1C13.3751 1 13.7348 1.149 14 1.41421C14.2652 1.67943 14.4142 2.03914 14.4142 2.41421C14.4142 2.78929 14.2652 3.149 14 3.41421L7.66667 9.74755L5 10.4142L5.66667 7.74755L12 1.41421Z" stroke="#FAF9F9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span>Editar Plantilla</span>
                  </button>
                </div>

                <div class="template-expand-panel">
                  <div class="template-spec-card">
                    <div class="template-expand-actions">
                      <button type="button" class="template-expand-btn template-expand-btn--forum" @click="exportarAForum(plantilla)">Exportar</button>
                      <button type="button" class="template-expand-btn template-expand-btn--import" @click="obrirModalImportarHabits(plantilla)">Importar</button>
                      <button type="button" class="template-expand-btn template-expand-btn--delete" @click="eliminarPlantilla(plantilla.id)">Eliminar</button>
                    </div>
                  </div>

                  <div class="moment-divider moment-divider--expanded-habits" role="presentation">
                    <span class="moment-divider__line" aria-hidden="true"></span>
                    <span class="moment-divider__text">{{ $t('templates.expanded_habits_section') }}</span>
                    <span class="moment-divider__line" aria-hidden="true"></span>
                  </div>

                  <div class="template-habits-stack">
                    <p v-if="!plantilla.habits || plantilla.habits.length === 0" class="template-habits-list__empty">{{ $t('templates.no_habits_to_select') }}</p>
                    <button v-for="habit in plantilla.habits" :key="habit.id" type="button" class="template-habit-card">
                      <span class="template-habit-card__mark" aria-hidden="true">
                        <svg class="template-habit-card__blob" width="56" height="40" viewBox="0 0 56 40" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1.64885 13.8624C4.80033 5.5202 12.7867 0 21.7043 0H46.8857C51.8563 0 55.8857 4.02944 55.8857 9V18.1149C55.8857 20.9967 55.1982 23.8369 53.8802 26.3997L53.3295 27.4705C49.3729 35.1639 41.4476 40 32.7964 40H18.4113C11.3613 40 4.93035 35.9742 1.85018 29.6327C-0.361252 25.0797 -0.600734 19.8171 1.18804 15.0821L1.64885 13.8624Z" :fill="habit.color || '#79D45D'" /></svg>
                        <span class="template-habit-card__icona">{{ habit.icona || '✨' }}</span>
                      </span>
                      <span class="template-habit-card__content">
                        <span class="template-habit-card__title">{{ habit.nom || habit.titol }}</span>
                      </span>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- 3. SECCIÓ D'AMICS -->
        <div v-if="selectedFilter === 'friends' || (selectedFilter === 'all' && plantillesAmics.length > 0)" class="template-section">
          <div class="moment-divider mt-1 mb-4" role="presentation">
            <span class="moment-divider__line" aria-hidden="true"></span>
            <span class="moment-divider__text">{{ $t('templates.friends') || 'Amics' }}</span>
            <span class="moment-divider__line" aria-hidden="true"></span>
          </div>

          <div v-if="plantillesAmics.length === 0" class="text-center py-6 text-white text-sm">
            No hi ha plantilles d'amics disponibles.
          </div>
          <div v-else class="templates-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
            <div
              v-for="plantilla in plantillesAmics"
              :key="plantilla.id"
              class="template-expandable"
              :class="isPlantillaExpandida(plantilla.id) ? 'template-expandable--active' : ''"
            >
              <button type="button" class="template-card w-full text-left" @click="togglePlantillaExpandida(plantilla.id)">
                <div class="template-card__mark" aria-hidden="true">
                  <svg class="template-card__blob" width="57" height="54" viewBox="0 0 57 54" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M1.33093 20.8883C4.84845 8.52703 16.1404 0 28.9924 0H47.2749C52.2455 0 56.2749 4.02944 56.2749 9V25.1665C56.2749 28.2757 55.6987 31.358 54.5756 34.2573L54.3455 34.8512C50.0838 45.8525 39.4989 53.1035 27.701 53.1035H24.1663C14.0216 53.1035 4.95681 46.7675 1.4712 37.2404C-0.281291 32.4504 -0.473285 27.2287 0.922704 22.3229L1.33093 20.8883Z" :fill="getHabitColor({ categoria_id: plantilla.categoria })" />
                  </svg>
                  <span class="template-card__icona">{{ getCategoryIcon(plantilla.categoria) }}</span>
                </div>

                <div class="template-card__content">
                  <p class="template-card__title">{{ plantilla.titol }}</p>
                  <div class="template-card__meta">
                    <span class="template-card__meta-item">
                      <span aria-hidden="true">
                        <svg width="14" height="10" viewBox="0 0 14 10" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4.33333 1H13M4.33333 5H13M4.33333 9H13M1 1H1.00667M1 5H1.00667M1 9H1.00667" stroke="#707070" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                      </span>
                      {{ (plantilla.habits && plantilla.habits.length) || 0 }} hàbits
                    </span>
                    <span class="template-card__meta-item font-bold text-blue-600">
                      👤 {{ plantilla.creadorNom || 'Amic' }}
                    </span>
                  </div>
                </div>
              </button>

              <div v-if="isPlantillaExpandida(plantilla.id)" class="template-expand-inline">
                <div class="template-expand-top">
                  <button class="template-expand-close" type="button" @click="tancarPlantillaExpandida">
                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M11.2917 6.54167L6.54167 11.2917M6.54167 6.54167L11.2917 11.2917M16.8333 8.91667C16.8333 13.2889 13.2889 16.8333 8.91667 16.8333C4.54441 16.8333 1 13.2889 1 8.91667C1 4.54441 4.54441 1 8.91667 1C13.2889 1 16.8333 4.54441 16.8333 8.91667Z" stroke="#FAF9F9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                  </button>
                </div>

                <div class="template-expand-panel">
                  <div class="template-spec-card">
                    <div class="template-expand-actions">
                      <button type="button" class="template-expand-btn template-expand-btn--import" @click="obrirModalImportarHabits(plantilla)">Importar</button>
                    </div>
                  </div>

                  <div class="moment-divider moment-divider--expanded-habits" role="presentation">
                    <span class="moment-divider__line" aria-hidden="true"></span>
                    <span class="moment-divider__text">{{ $t('templates.expanded_habits_section') }}</span>
                    <span class="moment-divider__line" aria-hidden="true"></span>
                  </div>

                  <div class="template-habits-stack">
                    <p v-if="!plantilla.habits || plantilla.habits.length === 0" class="template-habits-list__empty">{{ $t('templates.no_habits_to_select') }}</p>
                    <button v-for="habit in plantilla.habits" :key="habit.id" type="button" class="template-habit-card">
                      <span class="template-habit-card__mark" aria-hidden="true">
                        <svg class="template-habit-card__blob" width="56" height="40" viewBox="0 0 56 40" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1.64885 13.8624C4.80033 5.5202 12.7867 0 21.7043 0H46.8857C51.8563 0 55.8857 4.02944 55.8857 9V18.1149C55.8857 20.9967 55.1982 23.8369 53.8802 26.3997L53.3295 27.4705C49.3729 35.1639 41.4476 40 32.7964 40H18.4113C11.3613 40 4.93035 35.9742 1.85018 29.6327C-0.361252 25.0797 -0.600734 19.8171 1.18804 15.0821L1.64885 13.8624Z" :fill="habit.color || '#79D45D'" /></svg>
                        <span class="template-habit-card__icona">{{ habit.icona || '✨' }}</span>
                      </span>
                      <span class="template-habit-card__content">
                        <span class="template-habit-card__title">{{ habit.nom || habit.titol }}</span>
                      </span>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- 4. SECCIÓ GUARDADES -->
        <div v-if="selectedFilter === 'saved' || (selectedFilter === 'all' && plantillesGuardades.length > 0)" class="template-section">
          <div class="moment-divider mt-1 mb-4" role="presentation">
            <span class="moment-divider__line" aria-hidden="true"></span>
            <span class="moment-divider__text">{{ $t('templates.saved') || 'Guardades' }}</span>
            <span class="moment-divider__line" aria-hidden="true"></span>
          </div>

          <div v-if="plantillesGuardades.length === 0" class="text-center py-6 text-white text-sm">
            No tens plantilles guardades o importades del fòrum.
          </div>
          <div v-else class="templates-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
            <div
              v-for="plantilla in plantillesGuardades"
              :key="plantilla.id"
              class="template-expandable"
              :class="isPlantillaExpandida(plantilla.id) ? 'template-expandable--active' : ''"
            >
              <button type="button" class="template-card w-full text-left" @click="togglePlantillaExpandida(plantilla.id)">
                <div class="template-card__mark" aria-hidden="true">
                  <svg class="template-card__blob" width="57" height="54" viewBox="0 0 57 54" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M1.33093 20.8883C4.84845 8.52703 16.1404 0 28.9924 0H47.2749C52.2455 0 56.2749 4.02944 56.2749 9V25.1665C56.2749 28.2757 55.6987 31.358 54.5756 34.2573L54.3455 34.8512C50.0838 45.8525 39.4989 53.1035 27.701 53.1035H24.1663C14.0216 53.1035 4.95681 46.7675 1.4712 37.2404C-0.281291 32.4504 -0.473285 27.2287 0.922704 22.3229L1.33093 20.8883Z" :fill="getHabitColor({ categoria_id: plantilla.categoria })" />
                  </svg>
                  <span class="template-card__icona">{{ getCategoryIcon(plantilla.categoria) }}</span>
                </div>

                <div class="template-card__content">
                  <p class="template-card__title">{{ plantilla.titol }}</p>
                  <div class="template-card__meta">
                    <span class="template-card__meta-item">
                      <span aria-hidden="true">
                        <svg width="14" height="10" viewBox="0 0 14 10" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4.33333 1H13M4.33333 5H13M4.33333 9H13M1 1H1.00667M1 5H1.00667M1 9H1.00667" stroke="#707070" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                      </span>
                      {{ (plantilla.habits && plantilla.habits.length) || 0 }} hàbits
                    </span>
                    <span class="template-card__meta-item font-bold text-purple-600">
                      🏷️ Importada
                    </span>
                  </div>
                </div>
              </button>

              <div v-if="isPlantillaExpandida(plantilla.id)" class="template-expand-inline">
                <div class="template-expand-top">
                  <button class="template-expand-close" type="button" @click="tancarPlantillaExpandida">
                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M11.2917 6.54167L6.54167 11.2917M6.54167 6.54167L11.2917 11.2917M16.8333 8.91667C16.8333 13.2889 13.2889 16.8333 8.91667 16.8333C4.54441 16.8333 1 13.2889 1 8.91667C1 4.54441 4.54441 1 8.91667 1C13.2889 1 16.8333 4.54441 16.8333 8.91667Z" stroke="#FAF9F9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                  </button>
                  <button class="template-expand-edit" type="button" @click="editarPlantilla(plantilla.id)">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7 2.41421H2.33333C1.97971 2.41421 1.64057 2.55469 1.39052 2.80474C1.14048 3.05479 1 3.39392 1 3.74755V13.0809C1 13.4345 1.14048 13.7736 1.39052 14.0237C1.64057 14.2737 1.97971 14.4142 2.33333 14.4142H11.6667C12.0203 14.4142 12.3594 14.2737 12.6095 14.0237C12.8595 13.7736 13 13.4345 13 13.0809V8.41421M12 1.41421C12.2652 1.149 12.6249 1 13 1C13.3751 1 13.7348 1.149 14 1.41421C14.2652 1.67943 14.4142 2.03914 14.4142 2.41421C14.4142 2.78929 14.2652 3.149 14 3.41421L7.66667 9.74755L5 10.4142L5.66667 7.74755L12 1.41421Z" stroke="#FAF9F9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span>Editar Plantilla</span>
                  </button>
                </div>

                <div class="template-expand-panel">
                  <div class="template-spec-card">
                    <div class="template-expand-actions">
                      <button type="button" class="template-expand-btn template-expand-btn--forum" @click="exportarAForum(plantilla)">Exportar</button>
                      <button type="button" class="template-expand-btn template-expand-btn--import" @click="obrirModalImportarHabits(plantilla)">Importar</button>
                      <button type="button" class="template-expand-btn template-expand-btn--delete" @click="eliminarPlantilla(plantilla.id)">Eliminar</button>
                    </div>
                  </div>

                  <div class="moment-divider moment-divider--expanded-habits" role="presentation">
                    <span class="moment-divider__line" aria-hidden="true"></span>
                    <span class="moment-divider__text">{{ $t('templates.expanded_habits_section') }}</span>
                    <span class="moment-divider__line" aria-hidden="true"></span>
                  </div>

                  <div class="template-habits-stack">
                    <p v-if="!plantilla.habits || plantilla.habits.length === 0" class="template-habits-list__empty">{{ $t('templates.no_habits_to_select') }}</p>
                    <button v-for="habit in plantilla.habits" :key="habit.id" type="button" class="template-habit-card">
                      <span class="template-habit-card__mark" aria-hidden="true">
                        <svg class="template-habit-card__blob" width="56" height="40" viewBox="0 0 56 40" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1.64885 13.8624C4.80033 5.5202 12.7867 0 21.7043 0H46.8857C51.8563 0 55.8857 4.02944 55.8857 9V18.1149C55.8857 20.9967 55.1982 23.8369 53.8802 26.3997L53.3295 27.4705C49.3729 35.1639 41.4476 40 32.7964 40H18.4113C11.3613 40 4.93035 35.9742 1.85018 29.6327C-0.361252 25.0797 -0.600734 19.8171 1.18804 15.0821L1.64885 13.8624Z" :fill="habit.color || '#79D45D'" /></svg>
                        <span class="template-habit-card__icona">{{ habit.icona || '✨' }}</span>
                      </span>
                      <span class="template-habit-card__content">
                        <span class="template-habit-card__title">{{ habit.nom || habit.titol }}</span>
                      </span>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <Teleport to="body">
      <!-- Modal per crear/editar plantilla -->
      <div
        v-if="modalVisible"
        class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex justify-center items-center z-[100]"
        @click.self="tancar"
      >
        <div
          class="relative bg-white rounded-2xl shadow-xl p-8 m-4 max-w-2xl w-full"
        >
          <button
            @click="tancar"
            class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 text-2xl font-bold"
          >
            &times;
          </button>

          <h2 class="2xl font-bold text-gray-800 mb-6">
            {{ modoEdicio ? $t('templates.edit_title') : $t('templates.create_title') }}
          </h2>

          <div class="space-y-6">
            <!-- Nom de la Plantilla -->
            <div>
              <label
                class="habit-form-label"
                for="titol"
                >{{ $t('templates.name_label') }}</label
              >
              <input
                id="titol"
                v-model="form.titol"
                type="text"
                :placeholder="$t('templates.name_placeholder')"
                class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-500 focus:bg-white transition-all"
              />
            </div>

            <!-- Categoria -->
            <div>
              <UserHabitsHabitFormCategory
                embedded
                :categories="categories"
                :user-categories="userCategories"
                :selected-id="form.categoria"
                :category-custom-label="form.userCategoriaEtiqueta || ''"
                :category-custom-icona="form.icona"
                :selected-user-category-id="form.userCategoriaId"
                @select="seleccionarCategoria"
                @select-user="seleccionarCategoriaUsuari"
                @add-user-category="afegirCategoriaUsuari"
              />
            </div>

            <!-- Plantilla pública -->
            <div>
              <label class="habit-form-label" for="esPublica">{{ $t('templates.public_checkbox') }}</label>
              <SharedTemplatePublicSwitch
                input-id="esPublica"
                :model-value="form.esPublica"
                @update:model-value="form.esPublica = $event"
              />
            </div>

            <!-- Selecció d'Hàbits -->
            <div>
              <h3 class="habit-form-label">{{ $t('templates.select_habits') }}</h3>
              <div v-if="habitStore.loading" class="text-center py-4">
                <p class="text-gray-500">{{ $t('home.loading_habits') }}</p>
              </div>
              <div
                v-else-if="habitStore.error"
                class="text-center py-4 text-red-500"
              >
                <p>{{ $t('templates.error_prefix') }}{{ habitStore.error }}</p>
              </div>
              <div
                v-else-if="habitStore.habits.length === 0"
                class="text-center py-4 text-gray-400"
              >
                <p>{{ $t('templates.no_habits_to_select') }}</p>
              </div>
              <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-80 overflow-y-auto pr-2">
                <button
                  v-for="habit in habitStore.habits"
                  :key="habit.id"
                  type="button"
                  class="template-habit-selection-card"
                  :class="{ 'template-habit-selection-card--selected': form.habitsSeleccionats.indexOf(habit.id) !== -1 }"
                  @click="toggleHabitSeleccionat(habit.id)"
                >
                  <div class="template-habit-selection-card__check">
                    <SharedMissionStyleCheckIcon :selected="form.habitsSeleccionats.indexOf(habit.id) !== -1" :size="32" />
                  </div>
                  <div class="template-habit-selection-card__content">
                    <div class="template-habit-selection-card__icon-blob">
                      <svg class="template-habit-selection-card__blob-svg" viewBox="0 0 56 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M1.64885 13.8624C4.80033 5.5202 12.7867 0 21.7043 0H46.8857C51.8563 0 55.8857 4.02944 55.8857 9V18.1149C55.8857 20.9967 55.1982 23.8369 53.8802 26.3997L53.3295 27.4705C49.3729 35.1639 41.4476 40 32.7964 40H18.4113C11.3613 40 4.93035 35.9742 1.85018 29.6327C-0.361252 25.0797 -0.600734 19.8171 1.18804 15.0821L1.64885 13.8624Z" :fill="getHabitColor(habit)" />
                      </svg>
                      <span class="template-habit-selection-card__emoji">{{ habit.icona || '💧' }}</span>
                    </div>
                    <span class="template-habit-selection-card__name">{{ habit.nom || habit.titol }}</span>
                  </div>
                </button>
              </div>
            </div>

            <!-- Botó Eliminar (només en mode edició) -->
            <div v-if="modoEdicio" class="mb-2">
              <button
                type="button"
                @click="eliminarPlantilla(plantillaAEditar.id)"
                class="w-full py-3 bg-red-50 text-red-500 rounded-xl font-bold border border-red-100 hover:bg-red-100 transition-colors uppercase text-sm tracking-wider"
              >
                {{ $t('templates.delete_button') }}
              </button>
            </div>

            <!-- Botons d'Acció -->
            <div class="flex justify-end gap-3 mt-8">
              <button
                @click="tancar"
                class="px-6 py-3 border border-gray-300 rounded-xl text-gray-700 hover:bg-gray-100 transition-colors"
              >
                {{ $t('habits.cancel') }}
              </button>
              <button
                @click="guardarPlantilla"
                class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-bold rounded-xl shadow-lg transition-all transform active:scale-95"
              >
                <template v-if="modoEdicio">{{ $t('templates.update_button') }}</template>
                <template v-else>{{ $t('templates.create_button') }}</template>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal per importar hàbits de plantilla -->
      <div
        v-if="modalImportarVisible"
        class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex justify-center items-center z-[100]"
        @click.self="tancarModalImportar"
      >
        <div
          class="relative bg-white rounded-2xl shadow-xl p-8 m-4 max-w-2xl w-full"
        >
          <button
            @click="tancarModalImportar"
            class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 text-2xl"
          >
            &times;
          </button>

          <h2 class="habit-form-label text-gray-800 text-2xl mb-6">
            {{ $t('templates.import_title', { titol: plantillaAImportar ? plantillaAImportar.titol : '' }) }}
          </h2>

          <div class="space-y-6">
            <!-- Selecció d'Hàbits per importar -->
            <div>
              <h3 class="habit-form-label text-gray-800 mb-4">{{ $t('templates.import_select_habits') }}</h3>
              <div v-if="!plantillaAImportar || !plantillaAImportar.habits || plantillaAImportar.habits.length === 0" class="text-center py-4 text-gray-400">
                <p>{{ $t('templates.import_no_habits') }}</p>
              </div>
              <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-80 overflow-y-auto pr-2">
                <button
                  v-for="habit in plantillaAImportar.habits"
                  :key="habit.id"
                  type="button"
                  class="template-habit-selection-card"
                  :class="{ 'template-habit-selection-card--selected': habitsAImportarSeleccionats.indexOf(habit.id) !== -1 }"
                  @click="toggleHabitAImportarSeleccionat(habit.id)"
                >
                  <div class="template-habit-selection-card__check">
                    <SharedMissionStyleCheckIcon :selected="habitsAImportarSeleccionats.indexOf(habit.id) !== -1" :size="32" />
                  </div>
                  <div class="template-habit-selection-card__content">
                    <div class="template-habit-selection-card__icon-blob">
                      <svg class="template-habit-selection-card__blob-svg" viewBox="0 0 56 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M1.64885 13.8624C4.80033 5.5202 12.7867 0 21.7043 0H46.8857C51.8563 0 55.8857 4.02944 55.8857 9V18.1149C55.8857 20.9967 55.1982 23.8369 53.8802 26.3997L53.3295 27.4705C49.3729 35.1639 41.4476 40 32.7964 40H18.4113C11.3613 40 4.93035 35.9742 1.85018 29.6327C-0.361252 25.0797 -0.600734 19.8171 1.18804 15.0821L1.64885 13.8624Z" :fill="getHabitColor(habit)" />
                      </svg>
                      <span class="template-habit-selection-card__emoji">{{ habit.icona || '💧' }}</span>
                    </div>
                    <span class="template-habit-selection-card__name">{{ habit.nom || habit.titol }}</span>
                  </div>
                </button>
              </div>
            </div>

            <!-- Botons d'Acció per importar -->
            <div class="flex justify-end gap-3 mt-8">
              <button
                @click="tancarModalImportar"
                class="px-6 py-3 border border-gray-300 rounded-xl text-gray-700 hover:bg-gray-100 transition-colors"
              >
                {{ $t('habits.cancel') }}
              </button>
              <button
                @click="confirmarImportacioHabits"
                class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-xl shadow-lg transition-all transform active:scale-95"
              >
                {{ $t('templates.import_button') }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal per eliminar plantilla -->
      <Teleport to="body">
        <ConfirmModal
          :show="modalEliminarVisible"
          title="Eliminar plantilla?"
          :message="'Estàs segur que vols eliminar la plantilla ' + (plantillaAEliminar ? plantillaAEliminar.titol : '') + '? Aquesta acció no es pot desfer.'"
          confirm-text="Eliminar"
          @confirm="confirmarEliminar"
          @cancel="tancarModalEliminar"
        />
      </Teleport>
    </Teleport>

    <Teleport to="body">
      <Transition name="sheet-backdrop">
        <div
          v-if="plantillaSheetObert"
          class="fixed inset-0 z-[80] bg-black/40"
          @click="tancarSheetCrearPlantilla"
        ></div>
      </Transition>
      <Transition name="sheet-panel">
        <div
          v-if="plantillaSheetObert"
          class="fixed left-0 right-0 bottom-0 z-[81] bg-white rounded-t-3xl shadow-2xl max-h-[90vh] flex flex-col pb-[max(0.5rem,env(safe-area-inset-bottom))]"
        >
          <div class="sticky top-0 z-[1] bg-white rounded-t-3xl flex flex-col items-center shrink-0 border-b border-gray-100 w-full pt-4 px-6">
            <div class="w-12 h-1.5 bg-gray-300 rounded-full mb-4"></div>
            <h3 class="text-2xl font-['Bricolage_Grotesque'] font-bold text-[#949494] mb-4 text-center w-full">
              {{ modoEdicio ? $t('templates.edit_title') : $t('templates.create_sheet_heading') }}
            </h3>
          </div>
          <div class="flex-1 min-h-0 overflow-y-auto px-4 py-3 space-y-4 pb-[max(1rem,env(safe-area-inset-bottom))]">
            <div>
              <label class="habit-form-label" for="plantilla-sheet-titol">{{
                $t('templates.name_label')
              }}</label>
              <input
                id="plantilla-sheet-titol"
                v-model="form.titol"
                type="text"
                :placeholder="$t('templates.name_placeholder')"
                class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-green-500 focus:bg-white transition-all"
              />
            </div>
            <div>
              <UserHabitsHabitFormCategory
                embedded
                :categories="categories"
                :user-categories="userCategories"
                :selected-id="form.categoria"
                :category-custom-label="form.userCategoriaEtiqueta || ''"
                :category-custom-icona="form.icona"
                :selected-user-category-id="form.userCategoriaId"
                @select="seleccionarCategoria"
                @select-user="seleccionarCategoriaUsuari"
                @add-user-category="afegirCategoriaUsuari"
              />
            </div>
            <div>
              <label class="habit-form-label" for="plantilla-sheet-es-publica">{{ $t('templates.public_checkbox') }}</label>
              <SharedTemplatePublicSwitch
                input-id="plantilla-sheet-es-publica"
                :model-value="form.esPublica"
                @update:model-value="form.esPublica = $event"
              />
            </div>
            <div>
              <h4 class="habit-form-label">{{ $t('templates.select_habits') }}</h4>
              <div v-if="habitStore.loading" class="text-center py-6 text-gray-500 text-sm">
                {{ $t('home.loading_habits') }}
              </div>
              <div v-else-if="habitStore.error" class="text-center py-4 text-red-500 text-sm">
                {{ $t('templates.error_prefix') }}{{ habitStore.error }}
              </div>
              <div v-else-if="habitStore.habits.length === 0" class="text-center py-4 text-gray-400 text-sm">
                {{ $t('templates.no_habits_to_select') }}
              </div>
              <div v-else class="flex flex-col gap-2 max-h-[min(50vh,22rem)] overflow-y-auto pr-1">
                <button
                  v-for="habit in habitStore.habits"
                  :key="'sheet-habit-' + habit.id"
                  type="button"
                  class="template-habit-selection-card"
                  :class="{ 'template-habit-selection-card--selected': form.habitsSeleccionats.indexOf(habit.id) !== -1 }"
                  @click="toggleHabitSeleccionat(habit.id)"
                >
                  <div class="template-habit-selection-card__check">
                    <SharedMissionStyleCheckIcon :selected="form.habitsSeleccionats.indexOf(habit.id) !== -1" :size="32" />
                  </div>
                  <div class="template-habit-selection-card__content">
                    <div class="template-habit-selection-card__icon-blob">
                      <svg class="template-habit-selection-card__blob-svg" viewBox="0 0 56 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M1.64885 13.8624C4.80033 5.5202 12.7867 0 21.7043 0H46.8857C51.8563 0 55.8857 4.02944 55.8857 9V18.1149C55.8857 20.9967 55.1982 23.8369 53.8802 26.3997L53.3295 27.4705C49.3729 35.1639 41.4476 40 32.7964 40H18.4113C11.3613 40 4.93035 35.9742 1.85018 29.6327C-0.361252 25.0797 -0.600734 19.8171 1.18804 15.0821L1.64885 13.8624Z" :fill="getHabitColor(habit)" />
                      </svg>
                      <span class="template-habit-selection-card__emoji">{{ habit.icona || '💧' }}</span>
                    </div>
                    <span class="template-habit-selection-card__name">{{ habit.nom || habit.titol }}</span>
                  </div>
                </button>
              </div>
            </div>
            <!-- Botó Eliminar (només en mode edició) -->
            <div v-if="modoEdicio" class="pt-2">
              <button
                type="button"
                @click="eliminarPlantilla(plantillaAEditar.id)"
                class="w-full rounded-xl border-2 border-[#D14D6B] bg-[#FF6B8A] py-2.5 text-center text-base font-bold text-white transition hover:brightness-[0.97]"
              >
                {{ $t('templates.delete_button') }}
              </button>
            </div>

            <div class="grid grid-cols-2 gap-3 pt-2">
              <button
                type="button"
                class="flex w-full min-w-0 items-center justify-center border-0 bg-transparent py-2.5 text-center text-base font-normal text-[#5E5E5E] shadow-none outline-none ring-0 transition hover:opacity-80 focus-visible:underline"
                @click="tancarSheetCrearPlantilla"
              >
                {{ $t('habits.back') }}
              </button>
              <button
                type="button"
                class="w-full min-w-0 rounded-xl border-2 border-[#6FBC58] bg-[#79D45D] py-2.5 text-center text-base font-normal text-white transition hover:brightness-[0.97]"
                @click="guardarPlantilla"
              >
                {{ modoEdicio ? $t('templates.update_button') : $t('templates.create_button') }}
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
</template>

<script>
import { usePlantillaStore } from "../stores/usePlantillaStore";
import { useHabitStore } from "../stores/useHabitStore";
import { useGameStore } from "../stores/gameStore";
import { useSocketConfig } from "../composables/useSocketConfig";
import { watch } from 'vue';
import { getDefaultColorForCategoryId, nearestCategoryIdFromHex } from "~/utils/habitCategoryColor.js";
import { normalizeHex } from "~/utils/colorSpace.js";
import ConfirmModal from "~/components/user/social/ConfirmModal.vue";
import { usePlantillesPageSocket } from "~/composables/domains/plantilles/usePlantillesPageSocket.js";
import { useSocketUiCallbacks } from "~/stores/useSocketUiCallbacks.js";

export default {
  components: {
    ConfirmModal
  },
  setup: function () {
    var plantillaStore = usePlantillaStore();
    var habitStore = useHabitStore();
    var gameStore = useGameStore(); // Initialize useGameStore

    return { plantillaStore: plantillaStore, habitStore: habitStore, gameStore: gameStore };
  },
  // Dades reactives del component.
  data: function () {
    return {
      modalVisible: false,
      plantillaSheetObert: false,
      modoEdicio: false, // Indica si el modal està en mode edició o creació.
      plantillaAEditar: null, // Objecte de plantilla a editar, si n'hi ha.
      socket: null, // Instància del socket per a comunicació en temps real.
      selectedFilter: 'all', // New reactive property for the filter dropdown
      form: {
        titol: "",
        categoria: "",
        icona: "📁",
        esPublica: false,
        habitsSeleccionats: [], // Array d'IDs d'hàbits seleccionats per a la plantilla.
        userCategoriaEtiqueta: null,
        userCategoriaId: null
      },
      categories: [
        { id: 1, key: "physical", icona: "🏃" },
        { id: 2, key: "food", icona: "🥗" },
        { id: 3, key: "study", icona: "📚" },
        { id: 4, key: "reading", icona: "📖" },
        { id: 5, key: "wellness", icona: "🧘" },
        { id: 6, key: "improvement", icona: "✨" },
        { id: 7, key: "home", icona: "🏠" },
        { id: 8, key: "hobby", icona: "🎨" }
      ],
      userCategories: [],
      categoriaAnterior: null,
      modalImportarVisible: false,
      plantillaAImportar: null,
      habitsAImportarSeleccionats: [],
      modalEliminarVisible: false, // New: Controla la visibilitat del modal d'eliminació.
      plantillaAEliminar: null,   // New: Objecte de plantilla seleccionada per eliminar.
      plantillaExpandidaId: null,
      searchQuery: '',
      searchVisible: false,
    };
  },
  computed: {
    baseFilteredPlantilles: function () {
      var self = this;
      if (!Array.isArray(self.plantillaStore.plantilles)) {
        return [];
      }
      var llista = self.plantillaStore.plantilles;
      if (self.searchQuery.trim() !== '') {
        var query = self.searchQuery.toLowerCase();
        llista = llista.filter(function (p) {
          return (p.titol || '').toLowerCase().indexOf(query) !== -1;
        });
      }
      return llista;
    },
    plantillesDefault: function () {
      return this.baseFilteredPlantilles.filter(function (p) {
        return p.esDefault === true;
      });
    },
    plantillesPubliques: function () {
      var uid = this.gameStore.userId || 1;
      return this.baseFilteredPlantilles.filter(function (p) {
        return p.esPublica === true && !p.esGuardada && !p.esAmic && p.creadorId !== uid && !p.esDefault;
      });
    },
    plantillesPersonals: function () {
      var uid = this.gameStore.userId || 1;
      return this.baseFilteredPlantilles.filter(function (p) {
        return (p.creadorId === uid || !p.esPublica) && !p.esGuardada && !p.esDefault;
      });
    },
    plantillesAmics: function () {
      var uid = this.gameStore.userId || 1;
      return this.baseFilteredPlantilles.filter(function (p) {
        return (p.esAmic === true || (p.esPublica === true && p.creadorId !== uid && p.creadorId !== 1 && !p.esGuardada)) && !p.esDefault;
      });
    },
    plantillesGuardades: function () {
      return this.baseFilteredPlantilles.filter(function (p) {
        return (p.esGuardada === true || p.origen === 'forum' || p.importada === true) && !p.esDefault;
      });
    },
    filteredPlantilles: function () {
      if (this.selectedFilter === 'public') return this.plantillesPubliques;
      if (this.selectedFilter === 'personals') return this.plantillesPersonals;
      if (this.selectedFilter === 'friends') return this.plantillesAmics;
      if (this.selectedFilter === 'saved') return this.plantillesGuardades;
      if (this.selectedFilter === 'default') return this.plantillesDefault;
      return this.baseFilteredPlantilles;
    }
  },
  // Hook de cicle de vida: s'executa quan el component és muntat.
  mounted: function () {
    var self = this;
    self.carregarCategoriesUsuari();
    // A. Carregar les plantilles existents des de l'API.
    self.carregarPlantilles();
    // Set default userId for now as there's no authentication yet
    self.gameStore.assignarUsuariId(1); // Set userId to 1
    // B. Inicialitzar la connexió del socket.
    self.initSocket();
    // C. Carregar els hàbits disponibles per a la selecció.
    self.carregarHabits();

    // Watch for changes in selectedFilter and re-carregarPlantilles
    this.$watch('selectedFilter', function (newFilter, oldFilter) {
      if (newFilter !== oldFilter) {
        self.carregarPlantilles();
      }
    });

    // Watch for changes in gameStore.userId and re-carregarPlantilles
    this.$watch(function () {
      return this.gameStore.userId;
    }, function (newUserId, oldUserId) {
      // A. Comprovar si l'ID d'usuari ha canviat
      if (newUserId !== oldUserId) {
        console.log("gameStore.userId ha canviat, recarregant plantilles. Nou userId:", newUserId);
        self.carregarPlantilles();
      }
    });
  },
  // Hook de cicle de vida: s'executa abans que el component sigui desmuntat.
  beforeUnmount: function () {
    var self = this;
    if (self._netejaPlantillaSocket && typeof self._netejaPlantillaSocket === "function") {
      self._netejaPlantillaSocket();
    }
    if (self._plantillaFeedbackHandler) {
      useSocketUiCallbacks().eliminarHabitConfirmed(self._plantillaFeedbackHandler);
    }
  },
  // Mètodes del component.
  methods: {
    estaHabitImplementat: function (habit) {
      if (!habit || !this.habitStore.habits) return false;
      var nomHabit = (habit.nom || habit.titol || '').toLowerCase().trim();
      return this.habitStore.habits.some(function (h) {
        return (h.nom || h.titol || '').toLowerCase().trim() === nomHabit;
      });
    },
    // --- Mètodes de la vista principal de Plantilles ---

    /**
     * Carrega les plantilles des de l'API a través del 'plantillaStore'.
     * @returns {Promise<void>}
     */
    carregarPlantilles: async function () {
      var userId = this.gameStore.userId; // Get userId from gameStore
      console.log("Fetching plantilles with userId:", userId); // Debug log
      await this.plantillaStore.obtenirPlantillesDesDeApi('all', userId);
    },
    togglePlantillaExpandida: function (id) {
      if (this.plantillaExpandidaId === id) {
        this.plantillaExpandidaId = null;
        return;
      }
      this.plantillaExpandidaId = id;
    },
    tancarPlantillaExpandida: function () {
      this.plantillaExpandidaId = null;
    },
    isPlantillaExpandida: function (id) {
      return this.plantillaExpandidaId === id;
    },
    /**
     * Obre el bottom sheet per crear plantilla (mateix formulari que el modal, estil full hàbit).
     */
    obrirSheetCrearPlantilla: function () {
      var self = this;
      self.modoEdicio = false;
      self.plantillaAEditar = null;
      self.form.titol = "";
      self.form.categoria = "";
      self.form.icona = "📁";
      self.form.esPublica = false;
      self.form.habitsSeleccionats = [];
      self.form.userCategoriaEtiqueta = null;
      self.form.userCategoriaId = null;
      self.categoriaAnterior = null;
      
      // Carreguem en segon pla sense bloquejar l'obertura del desplegable
      self.carregarHabits();
      self.carregarCategoriesUsuari();
      
      self.modalVisible = false;
      self.plantillaSheetObert = true;
    },
    tancarSheetCrearPlantilla: function () {
      this.plantillaSheetObert = false;
      this.form.userCategoriaEtiqueta = null;
      this.form.userCategoriaId = null;
      this.categoriaAnterior = null;
    },
    togglePublicacioPlantilla: function (plantilla) {
      var self = this;
      var i;
      var habitsIds = [];

      if (!self.socket) {
        self.$swal.fire({
          icon: 'error',
          title: 'Error',
          text: this.$t('templates.alert_socket_unavailable')
        });
        return;
      }

      if (plantilla.habits && Array.isArray(plantilla.habits)) {
        for (i = 0; i < plantilla.habits.length; i++) {
          habitsIds.push(plantilla.habits[i].id);
        }
      }

      self.socket.emit("plantilla_action", {
        action: "UPDATE",
        plantilla_data: {
          id: plantilla.id,
          titol: plantilla.titol,
          categoria: plantilla.categoria,
          es_publica: !plantilla.esPublica,
          habits_ids: habitsIds
        },
      });
    },

    /**
     * Obre el modal en mode de creació de plantilla i reinicia el formulari.
     */
    obrirModalCrearPlantilla: function () {
      var self = this;
      self.modoEdicio = false;
      self.plantillaAEditar = null;
      // Reiniciar el formulari per a una nova creació.
      self.form.titol = "";
      self.form.categoria = "";
      self.form.icona = "📁";
      self.form.esPublica = false;
      self.form.habitsSeleccionats = [];
      self.form.userCategoriaEtiqueta = null;
      self.form.userCategoriaId = null;
      self.categoriaAnterior = null;

      self.carregarHabits(); // Refresh habits in background
      self.carregarCategoriesUsuari();

      self.plantillaSheetObert = false;
      self.modalVisible = true;
    },

    /**
     * Obre el modal en mode d'edició per a una plantilla específica.
     * @param {number} id - L'ID de la plantilla a editar.
     */
    editarPlantilla: function (id) {
      var self = this;
      var plantillaTrobada = null;
      var i;

      // Cercar la plantilla per ID a l'emmagatzematge.
      for (i = 0; i < self.plantillaStore.plantilles.length; i++) {
        if (self.plantillaStore.plantilles[i].id === id) {
          plantillaTrobada = self.plantillaStore.plantilles[i];
          break;
        }
      }

      if (!plantillaTrobada) return;

      self.plantillaAEditar = plantillaTrobada;
      self.modoEdicio = true;
      
      // Reiniciar formulari abans d'omplir-lo
      self.form.titol = self.plantillaAEditar.titol;
      self.form.categoria = self.plantillaAEditar.categoria;
      self.form.icona = self.plantillaAEditar.icona || "📁";
      self.form.esPublica = self.plantillaAEditar.esPublica;
      self.form.habitsSeleccionats = [];
      self.form.userCategoriaEtiqueta = null;
      self.form.userCategoriaId = null;

      // Carregar dades de categoria personalitzada si n'hi ha en metadata
      var meta = self.plantillaAEditar.metadata;
      if (meta && typeof meta === "object") {
        if (meta.user_categoria_nom) {
          self.form.userCategoriaEtiqueta = meta.user_categoria_nom;
          self.form.userCategoriaId = meta.user_categoria_id != null ? meta.user_categoria_id : null;
          if (meta.user_categoria_icona) {
            self.form.icona = meta.user_categoria_icona;
          }
        }
      }

      // Carregar els IDs dels hàbits que ja té la plantilla (mapejats als hàbits de l'usuari per títol/nom de forma robusta)
      if (self.plantillaAEditar.habits && Array.isArray(self.plantillaAEditar.habits)) {
        for (i = 0; i < self.plantillaAEditar.habits.length; i++) {
          var tHabit = self.plantillaAEditar.habits[i];
          var tNom = (tHabit.nom || tHabit.titol || "").toLowerCase().trim();
          
          // Cercar si l'usuari té un hàbit actiu amb el mateix nom
          var matchingUserHabit = self.habitStore.habits.find(function (h) {
            return (h.nom || h.titol || "").toLowerCase().trim() === tNom;
          });
          
          if (matchingUserHabit) {
            self.form.habitsSeleccionats.push(matchingUserHabit.id);
          } else {
            // Si no en té cap, per seguretat afegim el seu ID original per no perdre'l
            self.form.habitsSeleccionats.push(tHabit.id);
          }
        }
      }

      // Carreguem en segon pla
      self.carregarHabits();
      self.carregarCategoriesUsuari();
      
      // Obrim el desplegable (sheet) en lloc del modal per ser més instantani i consistent
      self.modalVisible = false;
      self.plantillaSheetObert = true;
    },

    /**
     * Gestiona l'eliminació d'una plantilla.
     * @param {number} id - L'ID de la plantilla a eliminar.
     */
    eliminarPlantilla: function (id) {
      var self = this;
      if (!self.socket) {
        self.$swal.fire({
          icon: 'error',
          title: 'Error',
          text: this.$t('templates.alert_socket_unavailable')
        });
        return;
      }
      self.tancar();
      self.tancarModalImportar();
      // Buscar la plantilla per mostrar el seu titol al modal
      var p = self.plantillaStore.plantilles.find(function(item) { return item.id === id; });
      self.plantillaAEliminar = p;
      self.modalEliminarVisible = true;
    },

    tancarModalEliminar: function () {
      this.modalEliminarVisible = false;
      this.plantillaAEliminar = null;
    },

    confirmarEliminar: function () {
      var self = this;
      if (!self.plantillaAEliminar) return;

      self.socket.emit("plantilla_action", {
        action: "DELETE",
        plantilla_id: self.plantillaAEliminar.id,
        user_id: self.gameStore.userId
      });
      
      self.tancarModalEliminar();
    },

    /**
     * Tanca el modal de creació/edició de plantilles.
     */
    tancar: function () {
      this.modalVisible = false;
      this.plantillaSheetObert = false;
      this.form.userCategoriaEtiqueta = null;
      this.form.userCategoriaId = null;
      this.categoriaAnterior = null;
    },

    /**
     * S'executa després que una plantilla s'hagi creat amb èxit.
     * Tanca el modal i recarrega la llista de plantilles.
     */
    plantillaCreada: function () {
      this.tancar();
      this.carregarPlantilles(); // Recarregar les plantilles per veure la nova.
    },

    /**
     * S'executa després que una plantilla s'hagi actualitzat amb èxit.
     * Tanca el modal i recarrega la llista de plantilles.
     */
    plantillaActualitzada: function () {
      this.tancar();
      this.carregarPlantilles(); // Recarregar les plantilles per veure els canvis.
    },

    // --- Mètodes del modal de creació/edició (adaptats) ---

    /**
     * Inicialitza la connexió amb el servidor de sockets.
     * Si ja hi ha una connexió, no fa res.
     */
    initSocket: function () {
      var self = this;
      var nuxtApp = useNuxtApp();
      
      // No fer res si el socket ja està inicialitzat.
      if (self.socket) {
        return;
      }
      
      // Utilitzem la instància global injectada pel plugin
      self.socket = nuxtApp.$socket;

      if (!self.socket) {
        console.error("❌ Socket global no disponible");
        return;
      }

      // console.log('Socket URL:', socketUrl); // Comentari de depuració, es pot eliminar o comentar.

      if (!self._plantillaFeedbackHandler) {
        self._plantillaFeedbackHandler = function (payload) {
          self.handlePlantillaFeedback(payload);
        };
        self._netejaPlantillaSocket = usePlantillesPageSocket(self._plantillaFeedbackHandler);
        useSocketUiCallbacks().registrarHabitConfirmed(self._plantillaFeedbackHandler);
      }
    },

    /**
     * Carrega els hàbits disponibles des de l'API a través del 'habitStore'.
     * @returns {Promise<void>}
     */
    carregarHabits: function () {
      // No fem await per permetre que la UI sigui instantània.
      // El store ja gestiona l'estat 'loading' que la UI mostra.
      this.habitStore.obtenirHabitsDesDeApi();
    },

    /**
     * Afegeix o treu un hàbit de la llista de seleccionats per a la plantilla.
     * @param {number} habitId - L'ID de l'hàbit a alternar.
     */
    toggleHabitSeleccionat: function (habitId) {
      var self = this;
      var pos = self.form.habitsSeleccionats.indexOf(habitId);
      // A. Si l'hàbit no està seleccionat, afegir-lo.
      if (pos === -1) {
        self.form.habitsSeleccionats.push(habitId);
      } else {
        // B. Si l'hàbit ja està seleccionat, treure'l.
        self.form.habitsSeleccionats.splice(pos, 1);
      }
    },

    seleccionarCategoria: function (id) {
      this.form.categoria = id;
      this.form.userCategoriaEtiqueta = null;
      this.form.userCategoriaId = null;
      var cat = this.categories.find(function (c) { return Number(c.id) === Number(id); });
      if (cat) {
        this.form.icona = cat.icona;
      }
      this.categoriaAnterior = id;
    },
    seleccionarCategoriaUsuari: function (payload) {
      if (!payload || payload.baseCategoryId == null) return;
      this.form.categoria = parseInt(String(payload.baseCategoryId), 10);
      this.form.icona = payload.icona || "📁";
      this.form.userCategoriaEtiqueta = payload.nom;
      this.form.userCategoriaId = payload.id;
      this.categoriaAnterior = this.form.categoria;
    },
    carregarCategoriesUsuari: function () {
      try {
        var raw = localStorage.getItem("loopy_user_habit_categories");
        if (!raw) {
          this.userCategories = [];
          return;
        }
        var parsed = JSON.parse(raw);
        this.userCategories = Array.isArray(parsed) ? parsed : [];
      } catch (e) {
        this.userCategories = [];
      }
    },
    persistirCategoriesUsuari: function () {
      try {
        localStorage.setItem("loopy_user_habit_categories", JSON.stringify(this.userCategories));
      } catch (e) {}
    },
    afegirCategoriaUsuari: function (payload) {
      var nom = "";
      var icona = "✨";
      var colorHex = null;
      var baseId = 8;
      if (typeof payload === "string") {
        nom = String(payload || "").trim();
        baseId = (this.userCategories.length % 8) + 1;
      } else if (payload && typeof payload === "object") {
        nom = String(payload.nom || "").trim();
        icona = payload.icona && String(payload.icona).trim() ? String(payload.icona).trim() : "✨";
        if (payload.color && String(payload.color).trim()) {
          colorHex = normalizeHex(payload.color);
        }
        if (payload.baseCategoryId != null) {
          var b = parseInt(String(payload.baseCategoryId), 10);
          baseId = Number.isNaN(b) ? nearestCategoryIdFromHex(colorHex || "#10B981") : b;
        } else {
          baseId = nearestCategoryIdFromHex(colorHex || "#10B981");
        }
      }
      if (!nom) return;
      var maxId = this.userCategories.reduce(function (m, c) {
        return Math.max(m, Number(c.id) || 0);
      }, 9000);
      var nextId = maxId + 1;
      var entry = { id: nextId, nom: nom, icona: icona, baseCategoryId: baseId };
      if (colorHex) entry.color = colorHex;
      this.userCategories = this.userCategories.concat([entry]);
      this.persistirCategoriesUsuari();
    },

    /**
     * Guarda la plantilla actual (creant-la o actualitzant-la).
     */
    guardarPlantilla: function () {
      var self = this;
      console.log('guardarPlantilla called');

      // A. Validacions del formulari.
      if (!self.form.titol) {
        console.log('Validation failed: Title is empty.');
        self.$swal.fire({
          icon: 'error',
          title: 'Error',
          text: this.$t('templates.alert_name_required')
        });
        return;
      }
      if (self.form.habitsSeleccionats.length === 0) {
        console.log('Validation failed: No habits selected.');
        self.$swal.fire({
          icon: 'error',
          title: 'Error',
          text: this.$t('templates.alert_habit_required')
        });
        return;
      }
  
      // B. Comprovar que el socket estigui disponible.
      if (!self.socket) {
        console.log('Validation failed: Socket not available.');
        self.$swal.fire({
          icon: 'error',
          title: 'Error',
          text: this.$t('templates.alert_socket_unavailable')
        });
        return;
      }

      console.log('All validations passed. Preparing plantillaData...');
      // C. Preparar les dades de la plantilla per enviar.
      var plantillaData = {
        titol: self.form.titol,
        categoria: String(self.form.categoria),
        icona: self.form.icona || "📁",
        es_publica: self.form.esPublica,
        habits_ids: self.form.habitsSeleccionats,
        metadata: {
          user_categoria_nom: self.form.userCategoriaEtiqueta,
          user_categoria_id: self.form.userCategoriaId,
          user_categoria_icona: self.form.userCategoriaEtiqueta ? self.form.icona : null
        }
      };

      // D. Determinar si és una creació o una actualització.
      if (self.modoEdicio && self.plantillaAEditar) {
        console.log('Emitting UPDATE action...');
        // Lògica per actualitzar una plantilla existent.
        // Afegir l'ID per a l'actualització.
        plantillaData.id = self.plantillaAEditar.id;
        self.socket.emit("plantilla_action", {
          action: "UPDATE",
          plantilla_data: plantillaData,
        });
      } else {
        console.log('Emitting CREATE action...');
        // Lògica per crear una nova plantilla.
        self.socket.emit("plantilla_action", {
          action: "CREATE",
          plantilla_data: plantillaData,
        });
      }
      console.log('Socket emitted.');
    },

    /**
     * Gestiona el feedback rebut del servidor de sockets després d'una acció de plantilla.
     * @param {object} payload - Les dades de resposta del servidor.
     */
    handlePlantillaFeedback: function (payload) {
      var self = this;
      console.log('handlePlantillaFeedback called. Payload:', payload); // Log the entire payload
      console.log('Payload success:', payload ? payload.success : 'N/A');
      console.log('Payload action:', payload ? payload.action : 'N/A');

      // A. Comprovar si l'acció ha estat exitosa.
      if (!payload || payload.success !== true) {
        self.$swal.fire({
          icon: 'error',
          title: 'Error',
          text: payload.message || this.$t('templates.error_processing')
        });
        return;
      }

      console.log('Received feedback payload:', payload); // DEBUG: Inspect entire payload

      // B. Executar la funció corresponent segons l'acció realitzada.
      if (payload.action === "CREATE") {
        self.$swal.fire({
          icon: 'success',
          title: 'Creat!',
          text: this.$t('templates.alert_created')
        });
        self.tancar();
        self.carregarPlantilles();
      } else if (payload.action === "UPDATE") {
        self.$swal.fire({
          icon: 'success',
          title: 'Actualitzat!',
          text: this.$t('templates.alert_updated')
        });
        self.tancar();
        self.carregarPlantilles();
      } else if (payload.action === "DELETE") {
        self.$swal.fire({
          icon: 'success',
          title: 'Eliminat!',
          text: this.$t('templates.alert_deleted')
        });
        self.carregarPlantilles();
      } else if (payload.action === "EXPORT_HABITS") {
          self.handleImportHabitsConfirmation(payload);
      }
    },

    /**
     * Retorna el color d'un hàbit per al fons del blob.
     * @param {object} habit - L'hàbit del qual obtenir el color.
     */
    getHabitColor: function (habit) {
      var c = habit && habit.color;
      if (c && String(c).trim()) {
        return normalizeHex(String(c).trim());
      }
      var catId = habit.categoriaId != null ? habit.categoriaId : habit.categoria_id;
      return getDefaultColorForCategoryId(Number(catId) || 1);
    },

    getCategoryIcon: function (catId) {
      var cat = this.categories.find(function (c) { return Number(c.id) === Number(catId); });
      return cat ? cat.icona : "📁";
    },

    /**
     * Obre el modal per importar els hàbits d'una plantilla a l'usuari.
     * @param {object} plantilla - La plantilla de la qual importar hàbits.
     */
    obrirModalImportarHabits: function (plantilla) {
      var self = this;
      self.tancar();
      self.tancarModalEliminar();
      self.plantillaAImportar = plantilla;
      self.modalImportarVisible = true;

      self.habitsAImportarSeleccionats = [];
      if (plantilla.habits && Array.isArray(plantilla.habits)) {
        var i;
        for (i = 0; i < plantilla.habits.length; i++) {
          self.habitsAImportarSeleccionats.push(plantilla.habits[i].id);
        }
      }
    },

    /**
     * Redirigeix l'usuari al fòrum (social) per exportar/compartir la plantilla.
     * @param {object} plantilla - La plantilla a compartir.
     */
    exportarAForum: function (plantilla) {
      // Redirigim a social. Se li podria passar l'ID si tinguéssim un flux de "share"
      navigateTo('/social');
    },

    tancarModalImportar: function () {
      this.modalImportarVisible = false;
      this.plantillaAImportar = null;
      this.habitsAImportarSeleccionats = [];
    },

    toggleHabitAImportarSeleccionat: function (habitId) {
      var self = this;
      var pos = self.habitsAImportarSeleccionats.indexOf(habitId);
      if (pos === -1) {
        self.habitsAImportarSeleccionats.push(habitId);
      } else {
        self.habitsAImportarSeleccionats.splice(pos, 1);
      }
    },

    confirmarImportacioHabits: function () {
      var self = this;
      var plantilla = self.plantillaAImportar;
      var habitsConfirmacio = [];
      var i;

      if (!plantilla || self.habitsAImportarSeleccionats.length === 0) {
        self.$swal.fire({
          icon: 'warning',
          title: 'Atenció',
          text: this.$t('templates.export_error_no_selection')
        });
        return;
      }

      for (i = 0; i < plantilla.habits.length; i++) {
        if (self.habitsAImportarSeleccionats.indexOf(plantilla.habits[i].id) !== -1) {
          habitsConfirmacio.push(plantilla.habits[i].nom || plantilla.habits[i].titol);
        }
      }

      var missatgeConfirmacio = this.$t('templates.export_confirm_msg', { 
        titol: plantilla.titol, 
        habits: habitsConfirmacio.join(", ") 
      });

      self.$swal.fire({
        title: 'Confirmar importació',
        text: missatgeConfirmacio,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Importa',
        cancelButtonText: 'Cancel·la',
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#d33'
      }).then(function (result) {
        if (result.isConfirmed) {
          self.importarHabitsSeleccionats();
        }
      });
    },

    importarHabitsSeleccionats: function () {
      var self = this;
      var plantilla = self.plantillaAImportar;

      if (!self.socket) {
        self.$swal.fire({
          icon: 'error',
          title: 'Error',
          text: this.$t('templates.export_error_socket')
        });
        return;
      }

      if (!plantilla || self.habitsAImportarSeleccionats.length === 0) {
        self.$swal.fire({
          icon: 'error',
          title: 'Error',
          text: this.$t('templates.export_error_no_data')
        });
        return;
      }

      self.socket.emit("habit_action", {
        action: "EXPORT_HABITS",
        plantilla_id: plantilla.id,
        selected_habits: self.habitsAImportarSeleccionats,
        user_id: self.gameStore.userId,
      });
      self.tancarModalImportar();
    },

    handleImportHabitsConfirmation: async function (payload) {
        var self = this;
        var exportedHabitNames = [];
        var i;
        var j; // New loop variable

        // Ensure payload has the necessary data
        if (!payload.exported_habits || !Array.isArray(payload.exported_habits)) {
            self.$swal.fire({
                icon: 'error',
                title: 'Error',
                text: this.$t('templates.export_success_error_info')
            });
            self.$router.push('/home'); // Assuming home is where user habits are displayed
            return;
        }

        // --- SIMPLIFIED LOGIC: Refetch from DB to ensure "automatic" update ---
        await self.gameStore.obtenirHabitos();
        
        // Refresh gameStore too if we want immediate home page consistency
        // self.gameStore.obtenirHabitos(); 

        for (i = 0; i < payload.exported_habits.length; i++) {
            exportedHabitNames.push(payload.exported_habits[i].nom || payload.exported_habits[i].titol);
        }

        self.$swal.fire({
            title: 'Nova plantilla?',
            text: this.$t('templates.export_confirm_save_template', {
                habits: exportedHabitNames.join(", "),
                titol: this.$t('templates.export_template_title_prefix') + self.plantillaAImportar.titol
            }),
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, crea-la',
            cancelButtonText: 'No, gràcies',
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#d33'
        }).then(function (result) {
            if (result.isConfirmed) {
                self.socket.emit("plantilla_action", {
                    action: "CREATE", // Creating a new template from exported habits
                    plantilla_data: {
                        titol: self.$t('templates.export_template_title_prefix') + self.plantillaAImportar.titol,
                        categoria: "Personal", // Default category for exported templates
                        es_publica: false, // Exported templates are private by default
                        habits_ids: self.habitsAImportarSeleccionats,
                    },
                    user_id: self.gameStore.userId,
                    // Add a flag to indicate this is a follow-up creation from export, if needed by backend
                    is_exported_template: true
                });
                self.$swal.fire({
                    icon: 'info',
                    title: 'Processant...',
                    text: self.$t('templates.export_creating_template'),
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                self.$swal.fire({
                    icon: 'info',
                    title: 'Info',
                    text: self.$t('templates.export_no_template_created'),
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        });

        self.$router.push('/home');
    },
    toggleSearch: function () {
      var self = this;
      self.searchVisible = !self.searchVisible;
      if (self.searchVisible) {
        self.$nextTick(function () {
          if (self.$refs.searchInput) {
            self.$refs.searchInput.focus();
          }
        });
      } else {
        self.searchQuery = '';
      }
    },
  },
};
</script>

<style scoped>
.templates-page {
  font-family: "Comfortaa", system-ui, sans-serif;
}

.templates-filter-wrap {
  width: 100%;
}

.templates-filter-row {
  display: flex;
  align-items: stretch;
  gap: 10px;
  width: 100%;
  flex-wrap: wrap;
}

.templates-filter-search {
  display: flex;
  align-items: center;
  width: 58px;
  height: 58px;
  border-radius: 10px;
  background: #faf9f9;
  transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  overflow: hidden;
  flex-shrink: 0;
}

.templates-filter-search--active {
  width: 100%;
}

.templates-filter-decor {
  flex-shrink: 0;
  width: 58px;
  height: 58px;
  border: 0;
  background: transparent;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  outline: none;
}

.templates-filter-search-input {
  flex: 1;
  border: 0;
  background: transparent;
  padding: 0 20px 0 0;
  color: #5e5e5e;
  font-family: "Comfortaa", system-ui, sans-serif;
  font-size: 16px;
  outline: none;
}

.templates-filter-card {
  position: relative;
  flex: 1 1 0;
  min-width: 200px;
  height: 58px;
  border-radius: 10px;
  background: #faf9f9;
  overflow: hidden;
  transition: width 0.3s ease, flex 0.3s ease;
}

.templates-filter-wrap--searching .templates-filter-card {
  flex: 0 0 100%;
}

.templates-filter-select {
  width: 100%;
  height: 100%;
  border: 0;
  background: transparent;
  padding: 0 48px 0 20px;
  color: #5e5e5e;
  font-family: "Comfortaa", system-ui, sans-serif;
  font-size: 14px;
  font-weight: 400;
  line-height: 1.2;
  appearance: none;
  outline: none;
}

.templates-filter-chevron {
  position: absolute;
  right: 20px;
  top: 50%;
  width: 14px;
  height: 14px;
  border-right: 5px solid #d8d8d8;
  border-bottom: 5px solid #d8d8d8;
  border-radius: 2px;
  transform: translateY(-62%) rotate(45deg);
  pointer-events: none;
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

/* Mateix separador que al catàleg / home; dins de la plantilla expandida */
.moment-divider--expanded-habits {
  margin-top: 2px;
}

.sheet-backdrop-enter-active,
.sheet-backdrop-leave-active {
  transition: opacity 0.2s ease;
}

.sheet-backdrop-enter-from,
.sheet-backdrop-leave-to {
  opacity: 0;
}

.sheet-panel-enter-active,
.sheet-panel-leave-active {
  transition: transform 0.25s ease, opacity 0.25s ease;
}

.sheet-panel-enter-from,
.sheet-panel-leave-to {
  transform: translateY(100%);
  opacity: 0.98;
}

.plantilla-sheet__header {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 2.75rem;
  padding-left: 2.75rem;
  padding-right: 2.75rem;
}

.plantilla-sheet__title {
  margin: 0;
  width: 100%;
  text-align: center;
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 1rem;
  font-weight: 700;
  line-height: 1.2;
  color: #949494;
}

.plantilla-sheet__close {
  position: absolute;
  right: 8px;
  top: 50%;
  transform: translateY(-50%);
  width: 40px;
  height: 40px;
  border: none;
  padding: 0;
  margin: 0;
  background: transparent;
  cursor: pointer;
}

.plantilla-sheet__close:focus {
  outline: none;
}

.plantilla-sheet__close:focus-visible {
  box-shadow: 0 0 0 2px rgba(148, 148, 148, 0.4);
  border-radius: 6px;
}

.plantilla-sheet__close-line {
  position: absolute;
  left: 50%;
  top: 50%;
  width: 18.5px;
  height: 4px;
  background-color: #d8d8d8;
  border-radius: 999px;
  transform-origin: center;
  box-sizing: border-box;
  pointer-events: none;
}

.plantilla-sheet__close-line--1 {
  transform: translate(-50%, -50%) rotate(43.17deg);
}

.plantilla-sheet__close-line--2 {
  transform: translate(-50%, -50%) rotate(-44.87deg);
}

.create-category-trigger {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  max-width: 338px;
  min-height: 64px;
  margin: 0 auto;
  padding: 0;
  border-radius: 10px;
  background: rgba(250, 249, 249, 0.5);
  border: 2px dashed #ffffff;
  box-shadow: none;
}

.create-category-trigger--grid {
  max-width: none;
  width: 100%;
  margin: 0;
}

.create-category-trigger__icon {
  width: 33px;
  height: 33px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.template-expandable {
  overflow: hidden;
  border-radius: 10px;
  max-height: 92px;
  transition: max-height 0.28s ease, background-color 0.2s ease, padding 0.2s ease;
}

.templates-grid {
  --tw-space-y-reverse: 0;
  column-gap: 1.5rem;
  row-gap: calc(0.75rem * calc(1 - var(--tw-space-y-reverse)));
}

.template-expandable--active {
  background: rgba(0, 0, 0, 0.54);
  padding: 10px;
  max-height: 620px;
}

.template-card {
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
}

.template-card__content {
  display: flex;
  flex-direction: column;
  justify-content: center;
  gap: 6px;
}

.template-card__mark {
  position: relative;
  width: 57px;
  height: 54px;
}

.template-card__blob {
  display: block;
  width: 57px;
  height: 54px;
}

.template-card__icona {
  position: absolute;
  left: 50%;
  top: 50%;
  transform: translate(-50%, -52%);
  z-index: 1;
  width: 2rem;
  text-align: center;
  font-size: 1.15rem;
  line-height: 1;
}

.template-card__title {
  margin: 0;
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 20px;
  font-weight: 700;
  line-height: 1.1;
  color: #2b2d42;
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
  gap: 6px;
  color: #707070;
  line-height: 1;
}

.template-expand-inline {
  animation: template-sheet-up 0.22s ease-out;
  margin-top: 8px;
}

.template-expand-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 8px;
}

.template-expand-close,
.template-expand-edit {
  border: 0;
  background: transparent;
  color: #faf9f9;
}

.template-expand-edit {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 16px;
  font-weight: 400;
}

.template-expand-panel {
  background: transparent;
  border-radius: 0;
  padding: 0;
  display: grid;
  gap: 10px;
}

.template-spec-card {
  border: 1px solid #ececec;
  border-radius: 10px;
  padding: 10px;
  background: #ffffff;
}

.template-habits-stack {
  display: grid;
  gap: 8px;
}

.template-habits-list__title {
  margin: 0 0 8px 0;
  color: #2b2d42;
  font-size: 13px;
  font-weight: 700;
}

.template-habits-list__empty {
  margin: 4px 0;
  color: #8a8a8a;
  font-size: 12px;
}

.template-habit-card {
  position: relative;
  display: flex;
  align-items: center;
  width: 100%;
  min-height: 74px;
  padding: 14px 14px 14px 82px;
  border: 0;
  border-radius: 10px;
  background: #ffffff;
  text-align: left;
}

.template-habit-card__mark {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  width: 56px;
  height: 40px;
}

.template-habit-card__blob {
  display: block;
  width: 56px;
  height: 40px;
}

.template-habit-card__icona {
  position: absolute;
  left: 50%;
  top: 50%;
  transform: translate(-50%, -52%);
  width: 2rem;
  text-align: center;
  font-size: 1.15rem;
  line-height: 1;
}

.template-habit-card__content {
  display: grid;
  gap: 2px;
}

.template-habit-card__title {
  color: #2b2d42;
  font-size: 16px;
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-weight: 700;
}

.template-habit-card__subtitle {
  color: #6b7280;
  font-size: 12px;
}

.template-expand-actions {
  display: flex;
  gap: 0;
  width: 100%;
}

.template-expand-btn {
  border: 0;
  border-radius: 0;
  padding: 10px 12px;
  font-size: 13px;
  font-weight: 700;
  flex: 1 1 50%;
  text-align: center;
}

.template-expand-btn:first-child {
  border-radius: 10px 0 0 10px;
}

.template-expand-btn:last-child {
  border-radius: 0 10px 10px 0;
}

.template-expand-btn:only-child {
  border-radius: 10px;
}

.template-expand-btn--danger {
  background: #fee2e2;
  color: #b91c1c;
}

.template-expand-btn--primary {
  background: #79d45d;
  color: #ffffff;
}

.template-expand-btn--secondary {
  background: #dbeafe;
  color: #1d4ed8;
}

@keyframes template-sheet-up {
  from { transform: translateY(20px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}

/* Estils per a la selecció d'hàbits dins dels formularis (estil Mission Card) */
.template-habit-selection-card {
  position: relative;
  display: flex;
  align-items: center;
  width: 100%;
  min-height: 70px;
  padding: 6px 16px 6px 66px;
  background-color: #faf9f9;
  border-radius: 10px;
  overflow: hidden;
  box-sizing: border-box;
  border: 2px solid transparent;
  transition: all 0.2s ease;
  cursor: pointer;
  text-align: left;
}

.template-habit-selection-card--selected {
  border-color: #79d45d;
  background-color: #ecfdf3;
}

.template-habit-selection-card__check {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  width: 43px;
  height: 43px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.template-habit-selection-card__content {
  display: flex;
  align-items: center;
  gap: 12px;
  flex: 1;
  min-width: 0;
}

.template-habit-selection-card__icon-blob {
  position: relative;
  width: 48px;
  height: 34px;
  flex-shrink: 0;
}

.template-habit-selection-card__blob-svg {
  width: 100%;
  height: 100%;
}

.template-habit-selection-card__emoji {
  position: absolute;
  left: 50%;
  top: 50%;
  transform: translate(-50%, -52%);
  font-size: 1.15rem;
  z-index: 1;
  text-shadow: 0 0 2px rgba(255, 255, 255, 0.8), 0 1px 2px rgba(0, 0, 0, 0.1);
}

.template-habit-selection-card__name {
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
  font-size: 16px;
  line-height: 1.1;
  color: #2b2d42;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.template-expand-btn--forum {
  background: #5BA5FF;
  color: white;
}

.template-expand-btn--import {
  background: #79D45D;
  color: white;
}

.template-expand-btn--delete {
  background: #FF6B8A;
  color: white;
}

/* SweetAlert Custom Loopy Styles */
:deep(.loopy-swal-popup) {
  border-radius: 32px !important;
  padding: 2.5rem !important;
  font-family: 'Outfit', sans-serif !important;
  border: 4px solid #F3F4F6 !important;
}

:deep(.loopy-swal-title) {
  font-weight: 900 !important;
  color: #1F2937 !important;
  font-size: 24px !important;
  margin-bottom: 1rem !important;
}

:deep(.loopy-swal-confirm) {
  background-color: #FF6B8A !important;
  color: white !important;
  border-radius: 16px !important;
  padding: 12px 24px !important;
  font-weight: 800 !important;
  margin: 8px !important;
  font-size: 16px !important;
  transition: transform 0.2s !important;
  border: none !important;
  box-shadow: 0 4px 0 #D14D6B !important;
}

:deep(.loopy-swal-confirm:active) {
  transform: translateY(2px) !important;
  box-shadow: 0 2px 0 #D14D6B !important;
}

:deep(.loopy-swal-cancel) {
  background-color: #F3F4F6 !important;
  color: #6B7280 !important;
  border-radius: 16px !important;
  padding: 12px 24px !important;
  font-weight: 800 !important;
  margin: 8px !important;
  font-size: 16px !important;
  border: none !important;
}
</style>

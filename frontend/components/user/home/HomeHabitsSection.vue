<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <h2 class="text-lg font-bold text-gray-800">{{ $t('home.habits_title') }}</h2>
      <NuxtLink to="/habits" class="text-blue-500 text-xs font-semibold hover:underline">
        {{ $t('home.see_all') }}
      </NuxtLink>
    </div>
    <div class="space-y-3">
      <div v-if="errorMissatge" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
        <span class="block sm:inline">{{ errorMissatge }}</span>
        <button class="absolute top-0 bottom-0 right-0 px-4 py-3" @click="$emit('netejar-error')">✕</button>
      </div>
      <div v-if="estaCarregant" class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded text-center">
        <span>{{ $t('home.loading_habits') }}</span>
      </div>
      <div v-else-if="habits.length === 0" class="bg-gray-50 border border-gray-200 text-gray-600 px-4 py-3 rounded text-center">
        <span>{{ $t('home.no_habits') }}</span>
      </div>
      <template v-else>
        <UserHomeHomeHabitCard
          v-for="h in habits"
          :key="h.id"
          :habit="h"
          :progress="obtenirProgres(h.id)"
          :completat-avui="habitCompletatAvui(h.id)"
          :esta-processant="estaProcessant(h.id)"
          @obrir-modal="$emit('obrir-modal-habit', $event)"
        />
      </template>
    </div>
  </div>
</template>

<script>
import UserHomeHomeHabitCard from "~/components/user/home/HomeHabitCard.vue";

export default {
  name: 'HomeHabitsSection',
  components: {
    UserHomeHomeHabitCard: UserHomeHomeHabitCard
  },
  props: {
    habits: { type: Array, default: function () { return []; } },
    estaCarregant: { type: Boolean, default: false },
    errorMissatge: { type: String, default: '' },
    obtenirProgres: { type: Function, required: true },
    habitCompletatAvui: { type: Function, required: true },
    estaProcessant: { type: Function, default: function () { return false; } }
  }
};
</script>

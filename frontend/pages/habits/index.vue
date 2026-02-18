<template>
  <div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">Els Meus Hàbits</h1>
    
    <div class="mb-4">
      <NuxtLink to="/habits/create" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
        Nou Hàbit
      </NuxtLink>
    </div>

    <div v-if="habitStore.loading" class="text-gray-500">
      Carregant hàbits...
    </div>

    <div v-else class="grid gap-4">
      <div v-for="habit in habitStore.habits" :key="habit.id" 
           class="p-4 border rounded shadow-sm flex items-center justify-between bg-white">
        <div class="flex items-center gap-3">
          <input type="checkbox" 
                 :checked="habit.completed" 
                 @change="habitStore.toggleHabit(habit.id)"
                 class="w-5 h-5 text-blue-600 rounded focus:ring-blue-500">
          <span :class="{'line-through text-gray-500': habit.completed}">
            {{ habit.title }}
          </span>
        </div>
        
        <button @click="habitStore.deleteHabit(habit.id)" 
                class="text-red-500 hover:text-red-700 px-2 py-1">
          Eliminar
        </button>
      </div>
      
      <div v-if="habitStore.habits.length === 0" class="text-gray-500 italic">
        No tens cap hàbit encara. Crea'n un!
      </div>
    </div>
  </div>
</template>

<script setup>
import { useHabitStore } from '~/stores/useHabitStore';
import { onMounted } from 'vue';

const habitStore = useHabitStore();

onMounted(() => {
  if (habitStore.habits.length === 0) {
    habitStore.fetchHabits();
  }
});
</script>

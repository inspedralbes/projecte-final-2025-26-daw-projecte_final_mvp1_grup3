<template>
  <div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">Nou Hàbit</h1>
    
    <form @submit.prevent="saveHabit" class="max-w-md bg-white p-6 rounded shadow">
      <div class="mb-4">
        <label for="title" class="block text-gray-700 text-sm font-bold mb-2">Títol de l'hàbit</label>
        <input v-model="title" 
               type="text" 
               id="title" 
               placeholder="Ex: Sortir a córrer" 
               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
               required>
      </div>

      <div class="flex items-center justify-between">
        <button type="submit" 
                class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
          Guardar
        </button>
        <NuxtLink to="/habits" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
          Cancel·lar
        </NuxtLink>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useHabitStore } from '~/stores/useHabitStore';
import { useRouter } from 'vue-router';

const title = ref('');
const habitStore = useHabitStore();
const router = useRouter();

function saveHabit() {
  if (!title.value) return;
  
  // Logic interna amb var
  var newTitle = title.value;
  
  habitStore.addHabit(newTitle);
  router.push('/habits');
}
</script>

<script setup>
/**
 * Configuració del Sistema (Admin).
 * Ajustos globals de l'aplicació, API i manteniment.
 */
definePageMeta({ layout: 'admin' });

import { ref } from 'vue';

// 1. DADES (VAR)
var runtimeConfig = useRuntimeConfig();

// Configuració via API
var { data: configData, refresh: refreshConfig } = useAuthFetch('/api/admin/configuracio', {
  key: 'admin_config'
});

var config = computed(function() {
  if (configData.value && configData.value.success) {
    return configData.value.data;
  }
  return {
    nomApp: "Loopy",
    modeManteniment: false,
    registreObert: true,
    maxUsuarisPerHabit: 50,
    apiUrl: runtimeConfig.public.apiUrl,
    socketUrl: runtimeConfig.public.socketUrl
  };
});

var missatgeExecucio = ref(null);

// 2. METHODS (FUNCTION)
async function guardarConfiguracio() {
  try {
    var res = await $fetch('/api/admin/configuracio', {
      method: 'PUT',
      baseURL: runtimeConfig.public.apiUrl,
      headers: useAuthStore().getAuthHeaders(),
      body: config.value
    });
    
    if (res.success) {
      missatgeExecucio.value = "Configuració guardada correctament!";
      refreshConfig();
      setTimeout(function() { missatgeExecucio.value = null; }, 3000);
    }
  } catch (e) {
    console.error('Error guardant configuració:', e);
  }
}

async function netejarCache() {
  if (confirm("Segur que vols netejar la cache del sistema?")) {
    try {
      var res = await $fetch('/api/admin/configuracio/netejar-cache', {
        method: 'POST',
        baseURL: runtimeConfig.public.apiUrl,
        headers: useAuthStore().getAuthHeaders()
      });
      
      if (res.success) {
        missatgeExecucio.value = "Cache neta!";
        setTimeout(function() { missatgeExecucio.value = null; }, 3000);
      }
    } catch (e) {
      console.error('Error netejant cache:', e);
    }
  }
}
</script>

<template>
  <div class="space-y-12 pb-20">
    <div class="flex justify-between items-end">
      <div>
        <h2 class="text-3xl font-black text-gray-900 uppercase tracking-tighter leading-none">Ajustos del Sistema</h2>
        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-2">Control tècnic de la plataforma</p>
      </div>
      <button @click="guardarConfiguracio" class="bg-gray-900 text-white px-10 py-4 rounded-[2rem] text-xs font-black uppercase tracking-widest hover:bg-black transition-all shadow-xl shadow-gray-200">
        Guardar Ajustos
      </button>
    </div>

    <div v-if="missatgeExecucio" class="bg-green-500 text-white px-8 py-4 rounded-2xl font-black text-xs uppercase tracking-widest animate-bounce">
      {{ missatgeExecucio }}
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
      <!-- General -->
      <div class="bg-white rounded-[3rem] p-10 shadow-2xl border border-gray-100 space-y-8">
        <h3 class="text-[10px] font-black text-blue-500 uppercase tracking-[0.2em] border-b pb-4">General</h3>
        
        <div class="space-y-6">
          <div class="flex justify-between items-center">
            <span class="text-xs font-black text-gray-700 uppercase">Registre d'Usuaris</span>
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" v-model="config.registreObert" class="sr-only peer">
              <div class="w-14 h-8 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[4px] after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-blue-600"></div>
            </label>
          </div>

          <div class="flex justify-between items-center">
            <div class="flex flex-col">
              <span class="text-xs font-black text-red-600 uppercase">Mode Manteniment</span>
              <p class="text-[8px] text-gray-400 font-bold uppercase">Bloqueja l'accés a tota la web</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" v-model="config.modeManteniment" class="sr-only peer">
              <div class="w-14 h-8 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[4px] after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-red-600"></div>
            </label>
          </div>

          <div class="space-y-2 pt-4">
            <label class="text-[9px] font-black text-gray-400 uppercase tracking-widest ml-4">Límit d'Usuaris per Hàbit</label>
            <input v-model="config.maxUsuarisPerHabit" type="number" class="w-full bg-gray-50 border border-gray-100 rounded-[1.5rem] px-6 py-4 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-blue-100 transition-all" />
          </div>
        </div>
      </div>

      <!-- Tècnic -->
      <div class="bg-white rounded-[3rem] p-10 shadow-2xl border border-gray-100 space-y-8">
        <h3 class="text-[10px] font-black text-orange-500 uppercase tracking-[0.2em] border-b pb-4">Serveis i rutes</h3>
        
        <div class="space-y-6">
          <div class="space-y-2">
            <label class="text-[9px] font-black text-gray-400 uppercase tracking-widest ml-4">Laravel API Endpoint</label>
            <input v-model="config.apiUrl" type="text" class="w-full bg-gray-50 border border-gray-100 rounded-[1.5rem] px-6 py-4 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-orange-100 transition-all" />
          </div>

          <div class="space-y-2">
            <label class="text-[9px] font-black text-gray-400 uppercase tracking-widest ml-4">Socket.io Broker</label>
            <input v-model="config.socketUrl" type="text" class="w-full bg-gray-50 border border-gray-100 rounded-[1.5rem] px-6 py-4 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-orange-100 transition-all" />
          </div>

          <div class="pt-6">
            <button @click="netejarCache" class="w-full py-4 rounded-[1.5rem] border-2 border-dashed border-gray-100 text-[10px] font-black text-gray-400 uppercase tracking-widest hover:border-red-200 hover:text-red-500 transition-all">
              NETEJAR CACHE DEL SISTEMA
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

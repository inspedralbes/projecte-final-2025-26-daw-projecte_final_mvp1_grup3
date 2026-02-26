<template>
  <div class="min-h-screen bg-gray-50 flex">
    <!-- Sidebar Fix -->
    <aside class="w-64 bg-white border-r border-gray-200 flex flex-col fixed h-full shadow-sm z-20">
      <div class="p-8 border-b border-gray-100 flex items-center gap-3">
        <div class="w-10 h-10 bg-gray-900 rounded-xl flex items-center justify-center font-black text-white text-xl shadow-lg">L</div>
        <h1 class="font-black text-lg text-gray-900 uppercase tracking-tighter leading-none">Loopy Admin</h1>
      </div>

      <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto scrollbar-thin">
        <!-- Navegació Principal -->
        <div class="mb-4">
          <p class="text-[9px] font-black text-gray-300 uppercase tracking-[0.2em] px-4 mb-2">Principal</p>
          <NuxtLink v-for="item in menuPrincipal" :key="item.ruta" :to="item.ruta"
            class="flex items-center gap-3 px-4 py-3 rounded-2xl transition-all font-bold text-xs uppercase tracking-widest text-gray-400 hover:bg-gray-50 hover:text-gray-900 group"
            active-class="bg-gray-900 !text-white shadow-lg shadow-gray-200">
            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
            {{ item.nom }}
          </NuxtLink>
        </div>

        <!-- Gestió CRUD -->
        <div class="mb-4">
          <p class="px-3 text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Gestió</p>
          <NuxtLink v-for="item in menuGestio" :key="item.ruta" :to="item.ruta"
            class="flex items-center gap-3 px-4 py-3 rounded-2xl transition-all font-bold text-xs uppercase tracking-widest text-gray-400 hover:bg-gray-50 hover:text-gray-900 group"
            active-class="bg-green-600 !text-white shadow-lg shadow-green-100">
            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
            {{ item.nom }}
          </NuxtLink>
        </div>

        <!-- Sistema -->
        <div>
          <p class="px-3 text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Sistema</p>
          <NuxtLink v-for="item in menuSistema" :key="item.ruta" :to="item.ruta"
            class="flex items-center gap-3 px-4 py-3 rounded-2xl transition-all font-bold text-xs uppercase tracking-widest text-gray-400 hover:bg-gray-50 hover:text-gray-900 group"
            active-class="bg-gray-900 !text-white shadow-lg shadow-gray-200">
            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
            {{ item.nom }}
          </NuxtLink>
        </div>
      </nav>

      <!-- Footer SideBar -->
      <div class="p-6 border-t border-gray-100 bg-gray-50/50">
        <NuxtLink to="/Login" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-xs font-black text-red-500 hover:bg-red-50 transition-all uppercase tracking-widest">
          <div class="w-2 h-2 rounded-full bg-red-400"></div>
          Sortir
        </NuxtLink>
      </div>
    </aside>

    <!-- Contingut Principal -->
    <main class="flex-1 ml-64 min-h-screen">
      <div class="p-10">
        <slot />
      </div>
    </main>
  </div>
</template>

<script setup>
/**
 * Layout d'Administració (Desktop).
 */
import { computed } from 'vue';

var route = useRoute();

function sortir() {
  useAuthStore().logout();
  navigateTo('/Login');
}

var menuPrincipal = [
  { nom: 'Dashboard', ruta: '/admin' },
  { nom: 'Notificacions', ruta: '/admin/notificacions' }
];

var menuGestio = [
  { nom: 'Usuaris', ruta: '/admin/usuaris' },
  { nom: 'Hàbits', ruta: '/admin/habits' },
  { nom: 'Plantilles', ruta: '/admin/plantilles' },
  { nom: 'Logros', ruta: '/admin/logros' },
  { nom: 'Missions', ruta: '/admin/missions' }
];

var menuSistema = [
  { nom: 'Perfil', ruta: '/admin/perfil' },
  { nom: 'Configuració', ruta: '/admin/configuracio' },
  { nom: 'Foro (Mod)', ruta: '/admin/foro' }
];
</script>

<style scoped>
/* Scrollbar silenciós per a escriptori */
.scrollbar-thin::-webkit-scrollbar {
  width: 4px;
}
.scrollbar-thin::-webkit-scrollbar-track {
  background: transparent;
}
.scrollbar-thin::-webkit-scrollbar-thumb {
  background: #f1f5f9;
  border-radius: 10px;
}
</style>

<template>
  <div class="global-app-container flex">
    <!-- Sidebar Redesign -->
    <aside class="w-64 bg-white/95 backdrop-blur-md border-r border-white/50 flex flex-col fixed h-full shadow-xl z-20">
      <div class="p-6 border-b border-gray-100/50 flex items-center gap-3">
        <div class="w-10 h-10 bg-[#79D45D] border border-[#6fbc58] rounded-[10px] flex items-center justify-center font-black text-white text-xl shadow-md">L</div>
        <h1 class="font-black text-lg text-gray-800 uppercase tracking-tighter leading-none font-bricolage">Loopy Admin</h1>
      </div>

      <nav class="flex-1 px-4 py-6 space-y-4 overflow-y-auto scrollbar-thin">
        <!-- Navegació Principal -->
        <div>
          <p class="text-[9px] font-black text-gray-400 uppercase tracking-[0.2em] px-4 mb-2 font-bricolage">Principal</p>
          <template v-for="item in menuPrincipal" :key="item.nom">
            <NuxtLink v-if="item.ruta" :to="item.ruta"
              class="flex items-center gap-3 px-4 py-3 rounded-[10px] transition-all font-black text-xs uppercase tracking-widest text-gray-500 hover:text-gray-900 group font-bricolage"
              active-class="bg-[#3b82f6] !text-white shadow-lg shadow-blue-100">
              <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
              {{ item.nom }}
            </NuxtLink>
            <div v-else
              class="flex items-center gap-3 px-4 py-3 rounded-[10px] font-black text-xs uppercase tracking-widest text-gray-300 cursor-not-allowed opacity-50 font-bricolage">
              <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
              {{ item.nom }}
            </div>
          </template>
        </div>

        <!-- Gestió CRUD -->
        <div>
          <p class="px-4 text-[9px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2 font-bricolage">Gestió</p>
          <NuxtLink v-for="item in menuGestio" :key="item.ruta" :to="item.ruta"
            class="flex items-center gap-3 px-4 py-3 rounded-[10px] transition-all font-black text-xs uppercase tracking-widest text-gray-500 hover:text-gray-900 group font-bricolage"
            active-class="bg-[#79D45D] !text-white shadow-lg shadow-green-100">
            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
            {{ item.nom }}
          </NuxtLink>
        </div>

        <!-- Sistema -->
        <div>
          <p class="px-4 text-[9px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2 font-bricolage">Sistema</p>
          <NuxtLink v-for="item in menuSistema" :key="item.ruta" :to="item.ruta"
            class="flex items-center gap-3 px-4 py-3 rounded-[10px] transition-all font-black text-xs uppercase tracking-widest text-gray-500 hover:text-gray-900 group font-bricolage"
            active-class="bg-[#7c3aed] !text-white shadow-lg shadow-purple-100">
            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
            {{ item.nom }}
          </NuxtLink>
        </div>
      </nav>

      <!-- Footer SideBar -->
      <div class="p-6 border-t border-gray-100 bg-gray-50/50">
        <button id="btn-admin-logout" type="button" data-cy="admin-logout" @click="sortir" class="flex items-center gap-3 px-4 py-3 rounded-[10px] text-xs font-black text-red-500 hover:text-red-700 transition-all uppercase tracking-widest w-full text-left font-bricolage">
          <div class="w-2 h-2 rounded-full bg-red-400"></div>
          Sortir
        </button>
      </div>
    </aside>

    <!-- Contingut Principal -->
    <main class="flex-1 ml-64 min-h-screen bg-transparent">
      <div class="p-10 relative z-10">
        <slot />
      </div>
    </main>
  </div>
</template>

<script setup>
/**
 * Layout d'Administració (Desktop).
 */
import { onBeforeUnmount, onMounted } from 'vue';

async function sortir() {
  var nuxtApp = useNuxtApp();
  try {
    await useAuthStore().logout();
  } catch (e) {}
  try {
    if (nuxtApp.$socket) {
      nuxtApp.$socket.disconnect();
    }
  } catch (e) {}
  if (typeof window !== 'undefined') {
    window.location.assign('/auth/login');
  }
}

onMounted(function () {
  if (typeof window !== 'undefined') {
    window.__loopyAdminSortir = sortir;
  }
});

onBeforeUnmount(function () {
  if (typeof window !== 'undefined' && window.__loopyAdminSortir === sortir) {
    delete window.__loopyAdminSortir;
  }
});

var menuPrincipal = [
  { nom: 'Dashboard', ruta: '/admin' },
  { nom: 'Fòrum (Pròximament)', ruta: null }
];

var menuGestio = [
  { nom: 'Usuaris', ruta: '/admin/usuaris' },
  { nom: 'Hàbits', ruta: '/admin/habits' },
  { nom: 'Plantilles', ruta: '/admin/plantilles' }
];

var menuSistema = [
  { nom: 'Perfil', ruta: '/admin/perfil' }
];
</script>

<style scoped>
.font-bricolage {
  font-family: "Bricolage Grotesque", system-ui, sans-serif;
}

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

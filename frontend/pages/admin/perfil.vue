<script setup>
/**
 * Perfil de l'Administrador.
 * Visualització i edició de dades personals i de seguretat.
 */
definePageMeta({ layout: 'admin' });

import { ref } from 'vue';

// 1. DADES (VAR)
var config = useRuntimeConfig();

// Perfil via API
var respostaAdmin = useAuthFetch('/api/admin/perfil', {
  key: 'admin_profile'
});

var admin = computed(function () {
  if (respostaAdmin.data.value && respostaAdmin.data.value.success) {
    var d = respostaAdmin.data.value.data;
    var rol = 'Administrador';
    var dataUnio = '2024-01-01';

    if (d.rol) {
      rol = d.rol;
    }
    if (d.created_at) {
      dataUnio = d.created_at.split('T')[0];
    }

    return {
      id: d.id,
      nom: d.nom,
      email: d.email,
      rol: rol,
      avatar: (d.nom || 'A').charAt(0),
      dataUnio: dataUnio,
      ultimAcces: 'Recent'
    };
  }
  return { nom: "Carregant...", email: "...", rol: "...", avatar: "?", dataUnio: "...", ultimAcces: "..." };
});

var popupObert = ref(null); // 'editar_perfil', 'canviar_pass'
var formulari = ref({
  nom: "",
  email: "",
  passVella: "",
  passNova: ""
});

// 2. METHODS (FUNCTION)
function obreEditar() {
  formulari.value.nom = admin.value.nom;
  formulari.value.email = admin.value.email;
  popupObert.value = 'editar_perfil';
}

function obrePass() {
  formulari.value.passVella = "";
  formulari.value.passNova = "";
  popupObert.value = 'canviar_pass';
}

function tancaPopup() {
  popupObert.value = null;
}

async function guardarCanvis() {
  var authStore = useAuthStore();
  var url = '/api/admin/perfil/password';
  var body = {};

  if (popupObert.value === 'editar_perfil') {
    url = '/api/admin/perfil';
    body = { nom: formulari.value.nom, email: formulari.value.email };
  } else {
    body = {
      contrasenya_actual: formulari.value.passVella,
      contrasenya_nova: formulari.value.passNova,
      contrasenya_nova_confirmation: formulari.value.passNova
    };
  }

  try {
    var metode = 'PATCH';
    if (popupObert.value === 'editar_perfil') {
      metode = 'PUT';
    }

    var res = await $fetch(url, {
      method: metode,
      baseURL: config.public.apiUrl,
      headers: authStore.getAuthHeaders(),
      body: body
    });
    
    if (res.success) {
      respostaAdmin.refresh();
      tancaPopup();
    }
  } catch (e) {
    console.error('Error guardant perfil:', e);
  }
}

function obtenirTitolPopup() {
  if (popupObert.value === 'editar_perfil') {
    return 'Editar Dades';
  }
  return 'Canviar Contrasenya';
}
</script>

<template>
  <div class="space-y-12 pb-20">
    <div class="flex justify-between items-end">
      <div>
        <h2 class="text-3xl font-black text-gray-900 uppercase tracking-tighter leading-none">El meu Perfil</h2>
        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-2">Gestió del compte d'administrador</p>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
      <!-- Card d'Informació -->
      <div class="lg:col-span-4 bg-white rounded-[3rem] p-10 shadow-2xl border border-gray-100 flex flex-col items-center">
        <div class="w-32 h-32 rounded-[2.5rem] bg-gradient-to-br from-gray-800 to-black text-white flex items-center justify-center text-4xl font-black shadow-xl shadow-gray-200 mb-8">{{ admin.avatar }}</div>
        <h3 class="text-2xl font-black text-gray-900 uppercase tracking-tighter">{{ admin.nom }}</h3>
        <span class="bg-blue-50 text-blue-600 px-4 py-1.5 rounded-xl font-black text-[10px] uppercase border border-blue-100 mt-3 tracking-widest">{{ admin.rol }}</span>
        
        <div class="w-full mt-10 space-y-4 border-t border-gray-50 pt-10">
          <div class="flex justify-between items-center">
            <span class="text-[9px] font-black text-gray-300 uppercase tracking-widest">Correu</span>
            <span class="text-xs font-bold text-gray-600">{{ admin.email }}</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-[9px] font-black text-gray-300 uppercase tracking-widest">Alta</span>
            <span class="text-xs font-bold text-gray-600">{{ admin.dataUnio }}</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-[9px] font-black text-gray-300 uppercase tracking-widest">Darrer Accés</span>
            <span class="text-xs font-bold text-gray-600">{{ admin.ultimAcces }}</span>
          </div>
        </div>
      </div>

      <!-- Accions i Seguretat -->
      <div class="lg:col-span-8 space-y-8">
        <div class="bg-white rounded-[3rem] p-10 shadow-2xl border border-gray-100 h-full">
          <h4 class="text-[10px] font-black text-gray-300 uppercase tracking-[0.2em] mb-10">Configuració de Seguretat</h4>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <button @click="obreEditar" class="group p-8 rounded-[2rem] border border-gray-100 hover:border-blue-200 hover:bg-blue-50/10 transition-all text-left">
              <div class="w-12 h-12 rounded-2xl bg-blue-100 flex items-center justify-center text-[10px] font-black text-blue-600 mb-6 group-hover:scale-110 transition-transform">PERFIL</div>
              <p class="font-black text-gray-800 text-sm uppercase">Dades Personals</p>
              <p class="text-[10px] text-gray-400 font-bold uppercase mt-2">Canviar nom i correu electrònic.</p>
            </button>

            <button @click="obrePass" class="group p-8 rounded-[2rem] border border-gray-100 hover:border-red-200 hover:bg-red-50/10 transition-all text-left">
              <div class="w-12 h-12 rounded-2xl bg-red-100 flex items-center justify-center text-[10px] font-black text-red-600 mb-6 group-hover:scale-110 transition-transform">PASS</div>
              <p class="font-black text-gray-800 text-sm uppercase">Contrasenya</p>
              <p class="text-[10px] text-gray-400 font-bold uppercase mt-2">Millora la seguretat de l'accés.</p>
            </button>
          </div>

          <div class="mt-10 p-8 rounded-[2rem] bg-gray-50 border border-gray-100 flex items-center justify-between">
            <div>
              <p class="text-xs font-black text-gray-800 uppercase tracking-tight">Autenticació en dos passos (2FA)</p>
              <p class="text-[10px] text-gray-400 font-bold uppercase mt-1">Estat: Desactivat</p>
            </div>
            <button class="bg-gray-200 text-gray-400 px-6 py-2 rounded-xl text-[9px] font-black uppercase tracking-widest cursor-not-allowed">Properament</button>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL (Popups) -->
    <Transition 
      enter-active-class="transition ease-out duration-300"
      enter-from-class="opacity-0 scale-95"
      enter-to-class="opacity-100 scale-100"
      leave-active-class="transition ease-in duration-200"
      leave-from-class="opacity-100 scale-100"
      leave-to-class="opacity-0 scale-95"
    >
      <div v-if="popupObert" class="fixed inset-0 z-50 flex items-center justify-center p-6 bg-gray-900/60 backdrop-blur-md" @click.self="tancaPopup">
        <div class="bg-white w-full max-w-xl rounded-[3rem] shadow-2xl relative overflow-hidden flex flex-col border border-white/20">
          
          <div class="p-10 border-b border-gray-100 flex justify-between items-center bg-gray-50/30">
            <div>
              <h3 class="text-2xl font-black text-gray-900 uppercase tracking-tighter">
                {{ obtenirTitolPopup() }}
              </h3>
              <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">Sistemes de privacitat</p>
            </div>
            <button @click="tancaPopup" class="w-12 h-12 rounded-full bg-white border border-gray-100 flex items-center justify-center font-black text-gray-400 hover:bg-red-50 hover:text-red-500 transition-all">X</button>
          </div>

          <div class="p-12 space-y-6">
            <template v-if="popupObert === 'editar_perfil'">
              <div class="space-y-2">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-4">Nom Administrativ</label>
                <input v-model="formulari.nom" type="text" class="w-full bg-gray-50 border border-gray-100 rounded-[1.5rem] px-6 py-4 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-blue-100 transition-all" />
              </div>
              <div class="space-y-2">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-4">Email Corporatiu</label>
                <input v-model="formulari.email" type="email" class="w-full bg-gray-50 border border-gray-100 rounded-[1.5rem] px-6 py-4 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-blue-100 transition-all" />
              </div>
            </template>

            <template v-if="popupObert === 'canviar_pass'">
              <div class="space-y-2">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-4">Contrasenya Actual</label>
                <input v-model="formulari.passVella" type="password" class="w-full bg-gray-50 border border-gray-100 rounded-[1.5rem] px-6 py-4 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-red-100 transition-all" />
              </div>
              <div class="space-y-2">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-4">Nova Contrasenya</label>
                <input v-model="formulari.passNova" type="password" class="w-full bg-gray-50 border border-gray-100 rounded-[1.5rem] px-6 py-4 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-red-100 transition-all" />
              </div>
            </template>
          </div>

          <div class="p-10 border-t border-gray-100 bg-gray-50/30 flex justify-end gap-4">
            <button @click="tancaPopup" class="px-8 py-4 rounded-[1.5rem] text-[10px] font-black uppercase tracking-widest text-gray-400 hover:bg-gray-100 transition-all">Cancel·lar</button>
            <button @click="guardarCanvis" class="px-10 py-4 rounded-[1.5rem] text-[10px] font-black uppercase tracking-widest bg-gray-900 text-white shadow-xl shadow-gray-100 hover:bg-black transition-all">Guardar Canvis</button>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

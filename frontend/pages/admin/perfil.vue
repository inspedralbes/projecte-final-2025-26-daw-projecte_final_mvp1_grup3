<!--
  Component o pagina Nuxt: perfil.
  Comentaris de codi: agents/frontend/AgentNuxt.md + AgentJavascript.md
-->
<script setup>
/**
 * Perfil de l'Administrador.
 * Visualització i edició de dades personals i de seguretat.
 */
definePageMeta({ layout: 'admin' });

import { ref } from 'vue';
import { authFetch, getBaseUrl } from '~/composables/useApi.js';

// 1. DADES (VAR)
var config = useRuntimeConfig();

// Perfil via API
var { data: adminData, refresh: refreshAdmin } = useAuthFetch('/api/admin/perfil', {
  key: 'admin_profile'
});

var admin = computed(function() {
  if (adminData.value && adminData.value.success) {
    var d = adminData.value.data;
    return {
      id: d.id,
      nom: d.nom,
      email: d.email,
      rol: d.rol || 'Administrador',
      avatar: (d.nom || 'A').charAt(0),
      dataUnio: d.created_at ? d.created_at.split('T')[0] : '2024-01-01',
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
  var url = popupObert.value === 'editar_perfil' ? '/api/admin/perfil' : '/api/admin/perfil/password';
  var body = {};
  
  if (popupObert.value === 'editar_perfil') {
    body = { nom: formulari.value.nom, email: formulari.value.email };
  } else {
    body = {
      contrasenya_actual: formulari.value.passVella,
      contrasenya_nova: formulari.value.passNova,
      contrasenya_nova_confirmation: formulari.value.passNova
    };
  }

  try {
    var fullUrl = getBaseUrl() + url;
    var resposta = await authFetch(fullUrl, {
      method: popupObert.value === 'editar_perfil' ? 'PUT' : 'PATCH',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(body)
    });
    var res = await resposta.json();
    if (res.success) {
      refreshAdmin();
      tancaPopup();
    }
  } catch (e) {
    console.error('Error guardant perfil:', e);
  }
}
</script>

<template>
  <div class="space-y-12 pb-20">
    <div class="flex justify-between items-end">
      <div>
        <h2 class="text-3xl font-black text-[#faf9f9] drop-shadow-sm uppercase tracking-tighter leading-none font-bricolage">El meu Perfil</h2>
        <p class="text-xs font-bold text-white/80 uppercase tracking-widest mt-2 font-comfortaa">Gestió del compte d'administrador</p>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
      <!-- Card d'Informació Bento Glass -->
      <div class="lg:col-span-4 bg-white/95 backdrop-blur-md rounded-[10px] p-8 shadow-xl border border-white/50 flex flex-col items-center">
        <div class="w-24 h-24 rounded-[10px] bg-gradient-to-br from-gray-800 to-black text-white flex items-center justify-center text-3xl font-black shadow-md mb-6 font-bricolage">{{ admin.avatar }}</div>
        <h3 class="text-2xl font-black text-gray-900 uppercase tracking-tighter font-bricolage leading-none">{{ admin.nom }}</h3>
        <span class="bg-blue-50 text-blue-600 px-4 py-1.5 rounded-[10px] font-black text-[10px] uppercase border border-blue-100 mt-4 tracking-widest font-bricolage">{{ admin.rol }}</span>
        
        <div class="w-full mt-8 space-y-4 border-t border-gray-100/50 pt-6 font-comfortaa">
          <div class="flex justify-between items-center">
            <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest font-bricolage">Correu</span>
            <span class="text-xs font-bold text-gray-600">{{ admin.email }}</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest font-bricolage">Alta</span>
            <span class="text-xs font-bold text-gray-600">{{ admin.dataUnio }}</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest font-bricolage">Darrer Accés</span>
            <span class="text-xs font-bold text-gray-600">{{ admin.ultimAcces }}</span>
          </div>
        </div>
      </div>

      <!-- Accions i Seguretat Bento Glass -->
      <div class="lg:col-span-8 space-y-8">
        <div class="bg-white/95 backdrop-blur-md rounded-[10px] p-8 shadow-xl border border-white/50 h-full">
          <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-8 font-bricolage">Configuració de Seguretat</h4>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 font-comfortaa">
            <button @click="obreEditar" class="group p-6 rounded-[10px] bg-white/50 border border-gray-200 transition-all text-left">
              <div class="w-10 h-10 rounded-[10px] bg-blue-100 flex items-center justify-center text-[10px] font-black text-blue-600 mb-4 transition-transform font-bricolage">PERFIL</div>
              <p class="font-black text-gray-800 text-sm uppercase font-bricolage">Dades Personals</p>
              <p class="text-[10px] text-gray-400 font-bold uppercase mt-2">Canviar nom i correu electrònic.</p>
            </button>

            <button @click="obrePass" class="group p-6 rounded-[10px] bg-white/50 border border-gray-200 transition-all text-left">
              <div class="w-10 h-10 rounded-[10px] bg-red-100 flex items-center justify-center text-[10px] font-black text-red-600 mb-4 transition-transform font-bricolage">PASS</div>
              <p class="font-black text-gray-800 text-sm uppercase font-bricolage">Contrasenya</p>
              <p class="text-[10px] text-gray-400 font-bold uppercase mt-2">Millora la seguretat de l'accés.</p>
            </button>
          </div>

          <div class="mt-8 p-6 rounded-[10px] bg-gray-50/50 border border-gray-100/50 flex items-center justify-between font-comfortaa">
            <div>
              <p class="text-xs font-black text-gray-800 uppercase tracking-tight font-bricolage">Autenticació en dos passos (2FA)</p>
              <p class="text-[10px] text-gray-400 font-bold uppercase mt-1">Estat: Desactivat</p>
            </div>
            <button class="bg-gray-200/50 text-gray-400 px-6 py-2 rounded-[10px] text-[9px] font-black uppercase tracking-widest cursor-not-allowed font-bricolage">Properament</button>
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
        <div class="bg-white/95 backdrop-blur-md w-full max-w-xl rounded-[10px] shadow-2xl relative overflow-hidden flex flex-col border border-white/50">
          
          <div class="p-8 border-b border-gray-100 flex justify-between items-center bg-white/50">
            <div>
              <h3 class="text-2xl font-black text-gray-900 uppercase tracking-tighter font-bricolage">
                {{ popupObert === 'editar_perfil' ? 'Editar Dades' : 'Canviar Contrasenya' }}
              </h3>
              <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1 font-comfortaa">Sistemes de privacitat</p>
            </div>
            <button @click="tancaPopup" class="w-10 h-10 rounded-[10px] bg-white border border-gray-200 flex items-center justify-center font-black text-gray-400 hover:bg-red-50 hover:text-red-500 transition-all uppercase text-[10px] font-bricolage">Tancar</button>
          </div>

          <div class="p-8 space-y-6 font-comfortaa">
            <template v-if="popupObert === 'editar_perfil'">
              <div class="space-y-2">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1 font-bricolage">Nom Administratiu</label>
                <input v-model="formulari.nom" type="text" class="w-full bg-white/50 border border-gray-200 rounded-[10px] px-5 py-3 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-[#79D45D]/10 focus:border-[#79D45D]/50 transition-all font-comfortaa" />
              </div>
              <div class="space-y-2">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1 font-bricolage">Email Corporatiu</label>
                <input v-model="formulari.email" type="email" class="w-full bg-white/50 border border-gray-200 rounded-[10px] px-5 py-3 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-[#79D45D]/10 focus:border-[#79D45D]/50 transition-all font-comfortaa" />
              </div>
            </template>

            <template v-if="popupObert === 'canviar_pass'">
              <div class="space-y-2">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1 font-bricolage">Contrasenya Actual</label>
                <input v-model="formulari.passVella" type="password" class="w-full bg-white/50 border border-gray-200 rounded-[10px] px-5 py-3 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-red-100 transition-all font-comfortaa" />
              </div>
              <div class="space-y-2">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1 font-bricolage">Nova Contrasenya</label>
                <input v-model="formulari.passNova" type="password" class="w-full bg-white/50 border border-gray-200 rounded-[10px] px-5 py-3 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-red-100 transition-all font-comfortaa" />
              </div>
            </template>
          </div>

          <div class="p-6 border-t border-gray-100 bg-white/50 flex justify-end gap-4">
            <button @click="tancaPopup" class="px-6 py-3 rounded-[10px] text-[10px] font-black uppercase tracking-widest text-gray-400 hover:bg-gray-100 transition-all font-bricolage">Cancel·lar</button>
            <button @click="guardarCanvis" class="px-8 py-3 rounded-[10px] text-[10px] font-black uppercase tracking-widest bg-[#79D45D] hover:bg-[#6fbc58] text-white border border-[#6fbc58] shadow-md transition-all font-bricolage">Guardar Canvis</button>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

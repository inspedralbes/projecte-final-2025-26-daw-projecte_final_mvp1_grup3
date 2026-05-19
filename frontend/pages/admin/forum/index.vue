<!--
  Component o pagina Nuxt: index.
  Comentaris de codi: agents/frontend/AgentNuxt.md + AgentJavascript.md
-->
<script setup>
/**
 * Hub de moderació del fòrum: reports d'usuaris vs contingut.
 */
definePageMeta({ layout: 'admin' });
</script>

<template>
  <div class="space-y-8 pb-20">
    <div>
      <h2 class="text-3xl font-black text-[#faf9f9] drop-shadow-sm uppercase tracking-tighter leading-none font-bricolage">Fòrum & Reports</h2>
      <p class="text-xs font-bold text-white/80 uppercase tracking-widest mt-2 font-comfortaa">Tria el tipus de moderació</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <NuxtLink
        to="/admin/forum/usuaris"
        class="group bg-white/95 backdrop-blur-md rounded-[10px] p-8 shadow-xl border border-white/50 hover:border-yellow-200 transition-all"
      >
        <div class="text-4xl mb-4">🚫</div>
        <h3 class="text-xl font-black text-gray-800 uppercase tracking-tight font-bricolage mb-2">Reports d'usuaris</h3>
        <p class="text-xs text-gray-500 font-comfortaa leading-relaxed">
          Denúncies contra perfils d'usuari. Prohibeix o desprohibeix comptes segons les infraccions reportades.
        </p>
        <span class="inline-block mt-6 text-[10px] font-black text-yellow-600 uppercase tracking-widest font-bricolage group-hover:underline">
          Gestionar bans →
        </span>
      </NuxtLink>

      <NuxtLink
        to="/admin/forum/contingut"
        class="group bg-white/95 backdrop-blur-md rounded-[10px] p-8 shadow-xl border border-white/50 hover:border-blue-200 transition-all"
      >
        <div class="text-4xl mb-4">📝</div>
        <h3 class="text-xl font-black text-gray-800 uppercase tracking-tight font-bricolage mb-2">Reports de contingut</h3>
        <p class="text-xs text-gray-500 font-comfortaa leading-relaxed">
          Posts i comentaris reportats al fòrum social. Revisa, edita o elimina el contingut denunciat.
        </p>
        <span class="inline-block mt-6 text-[10px] font-black text-blue-600 uppercase tracking-widest font-bricolage group-hover:underline">
          Moderar posts →
        </span>
      </NuxtLink>
    </div>
  </div>
</template>

<template>
  <div class="min-h-screen bg-gray-50">
    <HeaderSocial />
    <div class="max-w-5xl mx-auto px-4 py-8">
       <div class="flex justify-between items-center mb-6">
         <h1 class="text-3xl font-bold text-gray-800">Clans</h1>
         <button @click="showCreate = !showCreate" class="px-5 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 shadow transition-colors font-medium">
            {{ showCreate ? 'Tornar als Clans' : 'Crear Nou Clan' }}
         </button>
       </div>
       
       <transition name="fade" mode="out-in">
          <ClanSettings v-if="showCreate" @cancel="showCreate = false" @saved="showCreate = false" />
          <ClanList v-else />
       </transition>
    </div>
  </div>
</template>

<script>
import HeaderSocial from "~/components/HeaderSocial.vue";
import ClanList from "~/components/clans/ClanList.vue";
import ClanSettings from "~/components/clans/ClanSettings.vue";
import { useAuthStore } from "~/stores/useAuthStore.js";

export default {
  name: "ClansIndexPage",
  middleware: ["auth"],
  components: {
    HeaderSocial,
    ClanList,
    ClanSettings
  },
  data: function() {
     return {
        showCreate: false
     }
  },
  mounted: function() {
     var authStore = useAuthStore();
     if (authStore.user && authStore.user.nivell < 5) {
        alert("Has de ser nivell 5 o superior per accedir als clans.");
        this.$router.push("/social");
     }
  }
}
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter, .fade-leave-to {
  opacity: 0;
}
</style>

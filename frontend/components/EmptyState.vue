<template>
  <div class="empty-state-card max-w-md mx-auto my-6 text-center bg-white p-8 rounded-[32px] border-4 border-gray-100 shadow-xl font-['Outfit',sans-serif] transition-transform duration-300 hover:scale-[1.02]">
    <img :src="iconUrl" class="w-24 h-auto mx-auto mb-4 drop-shadow-md pixelated" :alt="title" />
    <h3 class="text-xl font-black text-gray-800 mb-2 tracking-tight">{{ title }}</h3>
    <p class="text-gray-500 font-semibold text-[14px] leading-snug mb-4">
      {{ description }}
    </p>
    <slot name="action"></slot>
  </div>
</template>

<script>
import logoPerfil from "~/assets/img/Icones/Icona_Logo_Perfil.png";
import coinIcon from "~/assets/img/Icones/Icona_Moneda.png";
import xpIcon from "~/assets/img/Icones/Icona_Experiencia.png";
import ratxaIcon from "~/assets/img/Icones/Icona_Ratxa.png";
import logrosIcon from "~/assets/img/Icones/Icona_Logros.png";

export default {
  name: "EmptyState",
  props: {
    title: {
      type: String,
      required: true
    },
    description: {
      type: String,
      required: true
    },
    icon: {
      type: String,
      default: "logo" // "logo", "moneda", "experiencia", "ratxa", "logros"
    }
  },
  computed: {
    iconUrl: function() {
      switch (this.icon) {
        case "moneda":
          return coinIcon;
        case "experiencia":
          return xpIcon;
        case "ratxa":
          return ratxaIcon;
        case "logros":
          return logrosIcon;
        case "logo":
        default:
          return logoPerfil;
      }
    }
  }
}
</script>

<style scoped>
.pixelated {
  image-rendering: pixelated;
}
</style>

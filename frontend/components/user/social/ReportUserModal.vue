<template>
  <div v-if="show" class="fixed inset-0 z-50 flex flex-col justify-end pointer-events-auto">
    <!-- Fons fosc semitransparent per tancar en fer clic a fora -->
    <div class="fixed inset-0 bg-black/50 transition-opacity" @click="close"></div>
    
    <!-- Contingut del desplegable -->
    <div class="relative w-full rounded-t-[32px] overflow-hidden animate-slide-up shadow-[0_-8px_30px_rgba(0,0,0,0.12)] flex flex-col" style="background-color: #FF8DA6; max-height: 85vh;">
      
      <!-- Línia superior decorativa (handle) -->
      <div class="w-full flex justify-center pt-4 pb-2">
        <div class="w-12 h-1.5 bg-white/40 rounded-full"></div>
      </div>
      
      <div class="px-6 pb-6 pt-2 overflow-y-auto custom-scrollbar flex-1">
        <h2 class="text-2xl font-['Bricolage_Grotesque'] font-bold text-white mb-4 text-center">
          {{ titolModal }}
        </h2>
        
        <form @submit.prevent="submitReport" class="flex flex-col gap-2">
          
          <!-- Opcions de report -->
          <div class="flex flex-col gap-2">
            <label v-for="(label, key) in motius" :key="key" class="report-option" :class="{ 'report-option--selected': selectedMotiu === key }">
              <input type="radio" v-model="selectedMotiu" :value="key" class="hidden" />
              <div class="report-radio-box">
                <div class="report-radio-dot" v-show="selectedMotiu === key"></div>
              </div>
              <span class="text-white font-['Comfortaa'] font-bold">{{ label }}</span>
            </label>
          </div>
          
          <!-- Textarea si es selecciona "Altres" -->
          <div v-if="selectedMotiu === 'altres'" class="mt-2 animate-fade-in">
            <label class="block text-white/90 text-sm font-['Comfortaa'] font-bold mb-2">
              Especifica el motiu (màxim 100 paraules):
            </label>
            <textarea 
              v-model="detalls" 
              class="w-full bg-white/10 border-2 border-white/30 text-white placeholder-white/50 rounded-xl p-3 font-['Comfortaa'] text-sm focus:outline-none focus:border-white focus:bg-white/20 transition-all resize-none"
              rows="3"
              placeholder="Explica breument què ha passat..."
            ></textarea>
            <div class="text-right text-xs mt-1" :class="wordCount > 100 ? 'text-yellow-300 font-bold' : 'text-white/70'">
              {{ wordCount }} / 100 paraules
            </div>
          </div>
          
          <!-- Botons d'acció -->
          <div class="flex w-full gap-4 mt-3 items-stretch">
            <button 
              type="button" 
              @click="close" 
              class="w-1/2 bg-white/20 hover:bg-white/30 text-white font-['Comfortaa'] font-bold py-3 rounded-xl transition-colors flex items-center justify-center text-center shadow-[0_4px_0_rgba(255,255,255,0.15)] active:translate-y-[4px] active:shadow-none"
            >
              Enrere
            </button>
            <button 
              type="submit" 
              class="w-1/2 bg-[#FFD166] hover:bg-[#ffc233] text-gray-900 font-['Comfortaa'] font-bold py-3 rounded-xl shadow-[0_4px_0_#d9a738] active:translate-y-[4px] active:shadow-none transition-all flex items-center justify-center text-center"
              :disabled="!isValid"
              :class="{ 'opacity-50 cursor-not-allowed': !isValid }"
            >
              Reportar
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "ReportUserModal",
  props: {
    show: {
      type: Boolean,
      required: true
    },
    reportType: {
      type: String,
      default: "user",
      validator: function (v) {
        return ["user", "post", "comment"].indexOf(v) !== -1;
      }
    },
    contentId: {
      type: [Number, String],
      default: null
    }
  },
  emits: ["close", "submit"],
  data() {
    return {
      selectedMotiu: null,
      detalls: "",
      motius: {
        nom: "Nom inapropiat",
        insult: "Text insultant",
        us_indegut: "Ús indegut de l'app",
        comentari: "Comentari ofensiu",
        altres: "Altres"
      }
    };
  },
  computed: {
    titolModal: function () {
      if (this.reportType === "post") {
        return "Reportar post";
      }
      if (this.reportType === "comment") {
        return "Reportar comentari";
      }
      return "Reportar usuari";
    },
    wordCount() {
      if (!this.detalls.trim()) return 0;
      return this.detalls.trim().split(/\s+/).length;
    },
    isValid() {
      if (!this.selectedMotiu) return false;
      if (this.selectedMotiu === 'altres') {
        return this.wordCount > 0 && this.wordCount <= 100;
      }
      return true;
    }
  },
  watch: {
    show(newVal) {
      if (newVal) {
        this.selectedMotiu = null;
        this.detalls = "";
      }
    }
  },
  methods: {
    close() {
      this.$emit("close");
    },
    submitReport() {
      if (!this.isValid) return;
      this.$emit("submit", {
        reportType: this.reportType,
        contentId: this.contentId,
        motiu: this.selectedMotiu,
        detalls: this.detalls
      });
    }
  }
};
</script>

<style scoped>
@keyframes slide-up {
  from {
    transform: translateY(100%);
  }
  to {
    transform: translateY(0);
  }
}

.animate-slide-up {
  animation: slide-up 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes fade-in {
  from {
    opacity: 0;
    transform: translateY(-5px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fade-in {
  animation: fade-in 0.2s ease-out forwards;
}

.report-option {
  display: flex;
  align-items: center;
  gap: 12px;
  background-color: rgba(255, 255, 255, 0.15);
  border: 2px solid transparent;
  padding: 10px 16px;
  border-radius: 16px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.report-option:hover {
  background-color: rgba(255, 255, 255, 0.25);
}

.report-option--selected {
  background-color: rgba(255, 255, 255, 0.25);
  border-color: #FFD166;
}

.report-radio-box {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  border: 2px solid rgba(255, 255, 255, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  transition: all 0.2s ease;
}

.report-option--selected .report-radio-box {
  border-color: #FFD166;
}

.report-radio-dot {
  width: 12px;
  height: 12px;
  background-color: #FFD166;
  border-radius: 50%;
}

.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.3);
  border-radius: 4px;
}
</style>

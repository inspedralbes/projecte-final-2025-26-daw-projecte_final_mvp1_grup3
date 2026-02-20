<script>
// A. Importar la imatge del bosc
import boscImg from '~/assets/img/Bosque.png'

export default {
  /**
   * Retorna les dades inicials del component.
   */
  data: function() {
    // B. Definir l'estil del fons de la mascota
    var estilFons = {
      backgroundImage: 'url(' + boscImg + ')',
      backgroundSize: 'cover',
      backgroundPosition: 'center'
    };

    return {
      estilFons: estilFons
    };
  }
}
</script>

<template>
  <main class="min-h-screen bg-gradient-to-b from-gray-50 to-gray-100 p-8">
    <div class="max-w-7xl mx-auto">
      <!-- Grid Principal -->
      <div class="grid grid-cols-12 gap-6">
        
        <!-- COSTAT ESQUERRE: Missions i Perfil -->
        <div class="col-span-3 space-y-6">
          
          <!-- Targeta Missions Diàries -->
          <div class="bg-white rounded-2xl shadow-lg p-6 border-l-4 border-orange-400">
            <div class="flex items-center gap-2 mb-4">
              <div class="w-6 h-6 bg-orange-400 rounded-full flex items-center justify-center">
                <span class="text-white text-sm">✓</span>
              </div>
              <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wide">Missions Diàries</h2>
            </div>
            
            <div class="space-y-3">
              <div class="bg-gray-50 rounded-lg p-3">
                <p class="text-gray-700 font-semibold text-sm">Missió Diària</p>
                <p class="text-2xl font-bold text-orange-500">0/1</p>
              </div>
            </div>

            <!-- Divisor -->
            <div class="h-px bg-gray-200 my-4"></div>

            <!-- Perfil Usuari -->
            <div class="text-center">
              <div class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 mx-auto mb-3 flex items-center justify-center">
                <span class="text-3xl"></span>
              </div>
              <h3 class="font-bold text-gray-800 text-sm">Nom</h3>
              <p class="text-xs text-gray-500 mb-2">Etiqueta</p>
              <div class="flex justify-center items-center gap-1 text-xs text-gray-600">
                <span>Lv 1</span>
                <div class="w-20 h-1 bg-gray-200 rounded-full">
                </div>
              </div>
            </div>
          </div>

          <!-- Targeta Últims Assoliments -->
          <div class="bg-white rounded-2xl shadow-lg p-6">
            <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wide mb-4">Últims Assoliments</h3>
            <div class="flex justify-around items-center">
              <div class="w-12 h-12 rounded-full bg-orange-100 flex items-center justify-center text-lg hover:scale-110 transition"></div>
              <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-lg hover:scale-110 transition"></div>
              <div class="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center text-lg hover:scale-110 transition"></div>
            </div>
          </div>
        </div>

        <!-- CENTRE: El teu Monstre -->
        <div class="col-span-6 space-y-6">
          
          <!-- Targeta El teu Monstre -->
          <div class="rounded-2xl shadow-lg p-8 flex flex-col items-center justify-center relative">
            
            <!-- Fons Decoratiu -->
            <div class="absolute inset-0 rounded-2xl opacity-40"></div>
            
            <!-- Contingut -->
            <div class="relative z-10">
              <div class="flex items-center justify-between w-full mb-4">
                <div>
                  <h2 class="text-lg font-bold text-gray-800">EL TEU MONSTRE</h2>
                  <p class="text-xs text-gray-500">Lv 1</p>
                </div>
                <div class="text-2xl">✓</div>
              </div>
              
              <!-- Imatge Monstre -->
               <div class="rounded-2xl shadow-lg p-8 flex flex-col items-center justify-center relative" :style="estilFons" style="min-width: 450px">
              <div class="w-40 h-40 rounded-xl flex items-center justify-center mb-6 overflow-hidden mx-auto">
                  <img src="assets/img/Mascota.png" alt="El teu monstre" class="w-full h-full object-cover" />
                </div>
              </div>
              <p class="text-center text-gray-600 text-sm">¡Ho estàs fent genial!</p>
            </div>
          </div>
        </div>

        <!-- COSTAT DRET: Hàbits -->
        <div class="col-span-3 space-y-6">
          
          <!-- Capçalera Hàbits -->
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-bold text-gray-800">HÀBITS</h2>
            <a href="#" class="text-blue-500 text-xs font-semibold hover:underline">VEURE TOT</a>
          </div>

          <!-- Llista d'Hàbits -->
          <div class="space-y-3">
            
          </div>

          <!-- Targeta Diari -->
        </div>
      </div>
    </div>
  </main>
</template>

<style scoped>
/* Estilos adicionales si es necesario */
</style>
<script setup>
import bosqueImg from '~/assets/img/Bosque.png'

const backgroundStyle = {
  backgroundImage: `url(${bosqueImg})`,
  backgroundSize: 'cover',
  backgroundPosition: 'center'
}
</script>

<template>
  <main class="min-h-screen bg-gradient-to-b from-gray-50 to-gray-100 p-8">
    <div class="max-w-7xl mx-auto">
      <!-- Grid Principal -->
      <div class="grid grid-cols-12 gap-6">
        
        <!-- LADO IZQUIERDO: Misiones y Perfil -->
        <div class="col-span-3 space-y-6">
          
          <!-- Tarjeta Misiones Diarias -->
          <div class="bg-white rounded-2xl shadow-lg p-6 border-l-4 border-orange-400">
            <div class="flex items-center gap-2 mb-4">
              <div class="w-6 h-6 bg-orange-400 rounded-full flex items-center justify-center">
                <span class="text-white text-sm">✓</span>
              </div>
              <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wide">Misiones Diarias</h2>
            </div>
            
            <div class="space-y-3">
              <div class="bg-gray-50 rounded-lg p-3">
                <p class="text-gray-700 font-semibold text-sm">Mision Diaria</p>
                <p class="text-2xl font-bold text-orange-500">0/1</p>
              </div>
            </div>

            <!-- Divisor -->
            <div class="h-px bg-gray-200 my-4"></div>

            <!-- Perfil Usuario -->
            <div class="text-center">
              <div class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 mx-auto mb-3 flex items-center justify-center">
                <span class="text-3xl"></span>
              </div>
              <h3 class="font-bold text-gray-800 text-sm">Nombre</h3>
              <p class="text-xs text-gray-500 mb-2">Etiqueta</p>
              <div class="flex justify-center items-center gap-1 text-xs text-gray-600">
                <span>Lv 1</span>
                <div class="w-20 h-1 bg-gray-200 rounded-full">
                </div>
              </div>
            </div>
          </div>

          <!-- Tarjeta Últimos Logros -->
          <div class="bg-white rounded-2xl shadow-lg p-6">
            <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wide mb-4">Últimos Logros</h3>
            <div class="flex justify-around items-center">
              <div class="w-12 h-12 rounded-full bg-orange-100 flex items-center justify-center text-lg hover:scale-110 transition"></div>
              <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-lg hover:scale-110 transition"></div>
              <div class="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center text-lg hover:scale-110 transition"></div>
            </div>
          </div>
        </div>

        <!-- CENTRO: Tu Monstruo -->
        <div class="col-span-6 space-y-6">
          
          <!-- Tarjeta Tu Monstruo -->
          <div class="rounded-2xl shadow-lg p-8 flex flex-col items-center justify-center relative">
            
            <!-- Fondo Decorativo -->
            <div class="absolute inset-0 rounded-2xl opacity-40"></div>
            
            <!-- Contenido -->
            <div class="relative z-10">
              <div class="flex items-center justify-between w-full mb-4">
                <div>
                  <h2 class="text-lg font-bold text-gray-800">TU MONSTRUO</h2>
                  <p class="text-xs text-gray-500">Lv 1</p>
                </div>
                <div class="text-2xl">✓</div>
              </div>
              
              <!-- Imagen Monstruo -->
               <div class="rounded-2xl shadow-lg p-8 flex flex-col items-center justify-center relative" :style="backgroundStyle" style="min-width: 450px">
              <div class="w-40 h-40 rounded-xl flex items-center justify-center mb-6 overflow-hidden mx-auto">
                  <img src="assets/img/Mascota.png" alt="Tu monstruo" class="w-full h-full object-cover" />
                </div>
              </div>
              <p class="text-center text-gray-600 text-sm">¡Lo estás haciendo genial!</p>
            </div>
          </div>
        </div>

        <!-- LADO DERECHO: Hábitos -->
        <div class="col-span-3 space-y-6">
          
          <!-- Encabezado Hábitos -->
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-bold text-gray-800">HÁBITOS</h2>
            <a href="#" class="text-blue-500 text-xs font-semibold hover:underline">VER TODO</a>
          </div>

          <!-- Lista de Hábitos -->
          <div class="space-y-3">
            
          </div>

          <!-- Tarjeta Diario -->
        </div>
      </div>
    </div>
  </main>
</template>

<style scoped>
/* Estilos adicionales si es necesario */
</style>
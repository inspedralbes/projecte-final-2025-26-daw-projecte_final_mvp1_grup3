<script setup>

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
                <p class="text-gray-700 font-semibold text-sm">Login Streak</p>
                <p class="text-2xl font-bold text-orange-500">1/1</p>
              </div>
            </div>

            <!-- Divisor -->
            <div class="h-px bg-gray-200 my-4"></div>

            <!-- Perfil Usuario -->
            <div class="text-center">
              <div class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 mx-auto mb-3 flex items-center justify-center">
                <span class="text-3xl">👨</span>
              </div>
              <h3 class="font-bold text-gray-800 text-sm">Alex Martinez</h3>
              <p class="text-xs text-gray-500 mb-2">Habit Master</p>
              <div class="flex justify-center items-center gap-1 text-xs text-gray-600">
                <span>Lv 13</span>
                <div class="w-20 h-1 bg-gray-200 rounded-full">
                  <div class="h-full bg-green-500 rounded-full" style="width: 65%"></div>
                </div>
              </div>
            </div>
          </div>

          <!-- Tarjeta Últimos Logros -->
          <div class="bg-white rounded-2xl shadow-lg p-6">
            <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wide mb-4">Últimos Logros</h3>
            <div class="flex justify-around items-center">
              <div class="w-12 h-12 rounded-full bg-orange-100 flex items-center justify-center text-lg hover:scale-110 transition">🏅</div>
              <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-lg hover:scale-110 transition">⭐</div>
              <div class="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center text-lg hover:scale-110 transition">🎯</div>
            </div>
          </div>
        </div>

        <!-- CENTRO: Tu Monstruo -->
        <div class="col-span-6 space-y-6">
          
          <!-- Tarjeta Tu Monstruo -->
          <div class="bg-white rounded-2xl shadow-lg p-8 flex flex-col items-center">
            <div class="flex items-center justify-between w-full mb-4">
              <div>
                <h2 class="text-lg font-bold text-gray-800">TU MONSTRUO</h2>
                <p class="text-xs text-gray-500">Lv 5 • Guardian de la</p>
              </div>
              <div class="text-2xl">✓</div>
            </div>
            
            <!-- Imagen Monstruo -->
            <div class="w-full h-64 bg-black rounded-xl flex items-center justify-center mb-6">
              <span class="text-6xl">🔶</span>
            </div>
            
            <p class="text-center text-gray-600 text-sm">¡Lo estás haciendo genial!</p>
          </div>

          <!-- Tarjetas de Gráficos -->
          <div class="grid grid-cols-2 gap-6">
            
            <!-- Productividad -->
            <div class="bg-white rounded-2xl shadow-lg p-6">
              <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wide mb-4">Productividad</h3>
              <div class="h-32 flex items-end justify-around gap-2">
                <div class="w-3 bg-blue-300 rounded-t" style="height: 40%"></div>
                <div class="w-3 bg-blue-400 rounded-t" style="height: 60%"></div>
                <div class="w-3 bg-blue-300 rounded-t" style="height: 45%"></div>
                <div class="w-3 bg-blue-500 rounded-t" style="height: 75%"></div>
                <div class="w-3 bg-blue-400 rounded-t" style="height: 55%"></div>
              </div>
            </div>

            <!-- Consistencia -->
            <div class="bg-white rounded-2xl shadow-lg p-6">
              <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wide mb-4">Consistencia</h3>
              <div class="h-32 flex items-end justify-around gap-2">
                <div class="w-6 bg-purple-300 rounded-t" style="height: 50%"></div>
                <div class="w-6 bg-purple-400 rounded-t" style="height: 70%"></div>
                <div class="w-6 bg-purple-300 rounded-t" style="height: 55%"></div>
                <div class="w-6 bg-purple-500 rounded-t" style="height: 85%"></div>
                <div class="w-6 bg-purple-400 rounded-t" style="height: 65%"></div>
              </div>
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
            
            <!-- Hábito 1 - Sin completar -->
            <div class="bg-white rounded-2xl shadow-md p-4 hover:shadow-lg transition flex items-center justify-between">
              <div class="flex items-center gap-3 flex-1">
                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                  ⭕
                </div>
                <div>
                  <p class="font-semibold text-gray-800 text-sm">Meditar</p>
                  <p class="text-xs text-gray-500">10 mins • Mañana</p>
                </div>
              </div>
              <div class="w-5 h-5 rounded-full border-2 border-red-300"></div>
            </div>

            <!-- Hábito 2 - Completado -->
            <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-2xl shadow-md p-4 hover:shadow-lg transition flex items-center justify-between border-l-4 border-blue-500">
              <div class="flex items-center gap-3 flex-1">
                <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center">
                  ✓
                </div>
                <div>
                  <p class="font-semibold text-gray-800 text-sm">Beber Agua</p>
                  <p class="text-xs text-gray-500">2L • Salud</p>
                </div>
              </div>
              <div class="w-5 h-5 rounded-full bg-blue-500 flex items-center justify-center">
                <span class="text-white text-xs">✓</span>
              </div>
            </div>

            <!-- Hábito 3 - Sin completar -->
            <div class="bg-white rounded-2xl shadow-md p-4 hover:shadow-lg transition flex items-center justify-between">
              <div class="flex items-center gap-3 flex-1">
                <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center">
                  📖
                </div>
                <div>
                  <p class="font-semibold text-gray-800 text-sm">Leer Libro</p>
                  <p class="text-xs text-gray-500">30 mins • Educación</p>
                </div>
              </div>
              <div class="w-5 h-5 rounded-full border-2 border-purple-300"></div>
            </div>

            <!-- Hábito 4 - Sin completar -->
            <div class="bg-white rounded-2xl shadow-md p-4 hover:shadow-lg transition flex items-center justify-between">
              <div class="flex items-center gap-3 flex-1">
                <div class="w-10 h-10 rounded-full bg-cyan-100 flex items-center justify-center">
                  💪
                </div>
                <div>
                  <p class="font-semibold text-gray-800 text-sm">Gimnasio</p>
                  <p class="text-xs text-gray-500">1 hora • Cuerpo</p>
                </div>
              </div>
              <div class="w-5 h-5 rounded-full border-2 border-cyan-300"></div>
            </div>

            <!-- Hábito 5 - Sin completar -->
            <div class="bg-white rounded-2xl shadow-md p-4 hover:shadow-lg transition flex items-center justify-between">
              <div class="flex items-center gap-3 flex-1">
                <div class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center">
                  💻
                </div>
                <div>
                  <p class="font-semibold text-gray-800 text-sm">Revisar Código</p>
                  <p class="text-xs text-gray-500">2h horas • Trabajo</p>
                </div>
              </div>
              <div class="w-5 h-5 rounded-full border-2 border-orange-300"></div>
            </div>
          </div>

          <!-- Tarjeta Diario -->
          <div class="bg-yellow-300 rounded-2xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-3">
              <div class="flex items-center gap-2">
                <div class="w-6 h-6 bg-yellow-500 rounded flex items-center justify-center">
                  ✓
                </div>
                <h3 class="font-bold text-gray-800 text-sm">Diario</h3>
              </div>
              <button class="text-gray-600 hover:text-gray-800">✏️</button>
            </div>
            <p class="text-xs text-gray-700 font-semibold">Completado • Hoy</p>
          </div>
        </div>
      </div>
    </div>
  </main>
</template>

<style scoped>
/* Estilos adicionales si es necesario */
</style>
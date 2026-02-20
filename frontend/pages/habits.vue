<template>
  <div class="min-h-screen bg-gray-50 p-6">
    <div class="max-w-7xl mx-auto">
      <h1 class="text-3xl font-bold text-gray-800 mb-8">Crear Hàbit</h1>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Columna 1 i 2: Seccions del formulari -->
        <div class="lg:col-span-2 space-y-6">
          
          <!-- 1. Detalls de l'Hàbit -->
          <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
            <div class="flex items-center gap-3 mb-4">
              <div class="bg-green-100 p-2 rounded-lg">
                <span class="text-xl">✏️</span>
              </div>
              <h2 class="text-lg font-bold text-gray-800">Detalls de l'Hàbit</h2>
            </div>
            
            <div class="space-y-4">
              <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Nom de l'hàbit</label>
                <input v-model="formulari.titol" type="text" placeholder="Ex: Beure 2L d'aigua, Llegir 30 min..." class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-500 focus:bg-white transition-all" />
              </div>
              
              <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Motivació (Opcional)</label>
                <textarea v-model="formulari.motivacio" placeholder="Per què vols començar aquest hàbit?" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-500 focus:bg-white transition-all resize-none h-24"></textarea>
              </div>

              <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Icona Ràpida</label>
                <div class="flex gap-3">
                  <button v-for="icona in icones" :key="icona" @click="seleccionarIcona(icona)" :class="{'bg-green-500 text-white': formulari.icona === icona, 'bg-gray-100 text-gray-600 hover:bg-gray-200': formulari.icona !== icona}" class="w-10 h-10 rounded-full flex items-center justify-center transition-colors">
                    {{ icona }}
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- 2. Categoria -->
          <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
            <div class="flex items-center gap-3 mb-4">
              <div class="bg-orange-100 p-2 rounded-lg">
                <span class="text-xl">Shapes</span>
              </div>
              <h2 class="text-lg font-bold text-gray-800">Categoria</h2>
            </div>
            
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
              <button v-for="cat in categories" :key="cat.id" @click="seleccionarCategoria(cat.id)" :class="{'ring-2 ring-green-500 bg-green-50': formulari.categoria === cat.id, 'bg-white border border-gray-200 hover:border-green-300': formulari.categoria !== cat.id}" class="p-4 rounded-xl flex flex-col items-center justify-center gap-2 transition-all">
                <span class="text-2xl">{{ cat.icon }}</span>
                <span class="text-sm font-medium text-gray-700">{{ cat.name }}</span>
              </button>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- 3. Planificació -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
              <div class="flex items-center gap-3 mb-4">
                <div class="bg-blue-100 p-2 rounded-lg">
                  <span class="text-xl">📅</span>
                </div>
                <h2 class="text-lg font-bold text-gray-800">Planificació</h2>
              </div>

              <div class="space-y-4">
                <div>
                  <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Freqüència</label>
                  <div class="flex bg-gray-100 rounded-lg p-1">
                    <button v-for="freq in frequencies" :key="freq" @click="formulari.frequenciaTipus = freq" :class="{'bg-white shadow-sm text-gray-800': formulari.frequenciaTipus === freq, 'text-gray-500 hover:text-gray-700': formulari.frequenciaTipus !== freq}" class="flex-1 py-1.5 text-sm font-medium rounded-md transition-all">
                      {{ freq }}
                    </button>
                  </div>
                </div>

                <div>
                   <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Dificultat</label>
                   <div class="flex gap-2">
                     <button v-for="diff in dificultats" :key="diff" @click="formulari.dificultat = diff" :class="{'bg-green-600 text-white': formulari.dificultat === diff, 'bg-gray-100 text-gray-600 hover:bg-gray-200': formulari.dificultat !== diff}" class="flex-1 py-2 text-xs font-bold rounded-lg transition-colors capitalize">
                       {{ diff }}
                     </button>
                   </div>
                </div>

                <div>
                   <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Dies Objectiu (En {{ formulari.frequenciaTipus }})</label>
                   <div class="flex justify-between">
                     <button v-for="(dia, index) in dies" :key="dia" @click="alternarDia(index)" :class="{'bg-green-600 text-white': formulari.diesSeleccionats.indexOf(index) !== -1, 'bg-gray-200 text-gray-600 hover:bg-gray-300': formulari.diesSeleccionats.indexOf(index) === -1}" class="w-8 h-8 rounded-full text-xs font-bold flex items-center justify-center transition-colors">
                       {{ dia }}
                     </button>
                   </div>
                </div>

                <div>
                  <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Recordatori</label>
                  <input v-model="formulari.recordatori" type="time" class="bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-sm w-full focus:outline-none focus:ring-2 focus:ring-green-500" />
                </div>
              </div>
            </div>

            <!-- 4. Personalitzar -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
               <div class="flex items-center gap-3 mb-4">
                <div class="bg-purple-100 p-2 rounded-lg">
                  <span class="text-xl">🖌️</span>
                </div>
                <h2 class="text-lg font-bold text-gray-800">Personalitzar</h2>
              </div>

              <p class="text-sm text-gray-500 mb-4">Tria l'estil visual de la teva targeta d'hàbit.</p>
              
              <div class="flex gap-3 mb-6">
                <button v-for="color in colors" :key="color" @click="formulari.color = color" :style="{ backgroundColor: color }" class="w-10 h-10 rounded-full transition-transform hover:scale-110 focus:outline-none ring-2 ring-offset-2" :class="{'ring-gray-400': formulari.color === color, 'ring-transparent': formulari.color !== color}"></button>
              </div>

              <!-- Preview Card -->
              <div class="bg-gray-50 rounded-xl p-4 flex items-center gap-4 opacity-75">
                <div :style="{ backgroundColor: formulari.color || '#10B981', color: 'white' }" class="w-10 h-10 rounded-lg flex items-center justify-center text-lg">
                   {{ formulari.icona || '📝' }}
                </div>
                <div class="h-2 bg-gray-200 rounded w-2/3"></div>
              </div>
            </div>
          </div>

          <!-- Submit Button -->
          <button @click="crearHabit" class="w-full bg-green-700 hover:bg-green-800 text-white font-bold py-4 rounded-xl shadow-lg shadow-green-700/20 transition-all transform active:scale-95 flex items-center justify-center gap-2">
            <span class="bg-white text-green-700 rounded-full w-5 h-5 flex items-center justify-center text-xs">✓</span>
            Crear Hàbit
          </button>

        </div>

        <!-- Columna 3: Llista dels meus Hàbits -->
        <!-- 5. Els meus hàbits -->
        <div class="lg:col-span-1">
          <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 h-full">
            <div class="flex items-center gap-3 mb-6">
              <span class="text-xl text-gray-400">📋</span>
              <h2 class="text-lg font-bold text-gray-800">Els meus Hàbits</h2>
            </div>
            
            <div v-if="magatzemHabits.habits?.length === 0" class="text-center py-10 text-gray-400">
              <p>Encara no tens hàbits.</p>
              <p class="text-sm">¡Afegeix-ne un de nou!</p>
            </div>

            <div v-else class="space-y-4">
              <div v-for="habit in magatzemHabits.habits" :key="habit.id" class="flex items-center gap-4 p-4 rounded-xl bg-gray-50 hover:bg-white hover:shadow-md transition-all border border-transparent hover:border-gray-100 group">
                <div :style="{ backgroundColor: habit.color || '#10B981' }" class="w-12 h-12 rounded-full flex items-center justify-center text-xl text-white shadow-sm">
                   {{ habit.icon }}
                </div>
                <div class="flex-1 min-w-0">
                  <h3 class="font-bold text-gray-800 truncate">{{ habit.titol }}</h3>
                  <p class="text-xs text-gray-500">{{ habit.frequencia_tipus }} <span v-if="habit.reminder">• {{ habit.reminder }}</span> <span v-if="habit.dificultat">• {{ habit.dificultat }}</span></p>
                </div>
                <!-- Delete Button -->
                <button @click="eliminarHabit(habit.id)" class="opacity-0 group-hover:opacity-100 p-2 text-gray-400 hover:text-red-500 transition-all focus:outline-none" title="Esborrar hàbit">
                  <span class="text-lg">Esborra</span>
                </button>
                <div class="w-2 h-2 rounded-full bg-green-500 group-hover:hidden"></div>
              </div>
            </div>

            <div class="mt-6 pt-6 border-t border-gray-100 text-center">
              <button class="text-sm text-gray-400 hover:text-green-600 transition-colors border-dashed border border-gray-300 rounded-full px-4 py-2 w-full">
                + Hàbit ràpid
              </button>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script>
import { useHabitStore } from '../stores/useHabitStore';

export default {
  /**
   * Configuració inicial del component (Vue 3 Bridge).
   */
  setup: function() {
    // A. Inicialitzar el magatzem d'hàbits
    var magatzemHabits = useHabitStore();
    
    // B. Activar el seguiment per sockets
    magatzemHabits.initSocketListeners();
    
    return { magatzemHabits: magatzemHabits };
  },

  /**
   * Defineix les dades reactives del component.
   */
  data: function() {
    return {
      formulari: {
        titol: '',
        motivacio: '',
        icona: '💧',
        categoria: '',
        frequenciaTipus: 'Diari',
        dificultat: 'Fàcil',
        recordatori: '08:00',
        diesSeleccionats: [0, 1, 2, 3, 4], // Dilluns a Divendres per defecte
        color: '#10B981'
      },
      icones: ['💧', '📖', '🏃', '🧘', '🚭', '🥗', '💊', '💤'],
      categories: [
        { id: 'salut', name: 'Salut', icon: '❤️' },
        { id: 'estudi', name: 'Estudi', icon: '📚' },
        { id: 'treball', name: 'Treball', icon: '💼' },
        { id: 'art', name: 'Art', icon: '🎨' }
      ],
      frequencies: ['Diari', 'Setmanal', 'Mensual'],
      dificultats: ['Fàcil', 'Mitjà', 'Difícil'],
      dies: ['L', 'M', 'X', 'J', 'V', 'S', 'D'],
      colors: ['#65A30D', '#3B82F6', '#A855F7', '#F97316', '#EC4899']
    };
  },

  methods: {
    /**
     * Selecciona una icona per a l'hàbit.
     * @param {string} icona - La icona triada.
     */
    seleccionarIcona: function(icona) {
      this.formulari.icona = icona;
    },

    /**
     * Selecciona una categoria per a l'hàbit.
     * @param {string} id - L'identificador de la categoria.
     */
    seleccionarCategoria: function(id) {
      this.formulari.categoria = id;
    },

    /**
     * Afegeix o treu un dia de la selecció.
     * @param {number} index - L'índex del dia.
     */
    alternarDia: function(index) {
      var self = this;
      var posicio = self.formulari.diesSeleccionats.indexOf(index);
      
      if (posicio === -1) {
        self.formulari.diesSeleccionats.push(index);
      } else {
        self.formulari.diesSeleccionats.splice(posicio, 1);
      }
    },

    /**
     * Crea un nou hàbit i el desa al magatzem.
     */
    crearHabit: function() {
      var self = this;
      var nouHabit;

      // A. Validació bàsica
      if (!self.formulari.titol) {
        alert('Per favor, introdueix un nom per a l\'hàbit.');
        return;
      }
      if (!self.formulari.categoria) {
        alert('Per favor, selecciona una categoria.');
        return;
      }

      // B. Preparar l'objecte de l'hàbit
      nouHabit = {
        titol: self.formulari.titol,
        motivation: self.formulari.motivacio,
        icon: self.formulari.icona,
        category: self.formulari.categoria,
        frequencia_tipus: self.formulari.frequenciaTipus,
        dificultat: self.formulari.dificultat,
        reminder: self.formulari.recordatori,
        dies_setmana: self.formulari.diesSeleccionats.join(','),
        color: self.formulari.color,
        createdAt: new Date()
      };

      // C. Cridar a l'acció del magatzem
      self.magatzemHabits.addHabit(nouHabit);

      // D. Reiniciar el formulari
      self.reiniciarFormulari();
    },

    /**
     * Elimina un hàbit existent.
     * @param {number} idHabit - L'ID de l'hàbit a eliminar.
     */
    eliminarHabit: function(idHabit) {
      var self = this;
      if (confirm('Estàs segur que vols esborrar aquest hàbit?')) {
        self.magatzemHabits.deleteHabit(idHabit);
      }
    },

    /**
     * Reinicia els camps del formulari als valors per defecte.
     */
    reiniciarFormulari: function() {
      var f = this.formulari;
      f.titol = '';
      f.motivacio = '';
      f.icona = '💧';
      f.categoria = '';
      f.frequenciaTipus = 'Diari';
      f.dificultat = 'Fàcil';
      f.recordatori = '08:00';
    }
  }
};
</script>

import { defineStore } from 'pinia'
import { ref } from 'vue'
const TIMEOUT_MS = 5000
const XP_BASE = 10
const XP_PER_DIFICULTAT = {
  facil: 100,
  media: 250,
  dificil: 400
}

export const useGameStore = defineStore('game', () => {
  const userId = ref(null)
  const racha = ref(0)
  const xpTotal = ref(0)
  const nivel = ref(1)
  const habitos = ref([])

  const completHabit = async (habitId, socket) => {
    if (!socket) throw new Error('Socket no disponible')

    const habit = habitos.value.find(h => h.id === habitId)
    if (!habit) return false

    return new Promise((resolve) => {
      const handleResponse = (response) => {
        socket.off('update_xp', handleResponse)
        clearTimeout(timeoutId)

        // Validar respuesta del backend
        if (response && response.xp_total !== undefined && response.ratxa_actual !== undefined) {
          // Actualizar con datos reales del backend
          xpTotal.value = response.xp_total
          racha.value = response.ratxa_actual
          habit.completado = true

          // Refrescar estado desde backend (fuente de verdad)
          fetchGameState()
            .then(function () {
              resolve(true)
            })
            .catch(function () {
              resolve(true)
            })
        } else {
          resolve(false)
        }
      }

      const timeoutId = setTimeout(() => {
        socket.off('update_xp', handleResponse)
        resolve(false)
      }, TIMEOUT_MS)

      // Escuchar respuesta del backend
      socket.on('update_xp', handleResponse)

      // Emitir evento al backend
      socket.emit('habit_completed', {
        user_id: userId.value,
        habit_id: habitId,
        data: new Date().toISOString()
      })
    })
  }

  const updateRacha = (newRacha) => {
    racha.value = newRacha
  }

  const updateXP = (xp) => {
    xpTotal.value = xp
  }

  const setUserId = (id) => {
    userId.value = id
  }

  const setNivel = (newNivel) => {
    nivel.value = newNivel
  }

  const fetchHabitos = async () => {
    try {
      const response = await fetch('http://localhost:8000/api/habits')
      if (!response.ok) {
        throw new Error(`Error al obtener hábitos: ${response.status}`)
      }
      const rawData = await response.json()
      
      console.log('📥 Datos crudos de la API:', rawData)
      
      // Laravel Resources devuelve { data: [...] }
      const habitosDelApi = Array.isArray(rawData) ? rawData : (rawData.data || [])
      
      console.log('📋 Hábitos extraídos:', habitosDelApi)
      
      habitos.value = habitosDelApi.map(habit => ({
        id: habit.id,
        nombre: habit.titol || 'Sin nombre',
        descripcion: `${habit.frequencia_tipus} - Dificultad: ${habit.dificultat}` || '',
        completado: false,
        xpReward: XP_PER_DIFICULTAT[habit.dificultat] || XP_BASE,
        usuari_id: habit.usuari_id,
        plantilla_id: habit.plantilla_id,
        dificultat: habit.dificultat,
        frequencia_tipus: habit.frequencia_tipus,
        dies_setmana: habit.dies_setmana,
        objectiu_vegades: habit.objectiu_vegades
      }))
      
      console.log('✅ Hábitos transformados:', habitos.value)
      return habitos.value
    } catch (error) {
      console.error('❌ Error fetching habitos:', error)
      habitos.value = []
      return []
    }
  }

  const fetchGameState = async () => {
    try {
      const response = await fetch('http://localhost:8000/api/game-state')
      if (!response.ok) {
        throw new Error(`Error al obtener game-state: ${response.status}`)
      }
      const data = await response.json()

      if (data && data.xp_total !== undefined) {
        xpTotal.value = data.xp_total
      }
      if (data && data.ratxa_actual !== undefined) {
        racha.value = data.ratxa_actual
      }
      return data
    } catch (error) {
      console.error('❌ Error fetching game-state:', error)
      return null
    }
  }

  return {
    userId,
    racha,
    xpTotal,
    nivel,
    habitos,
    completHabit,
    updateRacha,
    updateXP,
    setUserId,
    setNivel,
    fetchHabitos,
    fetchGameState
  }
})

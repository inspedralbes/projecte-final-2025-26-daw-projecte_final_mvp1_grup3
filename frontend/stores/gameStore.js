import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useRollback } from '~/composables/useRollback'

const TIMEOUT_MS = 5000
const XP_BASE = 10

export const useGameStore = defineStore('game', () => {
  const userId = ref(null)
  const racha = ref(0)
  const xpTotal = ref(0)
  const nivel = ref(1)
  const habitos = ref([])

  const { createSnapshot, commitSnapshot, rollbackStack } = useRollback()

  const _restoreState = (snapshotId) => {
    const snapshotData = rollbackStack.value[snapshotId]
    if (!snapshotData) return

    racha.value = snapshotData.snapshot.racha
    xpTotal.value = snapshotData.snapshot.xpTotal
    habitos.value = JSON.parse(JSON.stringify(snapshotData.snapshot.habitos))
  }

  const completHabit = async (habitId, socket) => {
    if (!socket) throw new Error('Socket no disponible')

    const habit = habitos.value.find(h => h.id === habitId)
    if (!habit) return false

    // Snapshot antes de cambios
    const snapshotId = createSnapshot({
      racha: racha.value,
      xpTotal: xpTotal.value,
      habitos: habitos.value.map(h => ({ ...h }))
    })

    // Mutación inmediata
    habit.completado = true
    xpTotal.value += habit.xpReward
    racha.value += 1

    return new Promise((resolve) => {
      const handleResponse = (response) => {
        socket.off('habit_completed_response', handleResponse)
        clearTimeout(timeoutId)

        // Validar respuesta del backend
        if (response && response.xp !== undefined && response.racha !== undefined) {
          // Actualizar con datos reales del backend
          xpTotal.value = response.xp
          racha.value = response.racha
          commitSnapshot(snapshotId)
          resolve(true)
        } else {
          // Error en respuesta, hacer rollback
          _restoreState(snapshotId)
          commitSnapshot(snapshotId)
          resolve(false)
        }
      }

      const timeoutId = setTimeout(() => {
        socket.off('habit_completed_response', handleResponse)
        // Timeout, hacer rollback
        _restoreState(snapshotId)
        commitSnapshot(snapshotId)
        resolve(false)
      }, TIMEOUT_MS)

      // Escuchar respuesta del backend
      socket.on('habit_completed_response', handleResponse)

      // Emitir evento al backend
      socket.emit('habit_completed', {
        userId: userId.value,
        habitId: habitId,
        timestamp: new Date().toISOString()
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
        xpReward: XP_BASE,
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
    fetchHabitos
  }
})

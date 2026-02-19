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
  const habitos = ref([
    { id: 1, nombre: 'Hábito Ejemplo', descripcion: 'Descripción del hábito', completado: false, xpReward: XP_BASE }
  ])

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
    setNivel
  }
})

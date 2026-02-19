import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useRollback } from '~/composables/useRollback'

/**
 * @typedef {Object} Habit
 * @property {number} id - ID del hábito
 * @property {string} nombre - Nombre del hábito
 * @property {string} descripcion - Descripción
 * @property {boolean} completado - Si está completado
 * @property {number} xpReward - XP que da completar
 */

/**
 * @typedef {Object} UserState
 * @property {number | null} userId - ID del usuario
 * @property {number} racha - Racha actual
 * @property {number} xpTotal - XP total del usuario
 * @property {number} nivel - Nivel del usuario
 * @property {Habit[]} habitos - Lista de hábitos
 */

export const useGameStore = defineStore('game', () => {
  // Estado reactivo
  const userId = ref(null)
  const racha = ref(0)
  const xpTotal = ref(0)
  const nivel = ref(1)
  const habitos = ref([
    {
      id: 1,
      nombre: 'Hábito Ejemplo',
      descripcion: 'Descripción del hábito',
      completado: false,
      xpReward: 10
    }
  ])

  // Composable de rollback
  const { createSnapshot, rollback, commitSnapshot, rollbackStack } = useRollback()

  /**
   * Completa un hábito con snapshot y rollback
   * Emite evento al backend
   * @param {number} habitId - ID del hábito a completar
   * @param {*} socket - Socket.io del cliente
   * @returns {Promise<boolean>} Si se completó exitosamente
   */
  const completHabit = async (habitId, socket) => {
    try {
      // 1. SNAPSHOT: Guardar estado antes de la mutación
      const snapshotId = createSnapshot({
        racha: racha.value,
        xpTotal: xpTotal.value,
        habitos: habitos.value.map(h => ({ ...h }))
      })

      // Encontrar el hábito
      const habit = habitos.value.find(h => h.id === habitId)
      if (!habit) {
        console.error('Hábito no encontrado')
        return false
      }

      // 2. MUTACIÓN INMEDIATA: Cambiar estado visual sin esperar al backend
      console.log('⚡ Actualizando estado visual inmediatamente')
      habit.completado = true
      xpTotal.value += habit.xpReward
      racha.value += 1

      // 3. EMIT: Enviar datos al backend
      if (!socket) {
        throw new Error('Socket no disponible')
      }

      return new Promise((resolve) => {
        // Escuchar respuesta del backend (con timeout)
        const responseHandler = (response) => {
          socket.off('habit_completed_response', responseHandler)
          clearTimeout(timeoutId)

          if (response.success) {
            console.log('✅ Backend confirmó el cambio')
            // Confirmar snapshot (removerlo del stack)
            commitSnapshot(snapshotId)
            resolve(true)
          } else {
            console.error('❌ Backend rechazó el cambio:', response.error)
            // ROLLBACK: Restaurar estado desde snapshot
            const snapshotData = rollbackStack.value[snapshotId]
            if (snapshotData) {
              racha.value = snapshotData.snapshot.racha
              xpTotal.value = snapshotData.snapshot.xpTotal
              habitos.value = JSON.parse(JSON.stringify(snapshotData.snapshot.habitos))
              console.log('✅ Estado restaurado:', { racha: racha.value, xpTotal: xpTotal.value })
            }
            commitSnapshot(snapshotId)
            resolve(false)
          }
        }

        const timeoutId = setTimeout(() => {
          socket.off('habit_completed_response', responseHandler)
          console.warn('⏱️ Timeout - Backend no respondió a tiempo')
          // Rollback automático después de timeout
          const snapshotData = rollbackStack.value[snapshotId]
          if (snapshotData) {
            racha.value = snapshotData.snapshot.racha
            xpTotal.value = snapshotData.snapshot.xpTotal
            habitos.value = JSON.parse(JSON.stringify(snapshotData.snapshot.habitos))
            console.log('✅ Estado restaurado por timeout:', { racha: racha.value, xpTotal: xpTotal.value })
          }
          commitSnapshot(snapshotId)
          resolve(false)
        }, 5000) // 5 segundos de timeout

        socket.on('habit_completed_response', responseHandler)

        // Emitir evento al backend
        socket.emit('habit_completed', {
          userId: userId.value,
          habitId: habitId,
          timestamp: new Date().toISOString()
        })
      })
    } catch (error) {
      console.error('Error en completHabit:', error)
      return false
    }
  }

  /**
   * Actualiza la racha desde el backend
   * @param {number} newRacha - Nueva racha
   */
  const updateRacha = (newRacha) => {
    racha.value = newRacha
  }

  /**
   * Actualiza XP desde el backend
   * @param {number} xp - XP total
   */
  const updateXP = (xp) => {
    xpTotal.value = xp
  }

  /**
   * Establece el userId
   * @param {number} id - ID del usuario
   */
  const setUserId = (id) => {
    userId.value = id
  }

  /**
   * Actualiza el nivel
   * @param {number} newNivel - Nuevo nivel
   */
  const setNivel = (newNivel) => {
    nivel.value = newNivel
  }

  return {
    // Estado
    userId,
    racha,
    xpTotal,
    nivel,
    habitos,

    // Acciones
    completHabit,
    updateRacha,
    updateXP,
    setUserId,
    setNivel
  }
})

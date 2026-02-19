/**
 * Composable para manejar snapshots y rollback de estado
 * Implementa patrón de "Optimistic Updates" con garantía de 0ms latencia visual
 */

import { ref } from 'vue'

/**
 * @typedef {Object} Snapshot
 * @property {*} [key] - Propiedades dinámicas
 */

/**
 * @typedef {Object} RollbackState
 * @property {Snapshot} snapshot - Datos del snapshot
 * @property {number} timestamp - Marca de tiempo
 * @property {boolean} isRollingBack - Si está en proceso de rollback
 */

export function useRollback() {
  // Stack de snapshots para múltiples operaciones concurrentes
  const rollbackStack = ref([])

  /**
   * Crea un snapshot del estado actual
   * @param {Snapshot} state - Objeto con el estado a guardar
   * @returns {number} ID del snapshot para identificarlo posteriormente
   */
  const createSnapshot = (state) => {
    const snapshot = {
      snapshot: JSON.parse(JSON.stringify(state)), // Deep clone
      timestamp: Date.now(),
      isRollingBack: false
    }
    rollbackStack.value.push(snapshot)
    console.log('📸 Snapshot creado:', { id: rollbackStack.value.length - 1, data: snapshot.snapshot })
    return rollbackStack.value.length - 1
  }

  /**
   * Restaura el estado desde un snapshot
   * @param {number} snapshotId - ID del snapshot a restaurar
   * @param {Snapshot} currentState - Estado actual para actualizar
   * @param {string} [errorMessage] - Mensaje de error opcional
   */
  const rollback = (snapshotId, currentState, errorMessage) => {
    if (snapshotId < 0 || snapshotId >= rollbackStack.value.length) {
      console.error('❌ ID de snapshot inválido:', snapshotId)
      return
    }

    const rollbackState = rollbackStack.value[snapshotId]
    rollbackState.isRollingBack = true

    console.warn('⏮️  Rollback iniciado:', {
      snapshotId,
      originalData: rollbackState.snapshot,
      error: errorMessage
    })

    // Restaurar cada propiedad del snapshot
    Object.keys(rollbackState.snapshot).forEach((key) => {
      currentState[key] = JSON.parse(JSON.stringify(rollbackState.snapshot[key]))
    })

    console.log('✅ Estado restaurado:', currentState)
  }

  /**
   * Limpia un snapshot del stack después de confirmar éxito
   * @param {number} snapshotId - ID del snapshot a remover
   */
  const commitSnapshot = (snapshotId) => {
    if (snapshotId >= 0 && snapshotId < rollbackStack.value.length) {
      rollbackStack.value.splice(snapshotId, 1)
      console.log('✓ Snapshot confirmado y removido del stack')
    }
  }

  /**
   * Limpia todos los snapshots
   */
  const clearSnapshots = () => {
    rollbackStack.value = []
    console.log('🗑️  Todos los snapshots removidos')
  }

  /**
   * Obtiene información del snapshot
   * @param {number} snapshotId - ID del snapshot
   * @returns {RollbackState | null} Información del snapshot o null
   */
  const getSnapshotInfo = (snapshotId) => {
    return snapshotId >= 0 && snapshotId < rollbackStack.value.length
      ? rollbackStack.value[snapshotId]
      : null
  }

  /**
   * Retorna el estado del stack
   * @returns {number} Tamaño del stack
   */
  const getStackSize = () => {
    return rollbackStack.value.length
  }

  return {
    createSnapshot,
    rollback,
    commitSnapshot,
    clearSnapshots,
    getSnapshotInfo,
    getStackSize,
    rollbackStack
  }
}

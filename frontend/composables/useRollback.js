/**
 * Composable para manejar snapshots y rollback de estado
 * Implementa patrón de Optimistic Updates con garantía de 0ms latencia visual
 */

import { ref } from 'vue'

export function useRollback() {
  const rollbackStack = ref([])

  const createSnapshot = (state) => {
    const snapshot = {
      snapshot: JSON.parse(JSON.stringify(state)),
      timestamp: Date.now()
    }
    rollbackStack.value.push(snapshot)
    return rollbackStack.value.length - 1
  }

  const rollback = (snapshotId, currentState) => {
    const snapshotData = rollbackStack.value[snapshotId]
    if (!snapshotData) return

    Object.keys(snapshotData.snapshot).forEach((key) => {
      currentState[key] = JSON.parse(JSON.stringify(snapshotData.snapshot[key]))
    })
  }

  const commitSnapshot = (snapshotId) => {
    if (snapshotId >= 0 && snapshotId < rollbackStack.value.length) {
      rollbackStack.value.splice(snapshotId, 1)
    }
  }

  const clearSnapshots = () => {
    rollbackStack.value = []
  }

  const getSnapshot = (snapshotId) => {
    return rollbackStack.value[snapshotId] || null
  }

  return {
    createSnapshot,
    rollback,
    commitSnapshot,
    clearSnapshots,
    getSnapshot,
    rollbackStack
  }
}

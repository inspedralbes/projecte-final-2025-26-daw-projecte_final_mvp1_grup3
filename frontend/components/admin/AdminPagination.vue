<template>
  <div class="flex items-center justify-between py-4" v-if="totalPages > 1">
    <button
      type="button"
      :disabled="currentPage <= 1"
      @click="$emit('change', currentPage - 1)"
      class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-semibold disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
    >
      Anterior
    </button>
    <span class="text-sm text-gray-600">
      Pàgina {{ currentPage }} de {{ totalPages }}
    </span>
    <button
      type="button"
      :disabled="currentPage >= totalPages"
      @click="$emit('change', currentPage + 1)"
      class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-semibold disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
    >
      Següent
    </button>
  </div>
</template>

<script>
export default {
  name: 'AdminPagination',
  props: {
    currentPage: { type: Number, required: true },
    totalPages: { type: Number, required: true }
  }
};
</script>

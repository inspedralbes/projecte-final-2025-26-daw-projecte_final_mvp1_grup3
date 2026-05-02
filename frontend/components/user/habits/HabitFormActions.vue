<template>
  <button
    type="button"
    @click="$emit('submit')"
    class="w-full bg-green-700 hover:bg-green-800 text-white font-bold py-4 rounded-xl shadow-lg shadow-green-700/20 transition-all transform active:scale-95 flex items-center justify-center gap-2"
  >
    <span class="bg-white text-green-700 rounded-full w-5 h-5 flex items-center justify-center text-xs">V</span>
    {{ $t('habits.create_button') }}
  </button>
</template>

<script>
export default { name: 'HabitFormActions' };
</script>

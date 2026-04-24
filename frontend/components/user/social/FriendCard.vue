<template>
  <div
    class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex items-center justify-between hover:shadow-md transition-shadow cursor-pointer"
    @click="$emit('view-profile', friend.friend)"
  >
    <div class="flex items-center gap-3">
      <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
        <span class="text-blue-600 font-bold text-lg">{{ friend.friend.nom.charAt(0) }}</span>
      </div>
      <div>
        <p class="font-semibold text-gray-800">{{ friend.friend.nom }}</p>
        <p class="text-xs text-gray-500">Nivell {{ friend.friend.nivell }} · {{ friend.friend.xp_total }} XP</p>
      </div>
    </div>
    <button
      @click.stop="$emit('open-chat', friend.friend)"
      class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-full transition-all"
      :title="$t('friends.chat')"
    >
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.874 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
      </svg>
    </button>
  </div>
</template>

<script>
export default {
  name: "FriendCard",
  props: {
    friend: {
      type: Object,
      required: true,
    },
  },
  emits: ["open-chat", "view-profile"],
};
</script>
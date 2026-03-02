<script setup>
import { ref, computed } from 'vue'
const { locale, locales, setLocale } = useI18n()

const availableLocales = computed(() => {
  return locales.value.filter(i => i.code !== locale.value)
})

const currentLocaleName = computed(() => {
  const current = locales.value.find(i => i.code === locale.value)
  return current ? current.name : locale.value
})

const isDropdownOpen = ref(false)

const toggleDropdown = () => {
  isDropdownOpen.value = !isDropdownOpen.value
}

const changeLanguage = (newLocale) => {
  setLocale(newLocale)
  isDropdownOpen.value = false
}
</script>

<template>
  <div class="relative inline-block text-left">
    <div>
      <button 
        type="button" 
        @click="toggleDropdown"
        class="inline-flex justify-center w-full rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
        id="language-menu-button" 
        aria-expanded="true" 
        aria-haspopup="true"
      >
        {{ currentLocaleName }}
        <svg class="-mr-1 ml-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
          <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
        </svg>
      </button>
    </div>

    <div 
      v-if="isDropdownOpen"
      class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-50" 
      role="menu" 
      aria-orientation="vertical" 
      aria-labelledby="language-menu-button" 
      tabindex="-1"
    >
      <div class="py-1" role="none">
        <a 
          v-for="l in availableLocales" 
          :key="l.code"
          href="#" 
          @click.prevent="changeLanguage(l.code)"
          class="text-gray-700 block px-4 py-2 text-sm hover:bg-gray-100" 
          role="menuitem" 
          tabindex="-1"
        >
          {{ l.name }}
        </a>
      </div>
    </div>
  </div>
</template>

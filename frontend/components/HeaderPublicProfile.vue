<!--
  Component o pagina Nuxt: HeaderPublicProfile.
  Comentaris de codi: agents/frontend/AgentNuxt.md + AgentJavascript.md
-->
<template>
  <header class="w-full p-3">
    <!-- Mòbil -->
    <nav class="w-full lg:hidden">
      <div class="flex w-full items-center gap-1 px-1 min-h-[2.75rem]">
        <div class="flex shrink-0 w-10 justify-start">
          <button class="hamburger-btn" @click="goBack" aria-label="Tornar">
            <svg width="24" height="24" viewBox="0 0 73 73" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M42.5834 54.75L24.3334 36.5L42.5834 18.25L46.8417 22.5083L32.85 36.5L46.8417 50.4917L42.5834 54.75Z" fill="#FAF9F9" />
            </svg>
          </button>
        </div>
        <div class="flex flex-1 justify-center min-w-0">
          <!-- Buit per complir "només el perfil del usuari sencer" -->
        </div>
        <div class="shrink-0 min-w-[9rem] max-w-[12.5rem]">
          <!-- Buit per no mostrar el clima -->
        </div>
      </div>
    </nav>

    <!-- Escriptori -->
    <nav class="hidden lg:flex w-full items-center px-4 nav-bar-desktop min-h-[2.75rem]">
      <div class="flex-1 min-w-0 flex justify-start">
        <button class="hamburger-btn hamburger-btn-desktop" @click="goBack" aria-label="Tornar">
          <svg width="24" height="24" viewBox="0 0 73 73" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M42.5834 54.75L24.3334 36.5L42.5834 18.25L46.8417 22.5083L32.85 36.5L46.8417 50.4917L42.5834 54.75Z" fill="#FAF9F9" />
          </svg>
        </button>
      </div>

      <!-- Desktop nav: buit per complir "només el perfil del usuari sencer" -->
      <div class="flex-1 min-w-0 flex justify-center"></div>

      <!-- Desktop actions: buit per complir "només el perfil del usuari sencer" -->
      <div class="flex-1 min-w-0 flex justify-end"></div>
    </nav>
  </header>
</template>

<script setup>
import { useRouter } from 'vue-router';

const router = useRouter();

function goBack() {
  if (window.history.state && window.history.state.back) {
    router.back();
  } else {
    router.push('/social');
  }
}
</script>

<style scoped>
header {
  width: 100%;
}
nav {
  width: 100%;
}

.hamburger-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  background: none;
  border: none;
  padding: 0.5rem;
  color: #faf9f9;
  cursor: pointer;
  border-radius: 0.375rem;
  transition: all 0.2s;
}

.hamburger-btn:hover {
  background-color: rgba(243, 244, 246, 0.25);
}
</style>

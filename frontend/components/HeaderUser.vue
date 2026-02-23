<template>
  <header class="w-full p-3">
    <nav class="w-full flex items-center justify-between px-4">
      
      <NuxtLink to="/HomePage" class="brand">
        <img :src="logo" alt="Loopy logo" class="logo-img"/>
        <span class="app-name">Loopy</span>
      </NuxtLink>

      <ul class="flex-1 flex items-center justify-center space-x-6 list-none m-0 p-0">
        <li>
          <NuxtLink to="/HomePage" class="nav-link">Home</NuxtLink>
        </li>
        <li>
          <NuxtLink to="/habits" class="nav-link">Crear</NuxtLink>
        </li>
        <li>
          <NuxtLink to="/Plantilles" class="nav-link">Catálogo</NuxtLink>
        </li>
        <li>
          <NuxtLink to="/foro" class="nav-link">Foro</NuxtLink>
        </li>
        <li>
          <NuxtLink to="/perfil" class="nav-link">Perfil</NuxtLink>
        </li>
      </ul>

    </nav>
  </header>
</template>

<script setup>
import logo from '~/assets/img/LogoLoopy.png'
</script>

<style scoped>
header {
  width: 100%;
}
nav {
  width: 100%;
}
nav ul {
  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: center;
  gap: 1.5rem;
  list-style: none;
  margin: 0;
  padding: 0;
}
nav ul li {
  margin: 0;
}
nav a {
  color: inherit;
  text-decoration: none;
}

.brand {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  color: inherit;
  text-decoration: none;
}
.brand:hover {
  color: #d1d5db; /* gray-300 */
}
.logo-img {
  width: 3rem;
  height: 3rem;
  border-radius: 50%;
  object-fit: cover;
  display: block;
}
.app-name {
  font-size: 1.25rem;
  font-weight: 700;
}
</style>
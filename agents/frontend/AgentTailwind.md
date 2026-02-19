# Agent de Tailwind (Estil i Disseny Bento)

Aquest document defineix les regles d'estil i disseny visual de l'aplicació. L'objectiu és crear una interfície moderna, neta i modular basada en el sistema **Bento Grid**.

## 1. Objectiu de l'Agent
Garantir la coherència visual, l'ús correcte de les classes d'utilitat i la implementació d'animacions suaus per a l'experiència d'usuari (UX).

## 2. Configuració Base
- **Framework**: Tailwind CSS 3.4.1.
- **Fitxer principal**: `assets/css/main.css` (on s'importen les directives `@tailwind`).
- **Config**: `tailwind.config.js` per a extensions de tema (colors personalitzats, fonts).

## 3. Sistema de Disseny: Bento Grid
La interfície s'ha de pensar com una graella de caixes (cel·les) independents.
- **Container**: Ús de `grid` i `gap` per separar les cel·les.
- **Cell (Targeta)**:
    - `bg-white` (o color de fons suau).
    - `rounded-xl` o `rounded-2xl` (bordes molt arrodonits).
    - `shadow-sm` o `shadow-md`.
    - `p-4` o `p-6` (padding generós).

## 4. Regles d'Ús de Classes
- **Directives al HTML**: S'ha d'escriure les classes directament als elements HTML (`<div class="...">`).
- **Evitar `@apply`**: No fer servir `@apply` en fitxers CSS a menys que sigui estrictament necessari per a components molt repetitius. Preferim la claredat explícita al HTML.
- **Ordre de Classes**:
    1. Layout (flex, grid, absolute).
    2. Espaiat (m, p).
    3. Mida (w, h).
    4. Tipografia (text, font).
    5. Visuals (bg, border, rounded, shadow).
    6. Interacció (hover, transition).

## 5. Animacions i Transicions
Per donar sensació de fluïdesa (especialment en l'Optimistic UI):
- **Transicions base**: `transition-all duration-300 ease-in-out`.
- **Hover**: Efectes subtils al passar el ratolí (`hover:scale-105`, `hover:shadow-lg`).
- **Feedback visual**: Canvis de color instantanis quan es clica un botó (`active:scale-95`).

## 6. Exemple de Component Bento
```html
<template>
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6 p-4">
    <!-- Cella Gran -->
    <div class="col-span-1 md:col-span-2 bg-white rounded-2xl shadow-md p-6 transition-all hover:shadow-lg">
      <h2 class="text-2xl font-bold text-gray-800 mb-4">El meu Progrés</h2>
      <!-- Contingut -->
    </div>

    <!-- Cella Petita -->
    <div class="bg-blue-50 rounded-2xl shadow-sm p-6 flex flex-col items-center justify-center">
      <span class="text-4xl font-black text-blue-600">350 XP</span>
      <span class="text-sm text-blue-400 mt-2">Nivell Actual</span>
    </div>
  </div>
</template>
```

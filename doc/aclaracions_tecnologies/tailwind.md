# Tailwind CSS — Aclaracions tècniques (Loopy)

> Sistema d'estils utilitari al frontend Nuxt.

---

## 1. Què és i per què l'hem triat

| Aspecte | Detall |
| :--- | :--- |
| **Framework CSS** | Tailwind CSS 3 |
| **Integració** | PostCSS al `nuxt.config.ts` |
| **Fitxer base** | `frontend/assets/css/main.css` |

**Motius:** Desenvolupament ràpid d'UI responsive, coherència visual (espaiat, colors), menys CSS custom dispers.

---

## 2. Model d'ús al projecte

### Utility-first

Classes al template Vue:

```html
<div class="bento-card rounded-[10px] p-8 bg-white/95 backdrop-blur-md">
```

### Components semàntics propis

Algunes classes custom a `main.css` o `<style scoped>`:

- `bento-card`, `template-card`, `perfil-stat-pill`
- `moment-divider`, `friends-paginator`

**Patró:** Tailwind per layout/espaiat; classes de component per identitat visual Loopy (gamificació, colors marca).

---

## 3. Estructura visual per domini

| Àrea | Estil característic |
| :--- | :--- |
| Home / mascota | Cards tipus "bento", ombres, fons amb imatges `assets/img/Fons/` |
| Hàbits / plantilles | `template-expand-*` per desplegables d'accions |
| Admin | Tipografia `font-bricolage`, botons uppercase compactes |
| Social | Avatars amb `friend-avatar-ring` |

---

## 4. Responsive

Breakpoints estàndard Tailwind (`sm:`, `lg:`):

- Mòbil: monstre a pantalla completa, navegació inferior
- Desktop: layout bento en graella (`lg:grid`, `hidden lg:flex`)

---

## 5. Relació amb altres tecnologies

| Tecnologia | Relació |
| :--- | :--- |
| **Vue/Nuxt** | Classes directament al `<template>` |
| **Pinia** | No afecta estils; només dades |
| **i18n** | Textos via `$t()`, no hardcoded a CSS |

---

## 6. Preguntes freqüents

**P: Per què no Bootstrap?**  
R: Tailwind ofereix més control granular sense sobreescriure estils de framework.

**P: On es defineixen colors de marca?**  
R: `tailwind.config` (si existeix) o valors arbitraris `rounded-[10px]`, colors hex inline per prototip Figma.

**P: Accessibilitat?**  
R: Ús de `sr-only` per labels, `aria-*` als components (ex. paginadors, modals).

---

## 7. Referències internes

- `frontend/nuxt.config.ts` (postcss)
- `agents/frontend/AgentTailwind.md`
- [nuxt.md](./nuxt.md)

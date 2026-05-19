# Sistema de Mascota Evolutiva - Diseño

## Visión General

La mascota evolutiva es un compañero digital que refleja el crecimiento personal del usuario en Loopy. Su ciclo de vida está ligado al nivel del usuario, creando un vínculo emocional que motiva el mantenimiento de hábitos. Las imágenes de los monstruos ya están disponibles en `/public/img/monster`.

## Nomenclatura de Sprites

**Formato MXY:**
- **M** = Monster (prefijo fijo)
- **X** = Color (V=Verde, R=Rosa, L=Lila, A=Amarillo)
- **Y** = Etapa (B=Bebé, N=Niño, A=Adolescente, M=Mamado)

**Ejemplos:**
- `MVB.png` → Monster Verde Bebé
- `MRN.png` → Monster Rosa Niño
- `MLA.png` → Monster Lila Adolescente
- `MAM.png` → Monster Amarillo Mamado

## Tipos de Monstruo (Colores)

| Código | Color |
|--------|-------|
| V | Verde |
| R | Rosa |
| L | Lila |
| A | Amarillo |

## Etapas de Evolución

| Etapa | Código | Nivel |
|-------|--------|-------|
| Bebé | B | 1-5 |
| Niño | N | 6-15 |
| Adolescente | A | 16-30 |
| Mamado | M | 31+ |

## Fórmula de Nivel

```
nivel = floor(sqrt(xp_total / 100)) + 1
```

## Fórmula de Etapa

```javascript
function getEtapa(nivel) {
  if (nivel <= 5) return 'B';      // Bebé
  if (nivel <= 15) return 'N';     // Niño
  if (nivel <= 30) return 'A';     // Adolescente
  return 'M';                        // Mamado
}

function getSpriteName(color, nivel) {
  const etapa = getEtapa(nivel);
  return `M${color}${etapa}.png`;
}

// Ejemplos:
getSpriteName('V', 3)   // → MVB.png
getSpriteName('R', 10)  // → MRN.png
getSpriteName('L', 20)  // → MLA.png
getSpriteName('A', 35)  // → MAM.png
```

## Estructura de Assets (ya existente)

```
/public/img/monster/
├── MVB.png  (Monster Verde Bebé)
├── MVN.png  (Monster Verde Niño)
├── MVA.png  (Monster Verde Adolescente)
├── MVM.png  (Monster Verde Mamado)
├── MRB.png  (Monster Rosa Bebé)
├── MRN.png  (Monster Rosa Niño)
├── MRA.png  (Monster Rosa Adolescente)
├── MRM.png  (Monster Rosa Mamado)
├── MLB.png  (Monster Lila Bebé)
├── MLN.png  (Monster Lila Niño)
├── MLA.png  (Monster Lila Adolescente)
├── MLM.png  (Monster Lila Mamado)
├── MAB.png  (Monster Amarillo Bebé)
├── MAN.png  (Monster Amarillo Niño)
├── MAA.png  (Monster Amarillo Adolescente)
├── MAM.png  (Monster Amarillo Mamado)
├── huevo_V.png
├── huevo_R.png
├── huevo_L.png
└── huevo_A.png
```

## Flujo de Selección (Post-Onboarding)

1. Usuario completa onboarding con IA
2. Se muestra MonsterEggSelector.vue con 4 huevos (verde, rosa, lila, amarillo)
3. Usuario selecciona un huevo
4. Se llama POST /api/user/monster-choice con monstre_tipus (VV, VR, VL, VA)
5. Se guarda monstre_tipus y data_naixement_monstre
6. Se redirige a home con animación de eclosión
7. Se muestra el sprite inicial MVB (Monster Verde Bebé)

## Flujo de Evolución

1. Usuario completa acción que otorga XP
2. Backend recalcula nivel
3. Se determina nueva etapa según umbrales (5, 15, 30)
4. Se emite evento xp_updated por WebSocket con datos de evolución
5. Frontend detecta cambio de etapa
6. Se muestra EvolutionModal.vue con animación celebratoria
7. Se actualiza MonsterDisplay.vue con nuevo sprite (ej: MVB → MVN)

## Componentes UI (Claymorphism)

### MonsterEggSelector.vue
- 4 huevos interactivos con hover effect
- Animación de balanceo al seleccionar
- Preview del sprite que resultará
- Transición a eclosión al confirmar

### MonsterDisplay.vue
- Props: tipo (VV/VR/VL/VA), nivel
- Renderizado de sprite según nomenclatura MXY
- Tooltip con etapa actual ("Nivel 8 - Niño")
- Botón de skins en etapa Mamado

### EvolutionModal.vue
- Overlay oscuro con animación central
- Sprite del monstruo creciendo
- Partículas/estrellas de celebración
- Texto de nueva etapa ("¡Tu monstruo ahora es Niño!")
- Botón "¡Genial!" para cerrar

## Modelo de Datos

```sql
-- Extension tabla USUARIS
ALTER TABLE USUARIS ADD COLUMN monstre_tipus VARCHAR(2) DEFAULT NULL;  -- 'VV', 'VR', 'VL', 'VA'
ALTER TABLE USUARIS ADD COLUMN data_naixement_monstre TIMESTAMP DEFAULT NULL;
```

## API Endpoints

### POST /api/user/monster-choice
```json
Request:
{
  "monstre_tipus": "VV"
}

Response:
{
  "success": true,
  "monstre": {
    "tipus": "VV",
    "etapa": "B",
    "nivell": 1,
    "sprite": "MVB"
  }
}
```

### GET /api/user/{id}/profile (actualizado)
```json
Response:
{
  "id": 123,
  "nom": "Usuario",
  "nivell": 8,
  "monstre_tipus": "VV",
  "monstre_etapa": "N",
  "monstre_sprite": "MVN"
}
```

## WebSocket Events

### xp_updated
```json
{
  "event": "xp_updated",
  "user_id": 123,
  "xp_total": 2500,
  "nivel_anterior": 4,
  "nivel_actual": 5,
  "etapa_anterior": "B",
  "etapa_actual": "N",
  "is_evolution": true
}
```

## Animaciones CSS

### Selección de Huevo
```css
.egg:hover {
  transform: scale(1.05) rotate(-5deg);
  filter: brightness(1.1);
  box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.egg.selected {
  animation: egg-wobble 0.5s ease-in-out infinite;
}
```

### Evolución
```css
@keyframes evolve-glow {
  0% { transform: scale(1); filter: brightness(1); }
  50% { transform: scale(1.5); filter: brightness(1.5) drop-shadow(0 0 20px gold); }
  100% { transform: scale(1); filter: brightness(1); }
}
```
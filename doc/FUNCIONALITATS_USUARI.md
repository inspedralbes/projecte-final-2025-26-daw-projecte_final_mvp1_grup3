# Funcionalitats d'usuari actives (Loopy MVP1)

Document que llista les funcionalitats que un usuari autenticat pot utilitzar actualment a la pàgina, sense incloure les genèriques (login, registre, logout, canvi d'idioma).

---

## 1. Home (Dashboard)

| Funcionalitat | Descripció |
|---------------|------------|
| **Missió diària** | Visualitzar la missió del dia i el progrés (objectiu completat / total). |
| **Tarjeta de perfil** | Resum amb nivell actual, barra d'XP (xp_actual_nivel / xp_objetivo_nivel) i percentatge. |
| **Logros** | Veure els últims logros desbloquejats; obrir modal amb tots els logros del compte. |
| **Ruleta** | Secció per girar la ruleta; obre modal per fer la tirada (consum monedes, premi aleatori). |
| **Monstre / mascota** | Visualització del monstre amb nivell i context escenari. |
| **Ratxa (streak)** | Mostrar ratxa actual i ratxa màxima, XP total i monedes. |
| **Hàbits del dia** | Llista dels hàbits actius per avui amb estat de progrés. |
| **Progrés d'hàbits** | Clic a un hàbit → modal per incrementar/decrementar progrés fins a l'objectiu. |
| **Completar hàbit** | Confirmar finalització quan el progrés arriba al 100%; guanyar XP i monedes. |
| **Modal ratxa trencada** | Avis quan es perd la ratxa (dia sense completar hàbits). |
| **Actualització en temps real** | Feedback via Socket.io (XP, ratxa, progrés) sense recarregar. |

---

## 2. Crear hàbits (`/habits`)

| Funcionalitat | Descripció |
|---------------|------------|
| **Crear hàbit** | Formulari amb: nom, motivació, icona, categoria, freqüència (diària/setmanal), dies de la setmana, objectiu (vegades + unitat), dificultat (fàcil/mig/difícil), color. |
| **Llista dels meus hàbits** | Veure tots els hàbits creats pel compte. |
| **Editar hàbit** | Clic a un hàbit de la llista per obrir-lo en mode edició (formulari). |
| **Eliminar hàbit** | Botó per esborrar un hàbit de la llista. |

---

## 3. Catàleg de plantilles (`/plantilles`)

| Funcionalitat | Descripció |
|---------------|------------|
| **Filtrar plantilles** | Dropdown: “Totes” (públiques + pròpies) o “Les meves”. |
| **Crear plantilla** | Modal: títol, categoria, públic/privada, selecció d’hàbits a incloure. |
| **Editar plantilla** | Només les plantilles pròpies; mateix formulari que crear. |
| **Eliminar plantilla** | Només plantilles pròpies; confirmació abans d’esborrar. |
| **Exportar hàbits** | Seleccionar hàbits d’una plantilla i afegir-los als meus hàbits actuals. |

---

## 4. Perfil (`/perfil`)

| Funcionalitat | Descripció |
|---------------|------------|
| **Dades del perfil** | Nom, email, nivell, monedes, XP total i barra de progrés. |
| **Logros** | Vista dels logros desbloquejats amb tooltip (nom i descripció). |
| **Mascota** | Visualització de la mascota amb ratxa actual i màxima. |
| **Historial diari** | Últims logs d’activitat (hàbits completats o pendents amb dia). |

---

## 5. Navegació

| Secció | Ruta | Estat |
|--------|------|-------|
| Home | `/home` | Activa |
| Crear | `/habits` | Activa |
| Catàleg | `/plantilles` | Activa |
| Fòrum | — | Desactivada (en breu) |
| Perfil | `/perfil` | Activa |

---

## 6. Gamificació

| Concepte | Descripció |
|----------|------------|
| **XP** | S’aconsegueix completant hàbits; augmenta segons dificultat (fàcil 100, mig 250, difícil 400). |
| **Monedes** | S’obtenen completant hàbits; es gasten a la ruleta. |
| **Nivell** | Puja amb l’XP; barra de progrés per nivell (xp_actual_nivel / xp_objetivo_nivel). |
| **Ratxa** | Dies consecutius completant almenys un hàbit; es perd si no es completa cap hàbit el dia. |
| **Logros** | Medalles desbloquejades per hitos (ex.: primer hàbit, ratxes, etc.). |
| **Missió diària** | Objectiu del dia assignat pel sistema. |
| **Ruleta** | Tirada aleatòria que consumeix monedes i dona premis. |

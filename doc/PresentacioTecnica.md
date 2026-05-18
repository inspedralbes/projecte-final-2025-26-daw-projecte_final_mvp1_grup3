# Diapositiva 1: Portada
- Título principal: Looppy
- Subtítulo: Presentación Técnica
- Botón/Acción de inicio: [Iniciar Looppy]
- Mensaje de bienvenida: Benvingut a Looppy, [Nom d'usuari / Placeholder]

---
# Diapositiva 2: ¿Qué es Looppy?
- Título: Què és Looppy?
- Definición del proyecto (adaptar a la naturaleza de Looppy): Aplicació web gamificada per a la gestió d'hàbits (basada en l'Habit Loop) amb interacció de mascotes virtuals i comunitat en temps real.
- Interfaz: Interfície Web desenvolupada amb Nuxt 3 / Vue 3 i TailwindCSS.
- Core/Motor de desarrollo: Desenvolupat amb Pinia (Gestió d'estat), Socket.io-client (Temps real) i integració d'IA amb Google Gemini API.
- Servidor: Servidor propi desenvolupat amb Node.js i base de dades relacional.

---
# Diapositiva 3: Evolución del Proyecto - Sprint 1
- Título: Evolució del Projecte - Sprint 1
- Hito 1: Planificar projecte Looppy, definició del model 'Habit Loop' i disseny de l'arquitectura de la base de dades.
- Hito 2: Buscar recursos per implementar al projecte (assets gràfics, icones de monstres/mascotes i paletes de colors).
- Hito 3: Primeres implementacions i configuració de la base estructural (inicialització de Nuxt 3 i configuració del Backend base).

---
# Diapositiva 4: Evolución del Proyecto - Sprint 2
- Título: Evolució del Projecte - Sprint 2
- Hito 1: Crear interfícies base de les aplicacions (Dashboard/Home, llistat d'hàbits, formularis de registre i inici de sessió).
- Hito 2: Implementar connectivitat inicial entre el Frontend i la API REST del Backend.
- Hito 3: Implementar els primers elements funcionals/estètics (Mascota virtual estàtica, formulari de creació d'hàbits i barres de progrés).

---
# Diapositiva 5: Evolución del Proyecto - Sprint 3
- Título: Evolució del Projecte - Sprint 3
- Hito 1: Reorganitzar el projecte després del MVP (Mínim Producte Viable) establint una estructura modular clara.
- Hito 2: Enfocar i polir les connexions entre el Backend i el Frontend/Core (autenticació segura amb JWT i middleware de protecció de rutes).
- Hito 3: Canviar el flux de l'aplicació i "rework" del codi base per a major escalabilitat (refactorització d'estats globals amb Pinia i estandardització de components).

---
# Diapositiva 6: Evolución del Proyecto - Sprint 4
- Título: Evolució del Projecte - Sprint 4
- Hito 1: Noves interfícies més intuïtives per a l'usuari (gestió avançada del perfil, catàleg de plantilles filtrable, tenda i carretó d'inventari).
- Hito 2: Millora del sistema de connectivitat en temps real (integració de WebSockets per a l'atorgament instantani d'XP, monedes i reaccions de la mascota).
- Hito 3: Implementació de microserveis/controladors específics i connexió total amb APIs externes (Google Books, Wger, OpenWeather, YouTube i Gemini AI).

---
# Diapositiva 7: Evolución del Proyecto - Sprint 5
- Título: Evolució del Projecte - Sprint 5
- Hito 1: Seguretat, llicència HTTPS i Backend segur a producció.
- Hito 2: Implementació de detalls finals i interfície definitiva de Looppy (ruleta de premis contínua, xat privat entre amics, clans i mode focus immersiu).
- Hito 3: Aplicacions completament funcionals i connectades en temps real.

---
# Diapositiva 8: ¡Lo hemos conseguido! (Logros Técnicos)
- Título: Ho hem aconseguit!
- Sección BACKEND:
  - Implementació de microserveis i mòduls API REST robustos.
  - Seguretat HTTPS i configuració Nginx adaptada al nostre Back amb suport per a WebSockets.
  - Login integrat (JWT / OAuth protegit) i configuració de la seva API amb rols d'Usuari i Administrador.
- Sección CORE / ENGINE:
  - Sistema de gamificació i temps real integrat (Socket.io) per al càlcul d'XP, nivells, monedes i ratxes.
  - Sincronització de dades en temps real mitjançant connexió amb el Back (actualització immediata de l'estat de la mascota i recompenses).
- Sección FRONTEND:
  - Sistema principal de l'aplicació: Dashboard interactiu amb Mascota Virtual evolutiva, Ruleta diària i sistema de Plantilles clonables.
  - Funcionalidad de usuario: Eines d'alta productivitat com el Mode Focus immersiu, fòrum social amb adjunts i xat privat directe entre amics.

---
# Diapositiva 9: Retos Superados
- Título: Reptes Superats (Exemples)
- Problema 1 y Solución: [Problema: Desconnexions o pèrdua de sincronització en les recompenses d'hàbits -> Solució: Implementació d'esdeveniments bidireccionals amb Socket.io i reconnexió automàtica per garantir que l'XP i les monedes s'assignin correctament en temps real].
- Problema 2 y Solución: [Problema: Sobrecàrrega i format inconsistent en la generació d'hàbits per IA -> Solució: Enginyeria de prompts estricta amb Google Gemini API i validació/normalització de dades al controlador abans de desar a la base de dades].

---
# Diapositiva 10: Stack Tecnológico
- Título: Stack Tecnològic
- Tecnologías del Core/Motor: Pinia (State Management) + Socket.io (WebSockets) + Google Gemini AI (Onboarding).
- Tecnologías del Frontend: Nuxt 3 + Vue 3 + TailwindCSS + CSS Nadiu (Glassmorphism).
- Tecnologías del Backend y APIs: Node.js + Express/AdonisJS + MySQL/PostgreSQL + APIs Externes (OpenWeather, Google Books, Wger, YouTube).

---
# Diapositiva 11: Desarrolladores / Equipo
- Título: Desenvolupadors
- Integrantes del equipo de Looppy: Biel Domínguez, Iker Mata, Iker Lopez

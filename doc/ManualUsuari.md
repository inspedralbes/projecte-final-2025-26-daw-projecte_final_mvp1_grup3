# Manual d'Usuari - Looppy (El Teu Habit Loop Gamificat)

## 1. Introducció i Benvinguda

Benvingut/da al manual d'usuari oficial de **Looppy**, una aplicació web revolucionària dissenyada per transformar el seguiment de les teves rutines i hàbits diaris en una aventura captivadora i altament motivadora.

Basada en el reconegut concepte científic del **'Habit Loop'** (Senyal, Rutina, Recompensa), Looppy deixa enrere les llistes de tasques avorrides i fredes. Mitjançant una interfície visualment relaxant basada en el _soft design_ (amb un efecte tàctil i atractiu d'elements SVG i _claymorphism_), l'aplicació t'acompanya en el teu creixement personal a través de la gamificació.

### Quin valor aporta Looppy a l'usuari?

- **Motivació contínua i tangible:** A mesura que compleixes els teus hàbits, guanyes punts d'experiència (XP) i monedes virtuals, pujant de nivell com en un videojoc.
- **Una mascota virtual interactiva:** No estàs sol. Disposaràs d'un monstre o mascota virtual que evoluciona amb tu i reacciona en temps real al teu progrés. Si compleixes els teus hàbits, estarà feliç i plena d'energia; si te n'oblides, necessitarà la teva atenció.
- **Onboarding intel·ligent amb Intel·ligència Artificial (IA):** Gràcies a la integració amb Google Gemini, no hauràs de començar de zero. La IA analitza els teus objectius i estil de vida per suggerir-te rutines personalitzades des del primer minut.
- **Hàbits enriquits amb l'entorn:** Pots vincular els teus hàbits a llibres (Google Books), exercicis físics (Wger), vídeos d'aprenentatge (YouTube) o fins i tot a la informació meteorològica (OpenWeather).

---

## 2. Requisits del Sistema i Accés

Looppy s'ha desenvolupat utilitzant estàndards web d'última generació (com Nuxt 3 i WebSockets en temps real) per garantir la màxima velocitat, seguretat i fluïdesa.

### Accés a l'Aplicació

Pots accedir a la plataforma des de qualsevol dispositiu amb connexió a Internet a través de l'URL de producció oficial:
**`https://looppy.cat`**

```
+-----------------------------------------------------------------------------------+
|                           COMPATIBILITAT DEL SISTEMA                              |
+------------------------+----------------------------------------------------------+
| Navegadors Suportats   | Google Chrome (90+), Mozilla Firefox (88+), Apple Safari |
| (Escriptori i Mòbil)   | (14+), Microsoft Edge (90+).                             |
+------------------------+----------------------------------------------------------+
| Enfocament per         | • Espai Client / Usuari: Dissenyat i optimitzat          |
| Dispositiu             |   específicament per a mòbils i tauletes (Tablets).      |
| (Mobile vs Desktop)    | • Panell d'Administració: Concebut en exclusiva per a    |
|                        |   pantalles d'escriptori (Desktop / PC).                 |
+------------------------+----------------------------------------------------------+
| Requisits Tècnics      | Connexió a Internet estable (3G/4G/Fibra) i suport per a |
|                        | JavaScript i galetes (Cookies) activat al navegador.     |
+------------------------+----------------------------------------------------------+
```

---

## 3. Gestió de Comptes (Registre i Inici de Sessió)

Per començar a gaudir de la teva experiència gamificada i desar el progrés de la teva mascota, cal que disposis d'un compte d'usuari a la plataforma.

### Creació d'un Compte Nou (Registre)

1. Accedeix a la pàgina principal i fes clic al botó **"Registra't"** situat a la part superior dreta.
2. Emplena el formulari amb els camps obligatoris següents:
   - **Nom d'usuari:** El nom amb què se't coneixerà a l'aplicació.
   - **Correu electrònic:** Una adreça vàlida i activa on rebràs notificacions sobre el teu compte. El sistema validarà que tingui un format correcte (ex: `usuari@correu.cat`) i que no estigui prèviament registrat.
   - **Contrasenya i Confirmació:** Introdueix una contrasenya i repeteix-la al camp de confirmació per evitar errors tipogràfics.

> **Nota:** Per protegir la teva seguretat i el teu progrés, la contrasenya ha de complir amb criteris de seguretat robustos: ha de tenir un mínim de 8 caràcters i incloure una combinació de lletres majúscules, minúscules, números i algun caràcter especial (com ara `@`, `#`, `$`, `!`, `?`, `%`).

### Inici de Sessió (Login)

- **Iniciar Sessió:** Si ja tens un compte, fes clic a **"Inicia Sessió"**. Introdueix el teu correu electrònic i la contrasenya. Pots marcar la casella _"Recorda'm"_ per mantenir la sessió oberta al teu dispositiu.

`[Inserir captura de pantalla de la vista de Login i Registre amb l'estètica soft design]`

---

## 4. Rols d'Usuari i Permisos (Taula)

Looppy disposa d'un sistema estricte de control d'accés basat en rols (RBAC) gestionat de forma segura pel backend de Laravel. A continuació es detallen els permisos, capacitats i el dispositiu d'accés recomanat per a cada tipus d'usuari dins de la plataforma:

| Rol d'Usuari | Nivell d'Accés i Dispositiu | Funcionalitats i Permisos Principals |
| :--- | :--- | :--- |
| **Usuari Visitant** <br>_(Anònim)_ | Públic <br>_(Mòbil / Tauleta / PC)_ | • Accés a la Landing Page i explicació del mètode 'Habit Loop'.<br>• Consulta de la taula de plans, preus i preguntes freqüents (FAQ).<br>• Accés als formularis de Registre i Inici de Sessió. |
| **Client Registrat** <br>_(Usuari Gamificat)_ | Privat <br>_(Mòbil i Tauleta)_ | • Accés complet al Dashboard (Home) i interacció amb la mascota virtual.<br>• Creació, edició i eliminació d'hàbits propis (CRUD d'hàbits).<br>• Ús de l'onboarding amb IA (Gemini) i vinculació amb APIs externes.<br>• Accés al catàleg de plantilles (crear plantilles pròpies i clonar públiques).<br>• Progrés gamificat: guanyar XP, monedes, mantenir ratxes i tirar la ruleta.<br>• Interacció al fòrum Social (compartir rutines i adjunts).<br>• Consulta de l'activitat de cada dia mitjançant el Calendari (mensual).<br>• Mode Focus (dins dels detalls d'un hàbit) per a la concentració sense distraccions.<br>• Gestió del perfil amb desglossament complet de la ratxa, XP total per veure tot el progrés, medalles i historial. |
| **Administrador** <br>_(Superusuari)_ | Backoffice <br>_(Escriptori / PC)_ | • Accés exclusiu al Panell d'Administració protegit per token JWT.<br>• Supervisió del tauler de mètriques globals (usuaris actius, rànquings).<br>• Gestió i moderació d'usuaris (prohibir/desprohibir comptes amb motiu).<br>• Gestió del catàleg global de plantilles, hàbits, medalles i missions diàries.<br>• CRUD complet de Posts de Social i moderació del fòrum comunitari.<br>• Revisió de denúncies (reports) i llistat complet de registres d'auditoria (logs).<br>• Modificació de paràmetres i configuració global del sistema. |

---

## 5. Guia de Navegació i Interfície Principal

L'aplicació client de Looppy s'ha concebut sota un enfocament _Mobile-first_ (pensat per a telèfons mòbils i tauletes), oferint una navegació tàctil extremadament intuïtiva, neta i lliure de distraccions. Totes les seccions estan a un sol toc de distància a través de la barra de navegació adaptada.

```
+-------------------------------------------------------------------------------------------------------+
|                                  BARRA DE NAVEGACIÓ PRINCIPAL                                         |
+-----------+---------------+---------------+---------------+---------------+---------------------------+
| [ LOGO ]  | 🏠 Home       | 📁 Plantilles | 💬 Social     | 🛒 Tenda      | 👤 Perfil                 |
+-----------+---------------+---------------+---------------+---------------+---------------------------+
```

### Seccions de la Barra de Navegació (Navbar)

1. **Logo Looppy (Esquerra):** En fer-hi toc, et portarà sempre de tornada a la teva pantalla d'inici (Home).
2. **🏠 Home (Dashboard):** El teu centre de comandament diari. Aquí veuràs la teva mascota, la missió del dia, la teva ratxa actual i la llista d'hàbits pendents de completar avui. A més, a la Home hi ha accessos directes (imatges/divs per fer clic) per anar al **Carretó** (Inventari), al **Calendari** i a la **Ruleta** de premis.
3. **📁 Plantilles (`/plantilles`):** Un repositori on pots explorar rutines predefinides per altres usuaris o pel sistema, així com empaquetar els teus propis hàbits en plantilles per compartir-les.
4. **💬 Social (`/social`):** El fòrum comunitari on pots interactuar amb altres usuaris, compartir les teves rutines i adjuntar hàbits o plantilles per inspirar la comunitat.
5. **🛒 Tenda (`/shop`):** La botiga virtual on pots utilitzar les monedes guanyades per adquirir ítems, multiplicadors o accessoris per a la teva mascota.
6. **👤 Perfil (`/perfil`):** La teva fitxa personalitzada. Mostra el teu nivell global, l'experiència (XP) detallada per categories per veure tot el teu progrés, la gestió avançada de la teva ratxa, el llistat de medalles (logros) i l'historial complet d'activitat.

### Accessos Directes i Eines Específiques

- **🛒 Icona del Carretó (Inventari a la Home):** A la mateixa pantalla d'inici (Home) trobaràs l'accés directe del carretó. Aquest et permet accedir al teu inventari per veure tot el que has comprat com a usuari i poder activar-te o desactivar-te els fons, la roba (accessoris), pocions, etc.
- **🎡 Div de la Ruleta (a la Home):** A la Home també hi ha un div o secció dedicada per accedir directament a la Ruleta de premis, on pots utilitzar les teves monedes per provar sort.
- **📅 Icona de Calendari (a la Home):** Situada a la Home (igual que el carretó), et dona accés al calendari (`/calendar`). Aquest disposa d'una vista exclusivament mensual on pots fer clic a cada dia concret per veure quina activitat i hàbits vas realitzar en aquella data específica.
- **⏱️ Mode Focus (`/focus`):** Aquesta eina es troba ubicada directament dins dels detalls de cada hàbit. Quan obres els detalls d'un hàbit específic, pots fer clic al botó del Mode Focus per iniciar un temporitzador de concentració immersiu lliure de distraccions.

### Com moure's per l'app d'una forma lògica i fluida?

El flux natural de Looppy comença explorant el catàleg de **Plantilles** o creant hàbits personalitzats. En el teu dia a dia, la teva activitat es concentrarà a la **Home** per marcar el progrés, tenir cura de la mascota i utilitzar els accessos directes del carretó (per gestionar el teu inventari), el calendari i la ruleta. Quan vulguis realitzar un hàbit amb màxima concentració, entraràs als seus detalls per activar el **Mode Focus**. Finalment, podràs compartir els teus èxits a la secció **Social** i revisar la teva evolució completa (ratxa i XP) al **Perfil**.

---

## 6. Funcionalitats Principals Pas a Pas (Flux de l'aplicació)

A continuació, detallem pas a pas les tres operacions més importants que realitzaràs en el teu dia a dia dins de Looppy des del teu dispositiu mòbil o tauleta.

### Funcionalitat 1: Creació i Gestió d'Hàbits (Habit Loop)

Aquesta funcionalitat et permet dissenyar els hàbits que vols incorporar a la teva rutina, personalitzant-ne cada detall visual i funcional.

```
+-----------------------------------------------------------------------------------+
|                        PAS A PAS: CREACIÓ D'UN NOU HÀBIT                          |
+-----------------------------------------------------------------------------------+
| 1. Anar a ➕ Crear    --> 2. Omplir Formulari       --> 3. Configurar Objectiu    |
|    (Pestanya Hàbits)       (Nom, Icona, Dificultat)      (Freqüència i Venciment) |
+-----------------------------------------------------------------------------------+
```

**Passos a seguir:**

1. Fes toc a la secció **➕ Crear** a la barra de navegació per accedir a la pantalla de gestió d'hàbits (`/habits`).
2. Fes toc al botó destacat **"Nou Hàbit"**. S'obrirà el formulari de creació adaptat a pantalles tàctils.
3. **Emplenar el Formulari:** Completa els camps següents per definir el teu hàbit:
   - **Nom de l'hàbit:** Ex: _Beure 2 litres d'aigua_, _Llegir 20 pàgines_.
   - **Motivació:** Una frase que et recordi per què vols fer-ho (el teu _Senyal_ de l'Habit Loop).
   - **Icona i Color:** Tria una icona representativa i un color d'identificació visual.
   - **Categoria i Dificultat:** Selecciona la categoria (Salut, Estudi, Esport...) i la dificultat (_Fàcil_, _Mitjana_, _Difícil_). Tingues en compte que els hàbits difícils atorguen molta més XP (fins a 400 XP) i monedes quan es completen!
   - **Freqüència i Objectiu:** Defineix si l'hàbit és diari o quins dies de la setmana s'ha de repetir, i indica l'objectiu numèric (ex: _2 vegades al dia_, _30 minuts_).
4. **Vinculació Externa (Opcional):** Pots enllaçar el teu hàbit a un llibre de Google Books, a una rutina d'exercicis de Wger, a un vídeo explicatiu de YouTube o a una condició meteorològica d'OpenWeather.
5. Fes toc a **"Desar Hàbit"**. L'hàbit s'afegirà immediatament a la teva llista i estarà disponible a la teva Home els dies configurats.

`[Inserir captura de la pantalla de creació d'hàbits en versió mòbil/tauleta amb el formulari]`

---

### Funcionalitat 2: Interacció amb el Dashboard Gamificat (Mascota, Ratxa, XP i Ruleta)

La **Home** és el cor de Looppy. Aquí és on la teva constància es transforma en recompenses visuals i on interactues amb la teva mascota virtual en temps real des del teu telèfon o tauleta.

```
+-----------------------------------------------------------------------------------+
|                      EL CICLE DIARI GAMIFICAT A LA HOME                           |
+-----------------------------------------------------------------------------------+
|  1. REVISAR MISSIÓ    -->  2. COMPLETAR HÀBITS  -->  3. RECOMPENSA I RULETA       |
|  (Objectiu del dia)        (Toc a la llista)         (XP, Monedes i Mascota feliç)|
+-----------------------------------------------------------------------------------+
```

**Passos a seguir:**

1. Accedeix a la **🏠 Home**. A la part central veuràs la teva **Mascota Virtual**. A la part superior veuràs la icona de la ratxa amb el número de dies seguits que portes complint hàbits.
2. **Revisar la Missió Diària:** Trobaràs la targeta de la _Missió Diària_ (ex: _"Completa 3 hàbits"_). Complir-la et donarà un bo especial de monedes.
3. **Marcar el Progrés dels Hàbits:** Veuràs la llista dels teus _"Hàbits del dia"_. Cada hàbit mostra una barra de progrés.
   - Fes toc sobre l'hàbit per obrir el modal d'avanç i sumar un pas (ex: has begut 1 got d'aigua de 4).
   - Quan la barra arriba al 100%, fes toc a **"Completar Hàbit"**.
4. **Feedback en Temps Real:** Gràcies a la tecnologia Socket.io, en el mateix instant en què completes l'hàbit, veuràs una animació de celebració: la teva barra d'XP pujarà, el teu comptador de monedes s'incrementarà i la teva mascota reaccionarà amb alegria.
5. **Mantenir la Ratxa (Streak):** La icona de la ratxa mostra el teu número de dies consecutius. Pots veure també la teva ratxa al Perfil (ratxa actual i ratxa màxima).
6. **Girar la Ruleta de Premis (Gratuïta, 1 cop/dia):** Fes toc al div de la _Ruleta_ a la Home. Pots girar-la **una vegada per dia de forma totalment gratuïta**. Els premis possibles que pots guanyar són: 50 XP ⚡, 100 XP ⚡, 200 XP 🌟, 1 Moneda 🪙, 5 Monedes 🪙 o 10 Monedes 🪙. Si ja has tirat avui, el botó t'indicarà que tornis l'endemà.

`[Inserir captura del Dashboard / Home gamificat en mòbil/tauleta amb la mascota virtual i la ruleta de premis]`

---

### Funcionalitat 3: Catàleg de Plantilles i Gestió Avançada del Perfil (Ratxa i XP)

Aquesta funcionalitat et permet descobrir noves rutines creades per la comunitat, empaquetar els teus hàbits d'èxit i analitzar a fons el teu rendiment històric, ratxa i punts d'experiència.

```
+-----------------------------------------------------------------------------------+
|                     EXPLORACIÓ DE PLANTILLES I PERFIL HISTÒRIC                    |
+-----------------------------------------------------------------------------------+
| 1. Explorar Plantilles --> 2. Importar al Compte --> 3. Consultar Perfil i Logros |
|    (Catàleg Públic)         (Clonar Hàbits)           (Ratxa, XP, Medalles, Hist.)|
+-----------------------------------------------------------------------------------+
```

**Passos a seguir:**

1. **Explorar i Importar Plantilles (`/plantilles`):**
   - Fes toc a **📁 Plantilles** a la barra de navegació.
   - Pots filtrar el catàleg amb el desplegable, que ofereix exactament les categories reals de l'app:
     - **Totes** — mostra totes les plantilles accessibles.
     - **Públiques** — plantilles compartides per la comunitat.
     - **Personals** — les plantilles que tu mateix has creat.
     - **Amics** — plantilles publicades pels teus amics.
     - **Guardades** — plantilles que has importat des del fòrum o d'altres usuaris.
   - Fes clic a qualsevol plantilla per desplegar-la i veure els hàbits que conté. Des d'aquí pots **Importar** els seus hàbits al teu compte o **Exportar** la plantilla al fòrum social.
2. **Crear una Plantilla Pròpia:**
   - Al catàleg, fes toc al botó **"+"** per crear una nova plantilla.
   - Posa-hi un títol, tria una categoria, decideix si vols que sigui pública o privada, i selecciona quins dels teus hàbits actuals vols incloure-hi.
3. **Consultar el teu Progrés Avançat al Perfil (`/perfil`):**
   - Fes toc a **👤 Perfil** a la barra de navegació.
   - **Nivell i Monedes:** Veuràs el teu nivell actual i les monedes disponibles en format de targetes visuals.
   - **Barra d'XP:** Mostra el teu progrés d'experiència dins del nivell actual (XP actual / XP objectiu del nivell).
   - **Ratxa Actual i Ratxa Màxima:** Dues targetes mostren la teva ratxa de dies consecutius actual i la teva millor ratxa de tots els temps.
   - **Secció de Medalles (Logros):** Veuràs totes les medalles guanyades (ex: _"Primer Hàbit"_, _"Ratxa de 3 dies"_, _"Ratxa de 7 dies"_, _"Mestre dels Hàbits"_, _"Col·leccionista de Monedes"_, _"Amic de la Comunitat"_), amb el nom i la descripció de cada fita.
   - **Historial Diari:** Un registre cronològic de fins a 12 entrades recents de l'activitat, amb la data, el progrés i si l'hàbit va ser completat o no.

`[Inserir captura del catàleg de plantilles i del perfil d'usuari amb el detall de ratxa i XP en versió mòbil/tauleta]`

---

### Funcionalitat 4: Social (Fòrum, Amics i Clans), Calendari i Mode Focus

Aquestes eines enriqueixen l'experiència de Looppy aportant un vessant comunitari, una visió temporal de la teva activitat i un entorn d'alta productivitat.

```
+-----------------------------------------------------------------------------------+
|                    EINES COMUNITÀRIES, TEMPORALS I DE PRODUCTIVITAT               |
+--------------------+--------------------------------------------------------------+
| 💬 Fòrum Social    | Posts públics, adjuntar hàbits/plantilles, importar rutines. |
+--------------------+--------------------------------------------------------------+
| 👥 Amics           | Llista d'amics, sol·licituds pendents, xat privat i cercador.|
+--------------------+--------------------------------------------------------------+
| 🛡️ Clans           | Grups de la comunitat per col·laborar i competir junts.      |
+--------------------+--------------------------------------------------------------+
| 📅 Calendari       | Vista mensual; clic a cada dia per veure l'activitat.        |
+--------------------+--------------------------------------------------------------+
| ⏱️ Mode Focus      | Temporitzador immersiu; accessible des dels detalls d'hàbit. |
+--------------------+--------------------------------------------------------------+
```

**Passos a seguir:**

1. **Interaccionar al Fòrum Social (`/social`):**
   - Fes toc a **💬 Social** a la barra de navegació.
   - Accediràs al mur de la comunitat on els usuaris comparteixen les seves experiències, consells i rutines.
   - **Crear un Post i Adjuntar Contingut:** Escriu el teu missatge i, opcionalment, utilitza el botó **"+"** per adjuntar un dels teus hàbits o plantilles. Qualsevol usuari podrà importar el teu adjunt al seu compte.
2. **Gestionar Amics (`/friends`):**
   - Des de la secció Social, accedeix a la pàgina d'**Amics**. Disposa de tres pestanyes:
     - **Amics:** Llista dels teus amics actuals. Fes clic a qualsevol amic per obrir un **xat privat** directe amb ell.
     - **Pendents:** Sol·licituds d'amistat rebudes pendents d'acceptar o rebutjar.
     - **Buscador:** Cerca qualsevol usuari de la plataforma per nom i envia-li una sol·licitud d'amistat.
3. **Clans (`/clans`):**
   - La secció de **Clans** permet unir-te o crear grups de la comunitat per col·laborar i interactuar conjuntament.
4. **Consultar l'Historial amb el Calendari (`/calendar`):**
   - Fes toc a la icona/imatge del **📅 Calendari** situada a la teva Home.
   - S'obrirà el calendari en una **vista exclusivament mensual**.
   - Fes clic a qualsevol dia concret del mes per carregar i visualitzar quins hàbits vas completar i quin era el seu estat en aquella data.
5. **Concentrar-se amb el Mode Focus (`/focus`):**
   - Fes clic sobre un hàbit a la Home per obrir els seus **detalls**.
   - Dins del modal de detalls, trobaràs el botó per iniciar el **⏱️ Mode Focus**.
   - La pantalla entrarà en un mode immersiu de temporitzador. En finalitzar, l'hàbit es marcarà i rebràs un bo d'XP.

`[Inserir captura de les pantalles de Fòrum Social, Amics (xat privat), Clans, Calendari mensual i Mode Focus]`

---

## 7. Panell d'Administració (Backoffice - Exclusiu Escriptori / PC)

A diferència de l'aplicació client que està pensada per a l'ús diari en dispositius mòbils i tauletes, el **Panell d'Administració** de Looppy s'ha concebut sota un enfocament _Desktop-first_. Està dissenyat específicament per a pantalles d'escriptori (PC / Escriptori), aprofitant l'espai ampli per gestionar còmodament taules de dades extenses, analítiques globals i registres complexos del sistema.

```
+-----------------------------------------------------------------------------------+
|                  ARQUITECTURA DE SEGURETAT I FLUX DE DADES (ADMIN)                |
+--------------------+--------------------------------------------------------------+
| Seguretat i Accés  | Protecció extrema mitjançant Middleware Laravel EnsureAdmin. |
| (JWT / RBAC)       | Accés restringit exclusivament a usuaris amb `role=admin`.   |
+--------------------+--------------------------------------------------------------+
| Rendiment Híbrid   | • Operacions GET (Lectura): Peticions HTTP directes a l'API. |
| (API + WebSockets) | • Operacions CUD (Escriptura): Flux asíncron via Socket.io,  |
|                    |   cues Redis (`admin_queue`) i Laravel Workers en temps real.|
+--------------------+--------------------------------------------------------------+
```

### Com gestiona l'Administrador les dades del sistema des de l'Escriptori?

L'administrador accedeix a un entorn de backoffice independent (`/api/admin/dashboard`) des del seu ordinador on disposa d'eines de gestió avançades per supervisar tota la plataforma:

1. **Dashboard i Mètriques Globals:** Només entrar, l'administrador visualitza un tauler de control amb indicadors clau en temps real: nombre total d'usuaris registrats, usuaris prohibits/bloquejats, plantilles públiques actives, rànquings de les 5 plantilles i hàbits més utilitzats, i un llistat amb els últims 10 registres d'activitat global.
2. **CRUD d'Usuaris i Moderació Avançada (`/usuaris`):**
   - L'administrador pot visualitzar una taula paginada amb tots els usuaris i administradors del sistema, filtrant per estat o cercant per nom/correu.
   - **Bloqueig i Suspensió (Prohibir/Desprohibir):** Si un usuari té un comportament inadequat o fa un ús fraudulent, l'administrador pot fer clic al botó _"Prohibir"_. El sistema permet introduir un motiu explicatiu de la sanció. L'usuari sancionat queda bloquejat immediatament i no pot accedir a cap funció de l'aplicació.
3. **CRUD de Posts de Social i Moderació del Fòrum (`/posts`):**
   - L'administrador disposa d'un mòdul dedicat a la gestió i supervisió de totes les publicacions (posts) del fòrum social.
   - Permet realitzar el CRUD complet: crear publicacions oficials o fixades de l'administració, editar contingut, revisar els adjunts (hàbits o plantilles vinculades) i eliminar qualsevol post que incompleixi les normes de la comunitat. _(Nota: Aquesta funcionalitat es troba en fase d'integració i desenvolupament al backend per oferir un control total sobre l'activitat social)_.
4. **Supervisió del Catàleg Global:** L'administrador té permisos de lectura, modificació i eliminació sobre qualsevol element de la base de dades: gestió de totes les plantilles (`/plantilles`), hàbits (`/habits`), medalles o logros (`/logros`), i configuració de les missions diàries assignades pel sistema (`/missions`).
5. **Gestió de Denúncies (Reports):** Un mòdul dedicat (`/reports`) on es reben i gestionen les denúncies o queixes enviades pels usuaris respecte a plantilles públiques inadequades o problemes de la comunitat.
6. **Auditoria i Registres de Sistema (AdminLogs):** Per garantir la traçabilitat absoluta de qualsevol canvi sensible, el sistema registra automàticament cada acció realitzada per qualsevol administrador a la taula `ADMIN_LOGS`. L'administrador principal pot accedir a la vista de registres (`/logs`) i filtrar per rang de dates, administrador específic, tipus d'acció (Crear, Modificar, Eliminar) o text clau per auditar exactament quin valor hi havia abans i després de qualsevol modificació.

`[Inserir captura del Dashboard d'administració, la taula de gestió d'usuaris, el CRUD de posts i el visor de logs en pantalla d'escriptori / PC]`

---

## 8. Resolució de Problemes Freqüents (FAQ)

A continuació donem resposta als dubtes i possibles incidències més habituals que pots trobar durant l'ús de l'aplicació.

### 1. Què faig si no rebo el correu de confirmació o verificació després de registrar-me?

- **Resposta:** En primer lloc, revisa les carpetes de _Correu Brossa (Spam)_ o _Promocions_ de la teva bústia de correu, ja que alguns proveïdors poden filtrar els missatges automàtics. Si segueixes sense veure'l, accedeix a la pantalla de Login amb les teves credencials; el sistema detectarà que el teu compte no està actiu i et mostrarà un botó directe per **"Tornar a enviar el correu de confirmació"**. Si el problema persisteix, comprova que no haguessis comès un error tipogràfic en escriure el correu durant el registre.

### 2. Per què no puc pujar la meva foto de perfil o avatar? (Mida i Format)

- **Resposta:** Per motius de seguretat i optimització de l'espai al servidor, el sistema aplica restriccions estrictes sobre els arxius de perfil. Assegura't que la imatge que intentes pujar compleixi amb aquests dos requisits:
  1. **Format d'arxiu:** Ha de ser obligatòriament una imatge en format `JPG`, `PNG` o `WEBP`.
  2. **Mida màxima:** L'arxiu no pot superar els **2 MB** de pes. Si la teva fotografia és molt gran, et recomanem comprimir-la o reduir-ne les dimensions abans de tornar-la a pujar.

### 3. Per què es mostra un error de desconnexió o no s'actualitza l'XP i la mascota en temps real?

- **Resposta:** Looppy utilitza una connexió contínua en segon pla (WebSockets a través de Socket.io) per sincronitzar les animacions de la teva mascota i els punts d'XP sense necessitat de recarregar la pàgina. Si perds la connexió a Internet momentàniament o el teu navegador bloqueja les connexions de WebSockets (sovint a causa d'algun tallafocs, proxy d'empresa o extensió d'AdBlocker agressiva), el sistema mostrarà un petit avís de desconnexió. Per solucionar-ho, comprova la teva connexió a Internet, desactiva temporalment els bloquejadors d'anuncis per al nostre domini i prem `F5` (o recarrega la pàgina al mòbil/tauleta) per restablir la connexió amb el servidor.

### 4. Com funciona la pèrdua de la ratxa (streak) i com puc evitar que el meu monstre estigui trist?

- **Resposta:** La teva ratxa (representada per la icona del foc) mesura els dies consecutius en què has completat almenys un hàbit de la teva llista diària. El cicle diari es reinicia cada nit a les 00:00h. Si transcorre un dia sencer sense que hagis marcat com a completat cap hàbit, la ratxa es trencarà, el comptador tornarà a zero i la teva mascota perdrà energia, mostrant-se trista. Per evitar-ho, assegura't d'entrar cada dia a la teva Home per complir els teus objectius o fes servir les monedes acumulades per comprar un _"Escut de Ratxa"_ a la ruleta de premis, el qual et protegirà durant un dia d'inactivitat.

### 5. Com puc donar de baixa o eliminar el meu compte definitivament?

- **Resposta:** En compliment de la normativa de protecció de dades (RGPD), tens dret a eliminar el teu compte i el teu historial en qualsevol moment. Per fer-ho, dirigeix-te a la barra superior, fes toc a **👤 Perfil** i accedeix a la configuració del compte. A la pestanya de privacitat trobaràs l'opció **"Eliminar el meu compte definitivament"**. El sistema et demanarà introduir la teva contrasenya com a mesura de seguretat per confirmar l'acció. Un cop confirmada, totes les teves dades personals, hàbits creats i el progrés de la teva mascota s'esborraran de forma immediata i permanent dels nostres servidors.

---

## 9. Suport i Contacte

A Looppy volem que la teva experiència sigui perfecta i que assoleixis tots els teus objectius personals amb l'ajuda de la nostra gamificació. Si tens qualsevol dubte addicional, trobes alguna incidència tècnica o vols fer-nos arribar un suggeriment per millorar l'aplicació, el nostre equip de suport està a la teva completa disposició.

Pots posar-te en contacte amb nosaltres a través dels canals següents:

- **Correu Electrònic de Suport (Atenció Directa):**
  Pots escriure'ns un correu electrònic detallant el teu dubte o problema a:
  **`suport@looppy.cat`**
  _(El nostre equip tècnic es compromet a respondre totes les consultes en un termini màxim de 24 a 48 hores laborables)._

- **Formulari de Contacte / Ajuda Integrada:**
  Dins de la mateixa aplicació, pots fer toc a la icona d'ajuda `(?)` situada a l'extrem inferior dret de la pantalla per obrir el formulari de contacte ràpid o informar d'un error (_Report a Bug_) directament al nostre equip de desenvolupament.

---

**Moltes gràcies per confiar en Looppy per transformar la teva rutina diària! 🌀🚀**

# Manual d'Usuari - Loopy

**Loopy: El Teu Habit Loop Gamificat**  
*Projecte Final de Cicle — 2n DAW — Curs 2025-26 — Grup 3*

---

## 1. Introducció i Requisits d'Accés

### 1.1. Què és Loopy?

**Loopy** és una aplicació web de seguiment d'hàbits amb gamificació. Transforma les teves rutines diàries en una experiència motivadora basada en el concepte del **Habit Loop** (senyal, rutina i recompensa): completes hàbits, guanyes experiència (XP) i monedes, fas créixer la teva **mascota** i desbloqueges recompenses a la botiga.

L'aplicació inclou, entre d'altres:

- **Panell principal (Home)** amb els hàbits del dia, la mascota, la missió diària i la ruleta.
- **Gestió d'hàbits** (crear, editar, completar i fer seguiment del progrés).
- **Mode focus** per concentrar-te en un hàbit concret.
- **Calendari (Arxiu d'Aventures)** per revisar el teu historial.
- **Botiga i inventari** per personalitzar la mascota (skins, fons, consumibles).
- **Social, amics i clans** per compartir progrés amb altres usuaris.
- **Plantilles** d'hàbits suggerides (amb suport d'IA en l'onboarding).
- **Panell d'administració** per al personal que gestiona la plataforma.

### 1.2. URL d'accés (demo)

| Entorn | URL | Notes |
| :--- | :--- | :--- |
| **Demo local (Docker)** | [http://localhost:3000](http://localhost:3000) | Recomanat per a la defensa si executeu el projecte en local |
| **API backend** | [http://localhost:8000](http://localhost:8000) | Només per a consultes tècniques; l'usuari normal no hi accedeix directament |
| **Demo en producció** | *Substituïu aquesta fila per la URL pública del vostre desplegament* | Ex.: `https://looppy.cat` |

> **⚠️ ATENCIÓ:** Abans de la demo, assegureu-vos que els contenidors Docker estan en marxa (`docker compose up -d` des de la carpeta `docker`) i que la base de dades té les migracions i dades inicials carregades.

### 1.3. Requisits tècnics (navegador i dispositiu)

| Requisit | Detall |
| :--- | :--- |
| **Navegadors recomanats** | Google Chrome (última versió estable), Mozilla Firefox, Microsoft Edge o Safari (macOS/iOS) |
| **Navegadors no recomanats** | Internet Explorer, versions molt antigues de qualsevol navegador |
| **Connexió** | Internet estable (l'app usa API REST i WebSockets per a actualitzacions en temps real) |
| **Resolució mínima** | 360 px d'amplada (disseny responsive; experiència òptima en mòbil i escriptori) |
| **JavaScript** | Cal tenir-lo activat |
| **Cookies / emmagatzematge local** | Necessari per mantenir la sessió iniciada |

### 1.4. Rols d'usuari a l'aplicació

| Rol | Qui és? | Què pot fer? | On entra? |
| :--- | :--- | :--- | :--- |
| **Usuari** | Persona que vol millorar els seus hàbits | Crear i completar hàbits, guanyar XP i monedes, usar botiga, calendari, social, clans, ruleta, perfil | `/auth/login` → **Home** |
| **Administrador** | Personal que gestiona la plataforma | Gestionar usuaris, hàbits, plantilles, missions, logros, fòrum i logs d'auditoria | `/auth/login` (seleccionant accés admin) → **Panell Admin** |

> **⚠️ ATENCIÓ:** Un mateix correu electrònic **no** pot ser usuari i administrador alhora. Són comptes separats a la base de dades.

### 1.5. Idiomes disponibles

L'aplicació està traduïda a:

- **Català** (per defecte en moltes pantalles)
- **Castellà**
- **Anglès**

Podeu canviar l'idioma des del selector de llengua de la interfície (quan estigui visible a la capçalera o al menú d'usuari).

---

## 2. Primers Passos: Registre i Inici de Sessió

### 2.1. Accedir a la pàgina d'inici de sessió

**Pas 1.** Obriu el navegador i entreu a la URL de la demo (per exemple `http://localhost:3000`).

**Pas 2.** Si no esteu autenticats, se us redirigirà automàticament a la pantalla d'**Inici de sessió** (`/auth/login`).

![Pantalla d'inici de sessió](./img/login.png)

### 2.2. Credencials de prova per al tribunal (accés ràpid)

> **⚠️ ATENCIÓ — Compte de demostració per a l'avaluació:**
>
> | Camp | Valor |
> | :--- | :--- |
> | **Correu electrònic** | `profe@escola.com` |
> | **Contrasenya** | `Test1234` |
>
> Utilitzeu aquestes credencials per entrar directament com a **usuari** sense haver de registrar-vos durant la defensa.
>
> Si el compte encara no existeix a la vostra base de dades de demo, creeu-lo una vegada des de **Registre** (apartat 2.3) amb aquest mateix correu i contrasenya, o executeu el script de dades inicials del projecte.

### 2.3. Comptes alternatius (base de dades de desenvolupament)

Si treballeu amb la base de dades que ve al fitxer `database/insert.sql`, també podeu provar:

| Rol | Correu | Contrasenya (text pla) |
| :--- | :--- | :--- |
| Usuari de prova | `llorcar@user.com` | `user123` |
| Administrador | `admin@admin.com` | `admin123` |

### 2.4. Iniciar sessió com a usuari

**Pas 1.** A la pantalla de login, introduïu el **correu electrònic** i la **contrasenya**.

**Pas 2.** Assegureu-vos que esteu al mode **Usuari** (no Administrador), si la pantalla ho permet.

**Pas 3.** Premeu el botó **Iniciar sessió** (o equivalent segons l'idioma).

**Pas 4.** Si les credencials són correctes:

- Si és el **primer cop** que entreu i no heu fet l'onboarding, se us portarà a la pantalla d'**Onboarding** (`/onboarding`).
- Si ja heu completat l'onboarding, anireu directament a la **Home** (`/home`).

![Onboarding - selecció de categoria](./img/onboarding.png)

### 2.5. Registrar un compte nou

**Pas 1.** A la pantalla de login, premeu l'enllaç **Registre** o **Crear compte** (`/auth/registre`).

**Pas 2.** Ompliu el formulari:

- Nom (o nom d'usuari visible)
- Correu electrònic vàlid
- Contrasenya segura (mínim recomanat: 8 caràcters, lletres i números)

**Pas 3.** Accepteu les condicions si el formulari ho demana i premeu **Registrar-se**.

**Pas 4.** Rebreu un missatge de confirmació. A continuació se us guiarà cap a l'**onboarding** per triar la vostra mascota, categories d'interès i primers hàbits suggerits.

> **⚠️ ATENCIÓ:** També podeu registrar-vos amb **Google** si el servidor té configurades les credencials OAuth (`Continuar amb Google`). En entorns de demo local, aquesta opció pot estar desactivada.

### 2.6. Onboarding (primera vegada)

L'onboarding és un recorregut guiat que només es mostra als usuaris nous:

**Pas 1.** Trieu el tipus de **mascota** (el vostre company digital).

**Pas 2.** Seleccioneu les **categories** que us interessen (esport, alimentació, estudi, lectura, benestar, etc.).

**Pas 3.** L'assistent (amb IA) us pot suggerir **hàbits inicials** segons les vostres respostes.

**Pas 4.** Confirmeu i passeu a la Home amb els primers hàbits ja preparats.

![Home després de l'onboarding](./img/onboarding-finish.png)

### 2.7. Iniciar sessió com a administrador

**Pas 1.** A `/auth/login`, activeu l'opció **Administrador** (interruptor o pestanya, segons la versió de la UI).

**Pas 2.** Introduïu el correu i la contrasenya d'admin (per exemple `admin@admin.com` / `admin123` en entorn de desenvolupament).

**Pas 3.** Entrareu al **panell d'administració** (`/admin`), amb estadístiques, gestió d'usuaris i altres eines.

![Panell d'administració](./img/admin-dashboard.png)

### 2.8. Tancar sessió

**Pas 1.** Obriu el menú d'usuari o la secció de **Perfil**.

**Pas 2.** Premeu **Tancar sessió** / **Logout**.

**Pas 3.** Tornareu a la pantalla de login i s'esborraran les dades de sessió del navegador.

---

## 3. Recorregut per la Interfície

Aquest apartat descriu les zones principals de l'aplicació un cop heu iniciat sessió com a **usuari**.

### 3.1. Vista general — Home (panell principal)

La **Home** és el centre de l'experiència diària. Des d'aquí gestioneu els hàbits del dia, veieu la vostra mascota i accediu a la gamificació.

![Dashboard Principal](./img/dashboard.png)

#### Elements principals de la Home

| Zona | Ubicació (habitual) | Funció |
| :--- | :--- | :--- |
| **Capçalera / perfil** | Part superior | Nom, nivell, XP, monedes, accés a calendari, inventari, perfil |
| **Mascota** | Centre (escriptori) o part superior (mòbil) | Representació visual del vostre progrés; reacciona a les vostres accions |
| **Ratxa** | Prop de la mascota o estadístiques | Dies consecutius completant hàbits |
| **Llista d'hàbits del dia** | Columna lateral o secció inferior | Targetes amb cada hàbit actiu avui |
| **Missió diària** | Sota o al costat dels hàbits | Objectiu extra del dia (ex.: «Completa 1 hàbit mitjà») |
| **Ruleta diària** | Prop de la missió | Tirada diària per obtenir recompenses extra |
| **Botó crear hàbit** | Dins la llista d'hàbits | Obre el formulari per afegir un hàbit nou |

### 3.2. Targeta d'un hàbit (a la Home)

Cada hàbit es mostra en una **targeta** amb:

- **Icona i color** de la categoria.
- **Títol** de l'hàbit.
- **Dificultat** (fàcil, mitjà, difícil) — afecta l'XP i les monedes.
- **Progrés del dia** (ex.: `1/3` vegades completat).
- Botons **`+`** i **`-`** per incrementar o reduir el progrés.
- Estat **completat avui**: vora verda, tic i animació quan l'heu acabat del tot.

**Pas 1.** Premeu la targeta per **expandir-la** i veure més opcions (mode focus, detalls).

**Pas 2.** Premeu **`+`** per avançar el progrés; quan arribeu a l'objectiu diari, l'hàbit es marca com a completat.

![Hàbit completat amb vora verda](./img/habit-completat.png)

### 3.3. Menú i pantalles secundàries

| Pantalla | Ruta habitual | Descripció breu |
| :--- | :--- | :--- |
| **Hàbits** | `/habits` | Llista completa i gestió detallada d'hàbits |
| **Plantilles** | `/plantilles` | Biblioteca d'hàbits predefinits per importar |
| **Botiga** | `/shop` | Comprar skins, fons i consumibles amb monedes |
| **Inventari** | `/inventari` | Equipar objectes comprats |
| **Calendari** | `/calendar` | Historial mensual del vostre progrés |
| **Ruleta** | `/roulette` | Ruleta de recompenses (si no es fa des de la Home) |
| **Social** | `/social` | Mur social, publicacions |
| **Amics** | `/friends` | Sol·licituds i llista d'amics |
| **Clans** | `/clans` | Grups d'usuaris amb objectius compartits |
| **Perfil** | `/perfil` | Dades personals, estadístiques, logros |
| **Mode Focus** | `/focus/[id]` | Temporitzador per a un hàbit concret |

### 3.4. Navegació ràpida (consell per a la demo)

1. **Home** → completar un hàbit i la missió diària.  
2. **Calendari** → mostrar que el dia queda guardat.  
3. **Botiga** → gastar monedes en una skin.  
4. **Perfil** → mostrar nivell, XP i medalles.  
5. *(Opcional)* **Admin** → gestió d'usuaris (segons rol).

---

## 4. Funcionalitats Principals Pas a Pas

### 4.1. Crear un hàbit nou

Podeu crear hàbits des de la **Home** o des de la pàgina **Hàbits**.

#### Opció A — Des de la Home

**Pas 1.** A la secció d'hàbits del dia, premeu el botó **+** o **Crear hàbit** (segons el text de la interfície).

![Botó crear hàbit a la Home](./img/crear-habit-boto.png)

**Pas 2.** S'obrirà un formulari o desplegable amb els camps principals.

**Pas 3.** Ompliu com a mínim:

| Camp | Exemple | Obligatori? |
| :--- | :--- | :--- |
| **Títol** | «Beure 2 litres d'aigua» | Sí |
| **Categoria** | Alimentació / Esport / etc. | Sí |
| **Dificultat** | Fàcil, Mitjà o Difícil | Sí |
| **Objectiu vegades** | `1` (un cop al dia) o `3` (tres vegades) | Sí |
| **Freqüència** | Diari, setmanal o dies específics | Sí |
| **Icona / color** | Personalització visual | Opcional |

**Pas 4.** Premeu **Desar** / **Crear**.

**Pas 5.** L'hàbit apareixerà a la llista del dia (si correspon segons la freqüència). En uns segons es sincronitzarà amb el servidor.

![Formulari de creació d'hàbit](./img/crear-habit-formulari.png)

#### Opció B — Des de Plantilles

**Pas 1.** Aneu a **Plantilles** (`/plantilles`).

**Pas 2.** Navegueu per les plantilles disponibles (ex.: «Beber agua», «Estiraments», «Llegir 20 minuts»).

**Pas 3.** Seleccioneu una o diverses plantilles i premeu **Importar** o **Afegir a els meus hàbits**.

**Pas 4.** Torneu a la Home i comproveu que els hàbits importats apareixen a la llista.

![Pàgina de plantilles](./img/plantilles.png)

> **⚠️ ATENCIÓ:** Alguns hàbits poden vincular-se amb APIs externes (llibres, exercicis, vídeos, clima). En aquests casos, el formulari us demanarà cercar un element (per exemple, un llibre a Google Books) abans de desar.

---

### 4.2. Registrar progrés i completar un hàbit

Completar un hàbit és l'acció central de Loopy. Us dona **XP**, **monedes** i pot completar la **missió diària**.

#### Completar amb un sol toc (objectiu = 1 vegada al dia)

**Pas 1.** A la Home, localitzeu l'hàbit que voleu completar.

**Pas 2.** Premeu el botó **`+`** de la targeta (o obriu la targeta expandida i premeu **`+`**).

**Pas 3.** Quan el progrés arriba a l'objectiu (ex.: `1/1`), l'aplicació:

- Marca l'hàbit com a **completat avui** (vora verda i tic).
- Envia la confirmació al servidor.
- Actualitza XP, monedes i ratxa si escau.
- Comprova si heu completat la **missió diària**.

**Pas 4.** Espereu un instant: pot aparèixer un modal de **missió completada** o de **pujar de nivell**.

![Animació de completat](./img/habit-animacio-completat.png)

#### Completar un hàbit amb objectiu múltiple (ex.: 3 vegades al dia)

**Pas 1.** Premeu **`+`** per cada vegada que completeu la rutina (ex.: un got d'aigua).

**Pas 2.** El comptador puja: `1/3`, `2/3`…

**Pas 3.** En el darrer increment que arriba a l'objectiu, l'hàbit es marca com a **completat** automàticament.

**Pas 4.** També podeu obrir el **modal de progrés** (tocant l'hàbit) i usar els botons **+** / **-** o **Completar hàbit**.

![Modal de progrés d'hàbit](./img/modal-progres.png)

#### Mode Focus (opcional)

**Pas 1.** Des de l'hàbit expandit, premeu **Mode focus** / **Focus**.

**Pas 2.** Se us obrirà `/focus/[id]` amb un temporitzador.

**Pas 3.** Inicieu la sessió, feu la pausa si cal i, en acabar, confirmeu. El progrés es registra al servidor.

![Mode focus](./img/focus-mode.png)

> **⚠️ ATENCIÓ:** Si veieu l'hàbit completat a la pantalla però en refrescar la pàgina desapareix, comproveu que el servidor (Docker) i el worker de Redis estan actius. L'app necessita guardar el completat al backend, no només a la memòria del navegador.

---

### 4.3. Completar la missió diària

Cada dia rebeu una **missió diària** aleatòria (ex.: «Completa 1 hàbit», «Completa 1 hàbit mitjà», «Completa 2 hàbits avui»).

**Pas 1.** A la Home, localitzeu la targeta **Missió diària** (sota la llista d'hàbits o en una columna lateral).

**Pas 2.** Llegiu el text de la missió per saber què us demana.

**Pas 3.** Completeu un hàbit que satisfaci la condició (per exemple, un hàbit de dificultat **mitjana** si la missió ho demana).

**Pas 4.** Quan la missió es compleix:

- La targeta es posa **verda** amb un **tic** a l'esquerra.
- Rebeu **+20 XP** i **+10 monedes** extra (valors per defecte del sistema).
- Pot obrir-se un **modal de felicitació** amb el resum de la recompensa.

![Missió diària completada](./img/missio-completada.png)

**Pas 5.** La ruleta diària es desbloqueja o es pot usar segons l'estat del vostre compte (consulteu la targeta **Ruleta** a la Home).

---

### 4.4. Utilitzar la ruleta diària

**Pas 1.** A la Home (o a `/roulette`), comproveu que podeu tirar (normalment un cop al dia).

**Pas 2.** Premeu el botó per **girar la ruleta**.

**Pas 3.** Espereu l'animació; el resultat us assignarà una recompensa (monedes, XP, etc.) segons el disseny del MVP.

**Pas 4.** Les monedes i XP es reflecteixen automàticament al capçalera / perfil.

![Ruleta diària](./img/ruleta.png)

---

### 4.5. Consultar i editar hàbits (pàgina Hàbits)

**Pas 1.** Aneu a **Hàbits** (`/habits`) des del menú de navegació.

**Pas 2.** Veureu la llista completa dels vostres hàbits actius i inactius.

**Pas 3.** Per **editar** un hàbit:

- Premeu l'hàbit o el botó d'edició (llapis / menú).
- Modifiqueu títol, dificultat, objectiu, freqüència o categoria.
- Deseu els canvis.

**Pas 4.** Per **eliminar** un hàbit:

- Obriu el menú de l'hàbit i trieu **Eliminar**.
- Confirmeu l'acció al diàleg de seguretat.

![Llista d'hàbits](./img/llista-habits.png)

> **⚠️ ATENCIÓ:** Les accions de crear, editar i eliminar hàbits es processen en temps real via el servidor. En entorns de demo, un retard d'1–3 segons és normal.

---

### 4.6. Calendari — Arxiu d'Aventures

El calendari us permet revisar com heu anat dia a dia.

**Pas 1.** Aneu a **Calendari** (`/calendar`) des de la capçalera o el menú.

**Pas 2.** Veureu una **vista mensual** amb els dies marcats segons l'activitat (hàbits completats, monedes guanyades, estat de la mascota).

**Pas 3.** Premeu un **dia concret** per veure el detall (`/calendar` amb paràmetre de data o pantalla de dia).

**Pas 4.** En la vista de dia històric podeu veure:

- Quins hàbits vau completar.
- Com era la vostra mascota aquell dia (skin/fons si s'havien equipat).
- Resum de recompenses.

**Pas 5.** Utilitzeu el botó **Tornar** per tornar al calendari mensual o a la Home.

![Calendari mensual](./img/calendari.png)

![Detall d'un dia al calendari](./img/calendari-dia.png)

---

### 4.7. Botiga i inventari

#### Comprar a la botiga

**Pas 1.** Aneu a **Botiga** (`/shop`).

**Pas 2.** Navegueu per les categories: **Skins** (gorres, accessoris per a la mascota), **Fons** (escenaris), **Consumibles**, etc.

**Pas 3.** Cada article mostra el **preu en monedes**. Comproveu que teniu prou monedes (es mostren a la capçalera).

**Pas 4.** Premeu **Comprar** a l'article desitjat.

**Pas 5.** Confirmeu la compra. L'objecte passarà al vostre **Inventari**.

![Botiga Loopy](./img/botiga.png)

#### Equipar des de l'inventari

**Pas 1.** Aneu a **Inventari** (`/inventari`).

**Pas 2.** Veureu els objectes que heu comprat o desbloquejat.

**Pas 3.** Premeu **Equipar** en una skin o un fons.

**Pas 4.** Torneu a la **Home**: la mascota i l'entorn reflectiran el canvi.

![Inventari](./img/inventari.png)

---

### 4.8. Perfil, logros i estadístiques

**Pas 1.** Aneu a **Perfil** (`/perfil`).

**Pas 2.** Consulteu:

- Nom i correu.
- **Nivell** i barra d'XP cap al següent nivell.
- **Monedes** totals.
- **Ratxa** actual i màxima.
- **Logros / medalles** desbloquejades i pendents.

**Pas 3.** Des d'aquí podeu editar dades del perfil o canviar la contrasenya (si la funció està activa al MVP).

![Perfil d'usuari](./img/perfil.png)

---

### 4.9. Funcions socials (resum)

#### Amics

**Pas 1.** Aneu a **Amics** (`/friends`).

**Pas 2.** Cerqueu usuaris per nom o correu.

**Pas 3.** Envieu una **sol·licitud d'amistat** i accepteu les que rebeu.

#### Social (mur)

**Pas 1.** Aneu a **Social** (`/social`).

**Pas 2.** Publiqueu un missatge o mireu les publicacions d'altres usuaris.

**Pas 3.** Podeu donar **like** o comentar (segons funcions activades al MVP).

#### Clans

**Pas 1.** Aneu a **Clans** (`/clans`).

**Pas 2.** Creeu un clan nou o uniteu-vos a un d'existent amb codi d'invitació.

**Pas 3.** Veieu el progrés col·lectiu del clan.

![Clans](./img/clans.png)

---

### 4.10. Panell d'administració (rol Administrador)

Aquest apartat és per al **tribunal** si voleu mostrar la part de gestió. Cal iniciar sessió com a **administrador**.

**Pas 1.** Login a `/auth/login` en mode **Administrador** amb credencials d'admin.

**Pas 2.** Accedireu a `/admin` amb un dashboard d'estadístiques.

**Pas 3.** Funcions principals:

| Secció | Què hi podeu fer? |
| :--- | :--- |
| **Usuaris** | Llistar, cercar, prohibir o desprohibir comptes d'usuari |
| **Hàbits** | Revisar hàbits creats pels usuaris (moderació) |
| **Plantilles** | Crear i editar plantilles globals |
| **Fòrum** | Moderar contingut social/reportat |
| **Perfil admin** | Canviar contrasenya i dades de l'administrador |

**Pas 4.** Els canvis d'administració es registren als **logs d'auditoria** per traçabilitat.

![Gestió d'usuaris (admin)](./img/admin-usuaris.png)

---

## 5. Preguntes Freqüents (FAQ)

### 5.1. He completat un hàbit però no em marca la missió diària. Què faig?

**Resposta:** Comproveu que l'hàbit completat compleix el text de la missió (per exemple, si demana un hàbit **mitjà**, un hàbit només **fàcil** no en té prou). Completeu l'hàbit amb el botó **`+`** fins al tic verd i espereu uns segons. Si refresqueu la pàgina (`F5`) i l'hàbit segueix completat però la missió no, reviseu que el servidor Docker i el worker de cues estan en marxa.

![Comprovar missió i hàbit](./img/faq-missio.png)

---

### 5.2. L'hàbit es veu completat però en refrescar desapareix el tic verd. Per què?

**Resposta:** L'aplicació primer mostra un canvi visual ràpid i després confirma amb el servidor. Si el backend no respon, el canvi no es desa. Assegureu-vos que:

- Els contenidors **frontend**, **backend-laravel**, **backend-node** i **redis** estan actius.
- Teniu connexió a `http://localhost:8000` (API) i al socket (`localhost:3001`).
- No hi ha errors a la consola del navegador (tecla `F12` → pestanya *Console*).

---

### 5.3. No puc iniciar sessió amb `profe@escola.com`. Què passa?

**Resposta:** Aquest compte cal que existeixi a la base de dades de la vostra demo. Si no l'heu creat:

1. Aneu a **Registre** i registreu `profe@escola.com` amb contrasenya `Test1234`, **o**
2. Utilitzeu el compte de seed `llorcar@user.com` / `user123` (vegeu apartat 2.3).

Per a administrador, useu `admin@admin.com` / `admin123` en entorn de desenvolupament.

---

### 5.4. La ruleta, la botiga o el mode focus no funcionen. És normal?

**Resposta:** Algunes funcions depenen de configuració del servidor (Redis, worker Laravel, claus API externes). Per a la defensa, prioritzeu el flux **login → home → completar hàbit → missió → calendari**. Si una funció secundària falla, comenteu-ho al tribunal i mostreu els logs del contenidor corresponent.

---

## 6. Glossari ràpid

| Terme | Significat |
| :--- | :--- |
| **XP** | Punts d'experiència; pugen el vostre nivell |
| **Monedes** | Moneda del joc per comprar a la botiga |
| **Ratxa** | Dies seguits completant almenys un hàbit |
| **Missió diària** | Repte extra del dia amb recompensa addicional |
| **Plantilla** | Hàbit predefinit que podeu importar |
| **Skin / Fons** | Personalització visual de la mascota i l'escenari |
| **Habit Loop** | Cicle Senyal → Rutina → Recompensa |

---

## 7. Suport i contacte del projecte

**Equip de desenvolupament (Institut Pedralbes, 2n DAW):**

- Biel Domínguez  
- Llorenç Carnisser  
- Iker Mata  
- Iker Lopez  

**Recursos addicionals del repositori:**

- `README.md` — Instal·lació i stack tècnic  
- `docker/README.md` — Desplegament amb Docker  
- `InfoProjecte.md` — Documentació ampliada del projecte  

---

## 8. Llista de captures recomanades per al manual

Perquè el manual es vegi complet a GitHub, afegiu les imatges a la carpeta `img/` a l'arrel del projecte (al costat d'aquest fitxer) amb els noms següents:

| Fitxer | Contingut a capturar |
| :--- | :--- |
| `login.png` | Pantalla d'inici de sessió |
| `onboarding.png` | Onboarding (mascota o categories) |
| `onboarding-finish.png` | Final de l'onboarding |
| `dashboard.png` | Home completa |
| `habit-completat.png` | Hàbit amb vora verda i tic |
| `crear-habit-boto.png` | Botó crear hàbit |
| `crear-habit-formulari.png` | Formulari de nou hàbit |
| `plantilles.png` | Pàgina de plantilles |
| `habit-animacio-completat.png` | Hàbit en completar |
| `modal-progres.png` | Modal de progrés |
| `focus-mode.png` | Pantalla de focus |
| `missio-completada.png` | Missió diària completada |
| `ruleta.png` | Ruleta |
| `llista-habits.png` | Pàgina /habits |
| `calendari.png` | Vista mensual del calendari |
| `calendari-dia.png` | Detall d'un dia |
| `botiga.png` | Botiga |
| `inventari.png` | Inventari |
| `perfil.png` | Perfil |
| `clans.png` | Clans |
| `admin-dashboard.png` | Dashboard admin |
| `admin-usuaris.png` | Gestió d'usuaris admin |
| `faq-missio.png` | Exemple missió + hàbit (FAQ) |

---

*Document generat per al Projecte Final de Cicle — Loopy (2025-26). Última actualització: maig de 2026.*

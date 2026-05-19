# Projecte Loopy: Estat de les Funcionalitats i Arquitectura

## 1. Funcionalitats / Històries d'usuari

### Funcionalitats / Històries d'usuari que han de millorar
* **Onboarding:** Com a usuari, vull poder registrar-me a l’aplicació seguint un formulari de registre personalitzat.
* **Accés a la Home:** Com a usuari, vull poder entrar a la pàgina *Home* amb normalitat (correcció d'errors d'accés).
* **Interfície de Creació:** Com a usuari, vull veure un formulari de creació d’un hàbit més intuïtiu.
* **Disseny General:** Com a usuari, vull que l’aplicació estigui millor ambientada (estils i disseny).
* **Gestió d'Usuaris (Admin):** Millorar el bloqueig d'usuaris per assegurar que la funcionalitat és correcta i el formulari més amable.
* **Gestió de Plantilles (Admin):** CRUD complet de plantilles, permetent editar també els hàbits associats (no només nom i estat).
* **Seguretat Admin:** Com a administrador, vull poder canviar la meva contrasenya correctament.
* **Neteja de Codi:** Eliminar els CRUDs innecessaris d'Assoliments (*Logros*) i Missions que no s'utilitzen.

### Funcionalitats / Històries d'usuari noves
* **Botiga i Personalització:** Accés a la botiga i capacitat de personalitzar en Loopy.
* **Secció Social:** * Compartir hàbits, plantilles i veure el progrés d'amics.
    * Sistema de xat: General, privat i grupal (comunitats).
* **Integracions:** Connectar hàbits (ex: lectura) amb aplicacions externes de llibres per mostrar detalls al perfil.
* **Gamificació:** * Sistema d'incentius i recompenses per ratxes.
    * Evolució visual de Loopy (nivells i estat de salut segons la constància).
* **Comunicació i Suport:**
    * Sistema de reports per a usuaris i seccions de l'app.
    * Notificacions al telèfon.
* **Participació (Admin):** Creació d'esdeveniments generals per a votacions de la comunitat sobre futures millores.
* **Autenticació Social:** Inici de sessió amb Google per a usuaris i administradors.

---

## 2. Millores a diversos nivells

### L'aplicació resol un problema REAL de forma òptima?
L'aplicació ataca el problema de la falta de constància en la creació d'hàbits mitjançant la gamificació i el reforç social, transformant una tasca individual en una experiència interactiva i compartida.

### UI / UX
* Implementació de disseny **responsive** per a ús mòbil.
* Millora de l'ambientació visual al panell d'administració per a una millor experiència d'usuari.
* Simplificació de fluxos complexos com la creació d'hàbits i el registre inicial.

### API's REST
* Gestió de dades mitjançant serveis REST per a la comunicació entre el frontend i el backend.
* Integració amb APIs externes (com la de llibres) per enriquir el perfil de l'usuari.

### Monitorització, "observabilitat" i gestió d'errors
* **Gestió d'errors:** L'aplicació compta amb una **pàgina d'errors personalitzada** per a una millor experiència d'usuari. L'API està dissenyada per retornar **codis d'error clars i estandarditzats**, facilitant el depurament i la gestió de fallades des del client.
* **Logs:** (Pendent de definir el sistema de traçabilitat de logs del servidor).
* **Mètriques:** El Dashboard d'administració inclourà estadístiques de les plantilles i categories més utilitzades.

---

## 3. Valoració de l'arquitectura

* **Model Arquitectònic Híbrid:** L'aplicació utilitza un patró **MVC** amb una implementació específica segons el tipus d'operació per optimitzar el rendiment i el desacoblament:
    * **Operacions de Lectura (Read):** Utilitzem controladors estàndard a través d'endpoints d'API a **Laravel**.
    * **Operacions d'Escriptura (CUD - Create, Update, Delete):** Per optimitzar la reactivitat, s'utilitza una arquitectura **sense controladors directes**. La lògica s'executa a través de **Node.js**, que envia les dades mitjançant **Redis** per ser rebudes per Laravel (i viceversa).
* **Modularització i Separació:** L'arquitectura separa clarament la part de l'usuari (frontend i lògica de gamificació) de la gestió d'administració.
* **Baix acoblament:** L'ús de Redis com a pont entre Node.js i Laravel permet un flux de dades asíncron i altament desacoblat.
* **Domini i Infraestructura:** Es manté el domini (lògica de l'hàbit i Loopy) separat de la infraestructura de comunicació.

---

## 4. Auditoria de seguretat

* **Autenticació:** Implementació de Google Login i sistema segur de canvi de contrasenya.
* **Validació:** Formularis validats tant en client com en servidor per evitar dades corruptes.
* **Protecció:** (Pendent de revisió de seguretat contra SQL injection, XSS i CSRF).
* **Privacitat:** Gestió de la visibilitat de les dades en la secció social i el perfil d'usuari.

---

## 5. CI / CD

* **Pipeline de desplegament:** (Pendent de definir el pipeline automàtic).
* **Testing inclòs:**
    * **Unitaris:** Lògica de ratxes i punts.
    * **Integració:** Comunicació amb l'API de llibres i xats.
    * **E2E:** Flux de registre i creació d'hàbits.
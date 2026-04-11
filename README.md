# PROJECTE [ BACKEND ]

Aquest projecte conté gran part del contingut que s'ha explicat a classe i/o el contingut que s'ha demanat durant el curs al módul de Backend.

## Descripció del projecte

L’objectiu d’aquesta aplicació és oferir una plataforma d’articles on un usuari pot:

- Registrar-se i iniciar sessió
- Publicar, editar i eliminar articles
- Veure el seu propi tauler amb els seus articles
- Consultar el llistat general d’articles
- Tenir una paginació i una barra de recerca per cercar articles
- Tenir un compte d'administrador per administrar el contingut i els usuaris de la web
- Editar el perfil d’usuari o editar el perfil dels usuaris en cas de que sigui un administrador
- Recuperar la contrasenya si l’ha oblidat
- Autenticar-se amb sistemes externs com Discord i GitHub
- Cercar articles amb una barra de cerca amb AJAX
- Consumir dades d'una api i poder publicar-les com contingut d'articles.
- Proveir un endpoint per poder donar dades de la web
- Reforçar l'autenticació de l'API perquè la key estigui vinculada al mateix usuari que inicia sessió

## Flux d’autenticació

El projecte té en compte tres escenaris principals:

1. L’usuari no està autenticat.
   - Es mostra la pantalla de login.
2. L’usuari té sessió activa.
   - Es mostra el dashboard amb els articles propis.
3. L’usuari té cookie de "recorda’m".
   - Inicia sessió automàticament i mostra el dashboard.
 
Flux general:

1. Arrenca l’aplicació.
2. Es comprova si hi ha sessió activa.
3. Si no n’hi ha, es revisa si existeix la cookie de recordatori.
4. Si la cookie és vàlida, s’inicia sessió automàticament.
5. Si no hi ha cap accés vàlid, es redirigeix al login.

Sistemes de seguretats aplicades:

   - Per protegir les dades de la base de dades en cas que hi hagi un inject de dades i agafi'm les dades.
     - Les contrasenyes es validen al formulari per obligar a col·locar una mínimament segura:
        - 8 caràcters de longitud com a mínim
        - 1 majúscula com a mínim
        - 1 símbol com a mínim
        - 1 número com a mínim
     - La contrasenya es guarda hasheat per evitar que sigui fàcil d'extreure les contrasenyes.
     - Es guarden tokens hasheats a la cookie i a la base de dades quan li dones a "recorda’m".
     - La sessió té una durada de 40 minuts si l’usuari no activa "recorda’m".

## Funcionalitats principals

### Autenticació i accés

- registre d’usuaris
- inici de sessió
- tancament de sessió
- recordatori d’accés amb cookie
- recuperació i canvi de contrasenya
- autologin automàtic segons cookie

### Articles

- llistar articles públics
- veure articles propis
- crear articles
- editar articles
- eliminar articles
- ordenar i paginar resultats
- cerca d’articles per paraula clau
- cerca en viu amb AJAX

### Usuaris

- editar perfil personal
- administrar usuaris des del panell d’administració
- gestionar permisos segons rol

### Integracions externes

- OAuth amb Discord
- HybridAuth amb GitHub

### Consum de dades de Steam

El projecte també consumeix informació externa de Steam per convertir notícies en contingut propi de l'aplicació.

- Es fa una petició a l'API pública de Steam per obtenir notícies d'un joc concret.
- La resposta JSON es llegeix i es valida abans de mostrar-la.
- Es neteja el contingut per eliminar HTML o format BBCode no desitjat.
- Si la notícia porta imatge, es fa servir; si no, es genera una imatge per defecte basada en l'ID del joc.
- També es pot publicar una notícia com a article intern perquè quedi integrada dins del sistema de la web.

Aquest enfocament permet reutilitzar contingut extern i adaptar-lo al format de l'aplicació sense dependre manualment de copiar i enganxar informació.

### API i autenticació

Endpoints d'autenticació de l'API:

- `POST /api/auth/login`: autentica l'usuari des de client extern amb `user` o `email` i `pass` o `password`.
- `POST /api/auth/refresh`: comprova si la sessió encara és vàlida i, si existeix la cookie de recordatori, regenera l'accés.
- `POST /api/auth/logout`: tanca la sessió i elimina el recordatori d'accés.
- `GET /api/articles`: retorna els articles en format JSON, però només si la API key pertany al mateix usuari que té la sessió activa.

En resum, la API no queda oberta per a tothom: primer cal una key vàlida, i després es comprova que el compte autenticat sigui el mateix que ha generat aquesta key.

Sistemes de seguretats aplicades a l'API:

   - Per protegir l'accés a les dades de l'API i evitar que una API key es pugui usar lliurement des de qualsevol compte o sessió.
       - Les API keys es generen des del perfil de l'usuari i es poden rotar quan es vulgui.
       - El token de `remember_me` es guarda hashejat a la base de dades per evitar que es pugui reutilitzar si algú accedeix a la BD.
       - La sessió de l'usuari es valida abans de permetre l'accés a endpoints sensibles.
       - La API key no només ha de ser correcta, sinó que també ha de pertànyer al mateix usuari que té la sessió iniciada.
       - Si la sessió no coincideix amb el propietari de la API key, l'accés es denega.

## Estructura del projecte

```text
PRJ1/
├── index.php
├── README.md
├── config/
│   ├── routes.php
│   ├── Route.php
│   ├── config.php
│   └── database-schema.sql
├── app/
│   ├── controller/
│   │   ├── article/
│   │   ├── auth/
│   │   ├── api/
│   │   ├── oauth/
│   │   ├── user/
│   │   └── main-controller.php
│   ├── model/
│   │   ├── db-connection.php
│   │   ├── dao/
│   │   └── entity/
│   ├── utils/
│   └── view/
├── public/
│   ├── assets/
│   ├── errors/
│   └── uploads/
└── resources/
    ├── css/
    ├── fonts/
    └── lang/
```

## Carpetes i responsabilitat

### `index.php`

Punt d’entrada principal de l’aplicació. Carrega la configuració, el router i els controladors.

### `config/`

Conté la configuració general i les rutes de l’aplicació.

- `config.php`: constants globals com `BASE_PATH`, `BASE_URL` i la connexió amb la base de dades.
- `routes.php`: definició de totes les rutes del projecte.
- `Route.php`: router propi basat en mètodes HTTP.
- `database-schema.sql`: estructura SQL de la base de dades.

### `app/controller/`

Lògica de control de l’aplicació.

- `main-controller.php`: mostra la pàgina principal, articles propis i cerca.
- `article/`: creació, edició, eliminació i gestió d’articles.
- `auth/`: login, sessió, cookies i recuperació de contrasenya.
- `api/`: endpoints JSON per consumir articles, autenticar-se des de client extern i tancar sessió per API.
- `user/`: gestió de perfil i administració d’usuaris.
- `oauth/`: callbacks i fluxos d’autenticació externa.

### `app/model/`

Accés a dades i entitats.

- `db-connection.php`: connexió amb MySQL.
- `dao/`: consultes SQL encapsulades en classes DAO.
- `entity/`: models de dades com `Article` i `User`.

### `app/view/`

Vistes HTML/PHP de la interfície.

- `layout/`: header i footer compartits.
- `main/`: vista principal i dashboard.
- `auth/`: login, registre, recuperació i reset de password.
- `article/`: formularis d’article.
- `user/`: perfil i llistat d’usuaris.
- `oauth/`: vistes de confirmació i selecció d’usuari.

### `public/`

Recursos públics, imatges pujades i pàgines d’error.

### `resources/`

Estils CSS, fonts i fitxers de llengua.

## Rutes principals

### Pàgina principal

- `GET /` o `GET /home`: mostra tots els articles
- `GET /my-articles`: mostra els articles de l’usuari autenticat

### Autenticació

- `GET /login`: formulari d’inici de sessió
- `GET /register`: formulari de registre
- `GET /logout`: tanca la sessió
- `POST /login-submit`: processa l’inici de sessió
- `POST /register-submit`: processa el registre

### Recuperació de contrasenya

- `GET /forgot-password`: formulari de recuperació
- `POST /forgot-password`: envia el correu de recuperació
- `GET /reset-password`: formulari de reset
- `POST /reset-password-submit`: actualitza la contrasenya

### Perfil i administració

- `GET /profile/edit`: edició del perfil
- `POST /profile/edit-submit`: guarda el perfil
- `GET /admin/users`: llista d’usuaris
- `GET /admin/users/edit/{id}`: editar usuari
- `POST /admin/users/edit-submit/{id}`: guardar usuari
- `GET /admin/users/delete/{id}` i `POST /admin/users/delete/{id}`: eliminar usuari

### Articles

- `GET /article/create`: formulari de creació
- `POST /article/create-submit`: crear article
- `GET /article/edit/{id}`: formulari d’edició
- `POST /article/edit/{id}` i `POST /article/edit-submit`: desar canvis
- `GET /article/delete/{id}` i `POST /article/delete/{id}`: eliminar article
- `GET /article/search`: cerca normal amb pàgina de resultats
- `GET /article/search/results`: endpoint AJAX de cerca en viu

## Cerca amb AJAX

La barra de cerca de la vista principal fa peticions asíncrones mentre l’usuari escriu.

Funcionament:

1. El JavaScript escolta l’`input` de cerca.
2. Quan hi ha com a mínim dues lletres, fa una petició amb `fetch`.
3. El controlador respon amb JSON.
4. Els resultats es mostren en un desplegable sense recarregar la pàgina.
5. En prémer Enter, el formulari continua fent la cerca normal.
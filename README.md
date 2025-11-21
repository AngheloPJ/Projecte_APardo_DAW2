# Analizando práctica

## ¿Qué quiero que pase y que datos necesito?

- 3 posibilidades:

  - No está logeado / no tiene cookie / no tiene sesión? --> Usuario anónimo [ Mostrar Login ]
  - Tiene sesión? -> Mostrar dashboard ( Articulos que ha publicado él )
  - Tiene cookie? (Recordarme seleccionado) --> Iniciar sesión automáticamente y mostrar dashboard ( Articulos que ha publicado él )

- Idea de como empear:

  - Comienza producción.
  - Revisamos:
    - ¿Hay sesión activa?
      - Sí --> Mostrar dashboard.
      - No --> Mandamos a revisar COOKIE;
    - ¿Hay cookie de recordar?
      - Sí --> Iniciar sesión automáticamente y mostrar dashboard.
      - No --> Mostrar página de login.

- Estructura base

  PRJ1/
  │
  ├── index.php
  ├── README.md
  ├── config/
  │ ├── routes.php
  │ ├── env.php
  │ └── PT04_Anghelo_Pardo.sql
  ├── app/
  │ ├── controller/
  │ │ ├── main-controller.php
  │ │ ├── login-controller.php
  │ │ ├── cookie-controller.php
  │ │ └── session-controller.php
  │ ├── model/
  │ │ ├── db-connection.php
  │ │ ├── dao/
  │ │ │ ├── ArticleDAO.php
  │ │ │ └── UserDAO.php
  │ │ │── entity/
  │ │ │ ├── Article.php
  │ │ │ └── Usuari.php
  │ └── view/
  │ ├── main-view.php
  │ ├── login-view.php
  │ └── register-view.php
  ├── resources/
  │ ├── css/
  │ │ ├── main.css
  │ │ └── login.css

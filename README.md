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

## Estructura de carpetas

```
src/app/
├── core/                     # SINGLETONS: Código que solo se carga una vez
│   ├── auth/                 # Servicios de JWT, Guards, Interceptors
│   ├── services/             # Servicios globales (ej: OfflineService con Dexie)
│   ├── models/               # Interfaces e Idioms globales
│   └── app.config.ts         # Configuración central
│
├── shared/                   # REUTILIZABLE: Componentes, Pipes y Directivas comunes
│   ├── components/           # Botones, inputs, modales (BEM)
│   ├── directives/           # Directivas de permisos, focus, etc.
│   └── pipes/                # Formateo de EAN, fechas, stocks
│
├── features/                 # FUNCIONALIDAD: Una carpeta por dominio de negocio
│   ├── inventory/            # Gestión de stock
│   │   ├── components/       # Componentes específicos del inventario
│   │   ├── pages/            # Componentes de "página" (vistas completas)
│   │   └── inventory.routes.ts
│   ├── auth/                 # Login, Recuperar contraseña
│   │   ├── pages/login/
│   │   └── auth.routes.ts
│   └── orders/               # Pedidos o recepciones
│
├── assets/                   # Estáticos y Estilos Globales
│   └── scss/
│       ├── base/             # Reset, tipografía, variables
│       └── main.scss         # Import central de BEM
└── index.html
```

**¿Por qué esta estructura?**

- Core vs Shared: Si pones el AuthService en shared, corres el riesgo de instanciarlo varias veces. En core, te aseguras de que el estado de la autenticación sea único.

- Features (Funcionalidad): Si aparece ua funcionalidad nueva, creamos una carpeta nueva en features. No tocas el resto del código.

- Pages vs Components: Dentro de cada funcionalidad, separamos los "Smart Components" (Pages: gestionan datos) de los "Dumb Components" (Components: solo muestran datos y emiten eventos).

- BEM Encapsulado: Los estilos específicos de la tarjeta de inventario irán en `features/inventory/components/card/card.component.scss`, mientras que los colores de marca irán en `assets/scss/base/_variables.scss`.


## TODO

¿Te gustaría que implementemos el OfflineInterceptor para que, cuando el resilienceInterceptor agote los reintentos, el sistema intente buscar los datos en la base de datos local de Dexie automáticamente?

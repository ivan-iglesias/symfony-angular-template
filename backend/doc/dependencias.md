# Dependencias

#### symfony/security-bundle

Proporciona herramientas para el hash de contraseñas (encriptarlas), el manejo de roles (ROLE_ADMIN, ROLE_DRIVER) y el control de acceso a las rutas.

#### lexik/jwt-authentication-bundle

Implementa el estándar JWT (JSON Web Token). Cuando un usuario se loguea con éxito, este bundle genera una cadena de texto larga y firmada (el token).

#### doctrine/orm

Te permite hablar con la base de datos usando objetos de PHP en lugar de escribir SQL a mano constantemente.

#### doctrine/doctrine-bundle

Es la integración oficial para que Doctrine funcione dentro de Symfony.

#### symfony/maker-bundle

Herramienta de desarrollo por consola que genera código boilerplate automáticamente (comandos como `make:controller`, `make:entity`, `make:migration`, etc.).

#### symfony/uid

Genera y gestiona identificadores únicos como UUIDs (v4, v7) y ULIDs. Ideal para claves primarias no secuenciales y trazabilidad (`correlation_id`).

#### symfony/rate-limiter

Proporciona mecanismos para limitar la frecuencia de peticiones HTTP en la API (ej. prevenir ataques de fuerza bruta en el `/login` o abusos de tráfico).

#### symfony/lock

Provee un sistema de bloqueos (locks) concurrentes usando Redis, BD o memoria. Evita condiciones de carrera (race conditions) en ejecuciones simultáneas.

#### symfony/asset

Gestiona la generación de URLs y rutas absolutas o relativas para recursos estáticos (imágenes, fuentes, assets de documentación).

#### psr/log

PSR significa PHP Standard Recommendation. Este paquete define una interfaz común para que todos los sistemas de logging (como Monolog, que viene con Symfony) hablen el mismo idioma.

#### symfony/monolog-bundle

Integración del motor Monolog en Symfony. Permite enviar registros/logs a ficheros, consola, servicios externos o formatearlos mediante procesadores personalizados.

#### symfony/validator

Proporciona un motor de validación basado en anotaciones/atributos (como `#[Assert\NotBlank]`) para verificar la integridad de DTOs y datos de entrada.

#### symfony/mailer

Componente para la creación y envío de correos electrónicos de forma síncrona o asíncrona mediante transportes como SMTP.


## SWAGGER

#### nelmio/api-doc-bundle

Este paquete lee tu código (tus controladores, tus entidades y los atributos que añadimos como `#[OA\Post]`) y lo convierte en un archivo de especificación OpenAPI.

#### symfony/twig-bundle

Twig es un lenguaje que permite generar HTML de forma dinámica. Aunque tu API solo devuelva JSON, Symfony necesita Twig para "dibujar" páginas web internas como la interfaz visual de Swagger UI.


## Fixtures y Datos de Prueba

#### doctrine/doctrine-fixtures-bundle

Permite crear clases PHP donde defines objetos (como Usuario), les asignas valores y los guardas en la base de datos de forma controlada.


## Tests

#### phpunit/phpunit

Framework para la ejecución de Tests Unitarios (probar la lógica de tus entidades y servicios en Domain) y Tests de Integración.

#### symfony/test-pack

Suite de herramientas para hacer peticiones HTTP reales a tu API (como si fueras Swagger o Angular) y verificar que un POST /api/shipments devuelve un 201 Created.

-  **symfony/phpunit-bridge**

    Es un componente que se asegura de que PHPUnit se ejecute con las dependencias correctas de Symfony y limpia los deprecation warnings para que la consola no se llene de ruido.

-  **symfony/browser-kit**

    Es el que permite que el $client haga peticiones internas sin necesidad de levantar un servidor web real (es extremadamente rápido).

#### dama/doctrine-test-bundle

Inicia una transacción de base de datos antes de cada test y le hace un rollback automático al terminar, manteniendo la BD limpia en cada prueba.

Preparación de la BD de Test (Solo una vez):

```sh
# Crear la base de datos de test
php bin/console doctrine:database:create --env=test

# Crear las tablas
php bin/console doctrine:migrations:migrate --env=test --no-interaction

# Cargar tus usuarios
php bin/console doctrine:fixtures:load --env=test --no-interaction
```

## Crear una nueva migración

Detectar cambios y generar el archivo de migración

```
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

## Borrón y cuenta nueva

```
php bin/console doctrine:database:drop --force
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:schema:validate
php bin/console doctrine:fixtures:load
```

## Dependencias

```sh
composer require symfony/security-bundle lexik/jwt-authentication-bundle
composer require doctrine/orm doctrine/doctrine-bundle psr/log symfony/monolog-bundle
composer require --dev symfony/maker-bundle
```

#### symfony/security-bundle

Proporciona herramientas para el hash de contraseñas (encriptarlas), el manejo de roles (ROLE_ADMIN, ROLE_DRIVER) y el control de acceso a las rutas.

#### lexik/jwt-authentication-bundle

Implementa el estándar JWT (JSON Web Token). Cuando un usuario se loguea con éxito, este bundle genera una cadena de texto larga y firmada (el token).

#### doctrine/orm y doctrine/doctrine-bundle

Te permite hablar con la base de datos usando objetos de PHP en lugar de escribir SQL a mano constantemente.

#### doctrine/doctrine-bundle

Es la integración oficial para que Doctrine funcione dentro de Symfony.

#### symfony/uid

```sh
composer require symfony/uid
```

#### symfony/rate-limiter

```sh
composer require symfony/rate-limiter
```

#### symfony/lock

```sh
composer require symfony/lock
```

#### psr/log

PSR significa PHP Standard Recommendation. Este paquete define una interfaz común para que todos los sistemas de logging (como Monolog, que viene con Symfony) hablen el mismo idioma.

## SWAGGER

```sh
composer require nelmio/api-doc-bundle
composer require symfony/asset symfony/twig-bundle
```

#### nelmio/api-doc-bundle

Este paquete lee tu código (tus controladores, tus entidades y los atributos que añadimos como #[OA\Post]) y lo convierte en un archivo de especificación OpenAPI.

#### symfony/twig-bundle

Twig es un lenguaje que permite generar HTML de forma dinámica. Aunque tu API solo devuelva JSON, Symfony necesita Twig para "dibujar" páginas web internas.

## Tests

```sh
composer require --dev doctrine/doctrine-fixtures-bundle
composer require --dev phpunit/phpunit symfony/test-pack dama/doctrine-test-bundle

php bin/console --env=test doctrine:database:create
php bin/console --env=test doctrine:migrations:migrate

php bin/phpunit
```

#### symfony/validator

```sh
composer require symfony/validator
```

#### symfony/mailer

```sh
composer require symfony/mailer
```

#### doctrine/doctrine-fixtures-bundle

Permite crear clases PHP donde defines objetos (como Usuario), les asignas valores y los guardas en la base de datos de forma controlada.

#### phpunit/phpunit

Tests Unitarios (probar la lógica de tus entidades y servicios en Domain) y Tests de Integración.

#### symfony/test-pack

Hacer peticiones HTTP reales a tu API (como si fueras Swagger o Angular) y verificar que un POST `/api/shipments` devuelve un 201 Created.

-   **symfony/phpunit-bridge**

Es un componente que se asegura de que PHPUnit se ejecute con las dependencias correctas de Symfony y limpia los deprecation warnings para que la consola no se llene de ruido.

-   **symfony/browser-kit**

Es el que permite que el $client haga peticiones internas sin necesidad de levantar un servidor web real (es extremadamente rápido).

#### dama/doctrine-test-bundle

Inicia una transacción de base de datos antes de cada test y le hace un rollback al terminar.

Preparación de la BD de Test (Solo una vez)

```sh
# Crear la base de datos de test
php bin/console doctrine:database:create --env=test

# Crear las tablas
php bin/console doctrine:migrations:migrate --env=test --no-interaction

# Cargar tus usuarios (admin@esku.com, etc.)
php bin/console doctrine:fixtures:load --env=test --no-interaction
```

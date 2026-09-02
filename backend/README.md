## Arquitectura

- [Dependencias instaladas](doc/dependencias.md)
- [Ciclo de vida](doc/ciclo-de-vida.md)
- [Concurrencia e Idempotencia](doc/concurrencia-e-idempotencia.md)

## Comandos básicos

### Doctrine

```sh
# Borrado de migraciones si hay inconsistencias
rm migrations/Version*.php

# Recrea la base de datos limpia
php bin/console doctrine:database:drop --force
php bin/console doctrine:database:create

# Genera migración con el esquema actual
php bin/console doctrine:migrations:diff

# Aplica la migración
php bin/console doctrine:migrations:migrate --no-interaction

# Carga fixtures
php bin/console doctrine:fixtures:load --no-interaction
```

### Redis

Comandos básicos en Redis.

```sh
# Ver claves existentes
KEYS *

# Buscar una clave en concreto
KEYS token_confirm_*

# Obtener valor de una clave
GET token_confirm_XXXXXXXX

# Consultar el tiempo de vida (TTL)
TTL token_confirm_XXXXXXXX

# Limpiar o borrar una clave
DEL token_confirm_AQUI_TU_TOKEN

# Limpiar o borrar todas las claves
FLUSHALL
```

#### Refresh token

El `refresh_token` (de larga duración) **nunca expone su valor en el JSON**; viaja exclusivamente mediante una cookie segura `HTTP-Only`, `Secure` y `SameSite=Strict`.


```sh
# Listar los tokens activos de un usuario específico
SMEMBERS "user_tokens:admin@acme.com"

# Ver a qué usuario pertenece un token concreto
GET "refresh_token:XXXXXXXX"

# Consultar cuántos segundos le quedan de vida al token (TTL)
TTL "refresh_token:XXXXXXXX"
```

##### Revocar manualmente una sesión individual

```sh
# Eliminar la clave del token individual
DEL "refresh_token:XXXXXXXX"

# Eliminar la referencia del token en el índice del usuario
SREM "user_tokens:admin@acme.com" "refresh_token:XXXXXXXX"
```

##### Revocar TODAS las sesiones de un usuario

Además de guardar la clave `refresh_token:<HASH>`, asociamos cada token al conjunto `user_tokens:<USER_EMAIL>`. Esto permite listar, auditar o **revocar todas las sesiones de un usuario de forma masiva** en $O(1)$ sin realizar búsquedas costosas con `KEYS *`.

```sh
# Obtener todos los tokens asociados al usuario
SMEMBERS "user_tokens:admin@acme.com"

# Eliminar las claves de los tokens obtenidos (puedes pasar múltiples claves a DEL)
DEL "refresh_token:XXXXXXXX" "refresh_token:YYYYYYYYY"

# Eliminar el índice del conjunto del usuario
DEL "user_tokens:admin@acme.com"
```

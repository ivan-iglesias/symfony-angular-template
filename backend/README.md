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

## Arquitectura

- [Dependencias instaladas](doc/dependencias.md)
- [Ciclo de vida](doc/ciclo-de-vida.md)
- [Concurrencia e Idempotencia](doc/concurrencia-e-idempotencia.md)

## Comandos básicos

### Crear una nueva migración

Detectar cambios y generar el archivo de migración

```
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

### Borrón y cuenta nueva

```
php bin/console doctrine:database:drop --force
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:schema:validate
php bin/console doctrine:fixtures:load
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

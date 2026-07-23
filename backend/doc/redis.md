# Redis

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

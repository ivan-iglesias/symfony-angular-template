# Carga las variables del .env nativamente en Make
include .env
export

COMPOSE := docker compose -p $(PROJECT_NAME) -f docker-compose.yml

.PHONY: build up down start stop ps logs goto redis-cli db-reset db-refresh db-diff db-validate test test-unit test-it help

## --- DOCKER CONTROL ---
build:
	@$(COMPOSE) build --no-cache

up:
	@$(COMPOSE) up -d

down:
	@$(COMPOSE) down

start:
	@$(COMPOSE) start

stop:
	@$(COMPOSE) stop

top:
	@$(COMPOSE) top

ps:
	@$(COMPOSE) ps -a

logs:
	@read -p "Service name (php, nginx, database, redis): " SERVICE; \
	$(COMPOSE) logs -f $$SERVICE

goto:
	@read -p "Service name (php, nginx, database, redis): " SERVICE; \
	docker exec -it $(PROJECT_NAME)_$$SERVICE sh

redis-cli:
	docker exec -it $(PROJECT_NAME)_redis redis-cli

## --- TESTING ---
test:
	$(PHP_EXEC) vendor/bin/phpunit

test-unit: ## Ejecuta solo los tests unitarios
	$(PHP_EXEC) vendor/bin/phpunit --testsuite Unit

test-it: ## Ejecuta solo los tests de integración
	$(PHP_EXEC) vendor/bin/phpunit --testsuite Integration

## --- HELP ---
help: ## Muestra esta lista de comandos disponibles
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-15s\033[0m %s\n", $$1, $$2}'

# Evitar que make procese argumentos desconocidos
%:
	@:

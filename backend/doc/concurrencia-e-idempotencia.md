

# Estrategia de concurrencia e idempotencia

Documento descriptivo sobre la diferencia de responsabilidades entre el middleware de Idempotencia y el servicio de Bloqueos Distribuidos (`LockService`).

## Conceptos Clave

* **Idempotency-Key (Capa HTTP / Red):** Protege operaciones de mutación contra reintentos involuntarios (fallos de red, doble clic en frontend). Si la petición ya fue procesada, devuelve la respuesta cacheada sin re-ejecutar la lógica.

> En el frontend, la `Idempotency-Key` no debe generarse en la función `onClick` del botón, debe generarse al inicializar la vista/pantalla de la operación y reutilizarse en todos los reintentos de esa misma acción hasta que se complete con éxito.

* **LockService (Capa de Aplicación / Dominio):** Protege recursos compartidos contra colisiones en tiempo real (*race conditions* simultáneas). Garantiza que solo un hilo procese una sección crítica a la vez.

## Flujo de Ejecución

```
┌───────────────────────────────────────────────┐
│            Petición HTTP Entrante             │
└──────────────────────┬────────────────────────┘
                       │
                       ▼
¿Tiene Idempotency-Key? (Mismo Origen)
  │
  ├── SI ──► Listener comprueba registro de Respuestas:
  │             ├── ¿Respuesta ya registrada? ─► Devuelve la respuesta guardada
  │             └── ¿Primera vez? ─────────────► Continúa el flujo y guarda la respuesta
  │
  └── NO ────────────────────────────────────► Continúa el flujo
          │
          │
          ▼
    ¿Operación Crítica / Concurrente? (LockService en Aplicación)
          │
          └── SÍ ──► LockService::acquireAndExecute()
          │               │
          │               ├── ❌ No adquirido ─► 409 Conflict / RESOURCE_LOCKED
          │               └──  Adquirido    ───► Ejecutar Caso de Uso + Liberar
          │
          │
          └── NO  ─────────────────────────────► Ejecutar Caso de Uso directamente
```

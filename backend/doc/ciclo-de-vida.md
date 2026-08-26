
## Flujo funcional de peticiones HTTP

La arquitectura de este backend sigue el principio **Happy Path First**. Los controladores únicamente gestionan y retornan el escenario de éxito. Cualquier interrupción del flujo estándar (errores de validación, violaciones de reglas de negocio o fallos de infraestructura) interrumpe la ejecución mediante excepciones.

<u>**Flujo Happy Path First**</u>

```
                 [ Petición HTTP ]
                         │
                         ▼
                ┌─────────────────┐
                │   Controlador   │
                └────────┬────────┘
                         │
                  ¿Todo correcto?
                  /             \
              SÍ /               \ NO (Lanza exepción)
                v                 v
┌──────────────────────┐   ┌────────────────────────┐
│ ApiResponse::success │   │  ApiExceptionListener  │
└───────────┬──────────┘   └───────────┬────────────┘
            │                          │
            │                          ▼
            │                ┌────────────────────┐
            │                │ ApiResponse::error │
            │                └─────────┬──────────┘
            │                          │
            └───────────┬──────────────┘
                        ▼
               [ Respuesta HTTP ]
```

## Mapa de eventos y prioridades de ejecución

Symfony utiliza un bus de eventos (`KernelEvents`) para procesar las peticiones. La ejecución ordenada de nuestros listeners garantiza el aislamiento de responsabilidades:

| Evento | Listener / Resolver | Prioridad | Propósito |
| :--- | :--- | :---: | :--- |
| `KernelEvents::REQUEST` | `CorrelationIdListener` | **255** | Extrae o genera el `correlation_id` (UUIDv4) en la entrada y lo propaga a Monolog. |
| `KernelEvents::REQUEST` | `IdempotencyListener` | **10** | Consulta si existe una respuesta previa guardada en caché para esa clave de idempotencia. |
| *Argument Resolver* | `RequestDtoResolver` | *N/A* | Deserializa el cuerpo JSON al DTO e invoca las reglas de validación. |
| `KernelEvents::EXCEPTION`| `ApiExceptionListener` | **10** | Intercepta cualquier excepción no capturada y la convierte en un `ApiResponse` con formato JSON estándar. |
| `KernelEvents::RESPONSE` | `IdempotencyListener` | **0** | Almacena en caché la respuesta del controlador si la petición incluía cabecera de idempotencia. |
| `KernelEvents::RESPONSE` | `CorrelationIdListener` | **-255** | Inyecta la cabecera `X-Correlation-ID` en el HTTP Response antes de enviarlo al cliente. |

## Resolución y validación automática de DTOs

Para evitar heredar de controladores base (`BaseApiController`) o duplicar código de deserialización en cada acción, se utiliza un **Value Resolver** personalizado.

Cuando Symfony inspecciona la firma del controlador, `RequestDtoResolver` entra en acción únicamente si el parámetro implementa una clase bajo el namespace de DTOs (`\DTO\`).

<u>**Flujo de mapeo a DTO**</u>

```
[ HTTP Request ]
       │ (JSON Body)
       ▼
[ Symfony ArgumentResolver ]
       │ (Inspecciona la firma del método __invoke)
       ▼
[ RequestDtoResolver ] ─── (Filtra por tipo \DTO\)
       │
       ├── 1. Deserializa el JSON a la clase DTO
       ├── 2. Ejecuta ValidatorInterface
       │      │
       │      └── ¿Errores de validación? ──> SÍ ──> Lanza ValidationException (422)
       │                                             (ApiExceptionListener lo captura)
       ▼ NO
[ Controller ] (Recibe el DTO instanciado, validado e inmutable)
```

<u>**Ejemplo**</u>

El controlador recibe el DTO completamente construido y validado:

```php
public function __invoke(LoginInput $input): ApiResponse
{
    // Si la ejecución llega aquí, $input está 100% validado
    $responseDto = $this->action->execute($input);

    return ApiResponse::success($responseDto);
}
```

## Trazabilidad global con correlation ID

Cada petición HTTP entrante se vincula a un id de trazabilidad único (correlation_id). Este ID acompaña a la petición a través de todas las capas de infraestructura, logs y respuestas.

```
[ HTTP Request ]
       │
       ▼
[ 1. CorrelationIdListener ] ──► Lee 'X-Correlation-ID' o genera un UUIDv4
       │                         Guarda el ID en $request->attributes y enMonolog
       ▼
[ 2. Controller / Domain ]   ──► Genera logs conteniendo el correlation_id
       │                         Devuelve ApiResponse::success($data)
       ▼
[ 3. CorrelationIdListener ] ──► Intercepta la respuesta y adjunta:
       │                         - Header: X-Correlation-ID
       ▼
[ HTTP Response (Client) ]
```

## Gestión unificada de excepciones

El `ApiExceptionListener` intercepta cualquier fallo devuelto por la aplicación y ajusta el formato de salida JSON y el código de estado HTTP adecuado:

1. `ValidationException` (HTTP 422): Devuelve el listado de campos con errores de validación emitidos por el DTO.

2. `BusinessException` (HTTP 4xx): Captura excepciones del dominio (ej. credenciales inválidas, saldo insuficiente) y expone su código interno.

3. `HttpExceptionInterface` (HTTP 404, 405, 429): Mapea errores nativos del framework como rutas no encontradas o límites de Rate Limiter superados.

4. `Excepciones no controladas` (HTTP 500): Registra el error en los logs con el correlation_id e impresiones del stack trace, devolviendo un mensaje genérico para proteger la seguridad del sistema.

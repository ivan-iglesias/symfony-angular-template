<?php

namespace App\Shared\Infrastructure\Logging;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use Symfony\Component\HttpFoundation\RequestStack;

final class CorrelationIdProcessor implements ProcessorInterface
{
    private static string $correlationId = '';

    public static function setCorrelationId(string $id): void
    {
        self::$correlationId = $id;
    }

    // Esta por si integramos colas/mensajería. Sirve para propagar el ID fuera del ciclo HTTP.
    public static function getCorrelationId(): string
    {
        return self::$correlationId;
    }

    public function __invoke(LogRecord $record): LogRecord
    {
        if (self::$correlationId !== '') {
            return $record->with(extra: array_merge($record->extra, [
                'correlation_id' => self::$correlationId,
            ]));
        }

        return $record;
    }
}

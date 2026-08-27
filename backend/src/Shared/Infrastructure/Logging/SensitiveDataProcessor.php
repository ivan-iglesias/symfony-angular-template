<?php

namespace App\Shared\Infrastructure\Logging;

use App\Shared\Infrastructure\Utils\StringSanitizer;
use Monolog\Attribute\AsMonologProcessor;
use Monolog\LogRecord;

#[AsMonologProcessor]
class SensitiveDataProcessor
{
    private const EMAIL_KEYS = ['email'];
    private const SECRET_KEYS = ['password', 'token', 'code', 'authorization', 'secret', 'bearer', 'api_key'];

    public function __invoke(LogRecord $record): LogRecord
    {
        $context = $this->sanitizeArray($record->context);

        return $record->with(context: $context);
    }

    private function sanitizeArray(array $data): array
    {
        foreach ($data as $key => $value) {
            $normalizedKey = strtolower((string) $key);

            if (is_array($value)) {
                $data[$key] = $this->sanitizeArray($value);
                continue;
            }

            if (in_array($normalizedKey, self::EMAIL_KEYS, true) && is_string($value)) {
                $data[$key] = StringSanitizer::maskEmail($value);
                continue;
            }

            if (in_array($normalizedKey, self::SECRET_KEYS, true)) {
                $data[$key] = '***[REDACTED]***';
            }
        }

        return $data;
    }
}

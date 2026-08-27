<?php

namespace App\Shared\Infrastructure\Utils;

final class StringSanitizer
{
    public static function maskEmail(?string $email): string
    {
        if (null === $email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return '***';
        }

        [$name, $domain] = explode('@', $email, 2);
        $length = mb_strlen($name);

        if ($length <= 2) {
            return mb_substr($name, 0, 1) . '***@' . $domain;
        }

        return mb_substr($name, 0, 1) . '***' . mb_substr($name, -1) . '@' . $domain;
    }
}

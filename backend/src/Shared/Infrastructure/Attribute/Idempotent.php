<?php

namespace App\Shared\Infrastructure\Attribute;

#[\Attribute(\Attribute::TARGET_METHOD)]
class Idempotent
{
    public function __construct(
        public int $ttlSeconds = 86400 // 24 horas por defecto
    ) {}
}

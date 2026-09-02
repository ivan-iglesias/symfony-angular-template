<?php

namespace App\Shared\Infrastructure\Service;

use App\Shared\Domain\Service\CodeGeneratorInterface;

final readonly class RandomCodeGenerator implements CodeGeneratorInterface
{
    public function generateNumeric(int $length = 5): string
    {
        if ($length < 1) {
            throw new \InvalidArgumentException('La longitud debe ser de al menos 1 dígito.');
        }

        $min = 10 ** ($length - 1);
        $max = (10 ** $length) - 1;

        return (string) random_int($min, $max);
    }
}

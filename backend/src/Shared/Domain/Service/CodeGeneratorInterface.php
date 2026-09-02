<?php

namespace App\Shared\Domain\Service;

interface CodeGeneratorInterface
{
    public function generateNumeric(int $length = 5): string;
}

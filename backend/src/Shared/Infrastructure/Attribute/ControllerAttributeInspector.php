<?php

namespace App\Shared\Infrastructure\Attribute;

use ReflectionMethod;
use Symfony\Component\HttpFoundation\Request;

class ControllerAttributeInspector
{
    /**
     * @template T of object
     * @param class-string<T> $attributeClass
     * @return T|null
     */
    public function getMethodAttribute(Request $request, string $attributeClass): ?object
    {
        $controller = $request->attributes->get('_controller');

        if (!$controller) {
            return null;
        }

        try {
            $reflection = match (true) {
                is_array($controller) => new ReflectionMethod($controller[0], $controller[1]),
                is_string($controller) && str_contains($controller, '::') => new ReflectionMethod(...explode('::', $controller, 2)),
                is_string($controller) && class_exists($controller) => new ReflectionMethod($controller, '__invoke'),
                default => null,
            };

            if (!$reflection) {
                return null;
            }

            $attributes = $reflection->getAttributes($attributeClass);

            return !empty($attributes) ? $attributes[0]->newInstance() : null;
        } catch (\ReflectionException) {
            return null;
        }
    }
}

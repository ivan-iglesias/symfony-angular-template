<?php

namespace App\Shared\Infrastructure\Request;

use App\Shared\Domain\Exception\ValidationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final readonly class RequestDtoResolver implements ValueResolverInterface
{
    public function __construct(
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {}

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $type = $argument->getType();

        // Solo procesa si el tipo es una clase DTO
        if ($type === null || !str_contains($type, '\\DTO\\')) {
            return [];
        }

        $content = $request->getContent();
        if (trim($content) === '') {
            $content = '{}';
        }

        try {
            $dto = $this->serializer->deserialize($content, $type, 'json');
        } catch (SerializerException | \UnexpectedValueException $e) {
            // SerializerException es una interfaz del Serializer de Symfony, no cubre la excepción
            // de decodificación nativa de PHP, por lo que añadimos UnexpectedValueException.
            throw new ValidationException(
                ['json' => 'Sintaxis JSON no válida o tipos de datos incompatibles.'],
                'El cuerpo de la petición contiene un formato JSON inválido.'
            );
        }

        $violations = $this->validator->validate($dto);
        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }

            throw new ValidationException('Error de validación de campos.', $errors);
        }

        return [$dto];
    }
}

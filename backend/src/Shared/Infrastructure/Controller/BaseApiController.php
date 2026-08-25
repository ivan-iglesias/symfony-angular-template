<?php

namespace App\Shared\Infrastructure\Controller;

use App\Shared\Domain\Exception\BusinessException;
use App\Shared\Infrastructure\Response\ApiResponse;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class BaseApiController extends AbstractController
{
    public function __construct(
        protected readonly LoggerInterface $logger,
        private readonly ValidatorInterface $validator,
    ) {}

    /**
     * Para POST/PUT con Body JSON y validación de DTO
     */
    protected function handleInput(Request $request, string $dtoClass, callable $action): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        // Instanciar DTO
        $input = new $dtoClass($data);

        // Validar
        $errors = $this->validator->validate($input);
        if (count($errors) > 0) {
            $messages = [];
            foreach ($errors as $error) {
                $messages[$error->getPropertyPath()] = $error->getMessage();
            }

            return ApiResponse::error('VALIDATION_ERROR', 'Error de validación de campos.', 422, $messages);
        }

        // Ejecutar Acción y capturar errores
        return $this->runSafe(fn() => $action($input));
    }

    /**
     * Para GET/DELETE o acciones simples sin DTO
     */
    protected function handleSimpleAction(callable $action): JsonResponse
    {
        return $this->runSafe($action);
    }

    /**
     * Captura las excepciones en un solo sitio
     */
    private function runSafe(callable $action): JsonResponse
    {
        try {
            $result = $action();

            if ($result instanceof JsonResponse) return $result;

            return ApiResponse::success($result);
        } catch (BusinessException $error) {
            return ApiResponse::error(
                $error->getBusinessCode(),
                $error->getMessage(),
                $error->getCode()
            );
        } catch (\Throwable $error) {
            $this->logger->critical('Fallo crítico: ' . $error->getMessage());

            return ApiResponse::critical();
        }
    }
}

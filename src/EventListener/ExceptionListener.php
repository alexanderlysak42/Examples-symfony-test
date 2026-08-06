<?php

namespace App\EventListener;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException as CoreAccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Validator\Exception\ValidationFailedException;

#[AsEventListener(event: 'kernel.exception', priority: 10)]
class ExceptionListener
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
    ) {
    }

    public function __invoke(ExceptionEvent $exceptionEvent): void
    {
        $exception = $exceptionEvent->getThrowable();
        $validationException = null;
        if ($exception instanceof ValidationFailedException) {
            $validationException = $exception;
        } elseif ($exception->getPrevious() instanceof ValidationFailedException) {
            $validationException = $exception->getPrevious();
        }

        if ($validationException !== null) {
            $status = 422;
            $data = [
                'error' => 'validation_failed',
                'details' => $this->formatErrors($validationException),
            ];
        } elseif ($exception instanceof UniqueConstraintViolationException) {
            $status = 422;
            $data = ['error' => 'duplicate_entry'];
        } elseif ($exception instanceof AuthenticationException) {
            $status = 401;
            $data = ['error' => 'unauthorized'];
        } elseif ($exception instanceof CoreAccessDeniedException) {
            if ($this->tokenStorage->getToken() === null) {
                $status = 401;
                $data = ['error' => 'unauthorized'];
            } else {
                $status = 403;
                $data = ['error' => 'access_denied'];
            }
        } elseif ($exception instanceof HttpExceptionInterface) {
            $status = $exception->getStatusCode();
            $data = ['error' => $status === 404 ? 'not_found' : 'http_error'];
        } else {
            $status = 500;
            $data = ['error' => 'internal_error'];
        }

        $exceptionEvent->setResponse(new JsonResponse($data, $status));
    }

    private function formatErrors(ValidationFailedException $validationFailedException): array
    {
        $errorsData = [];
        $errors = $validationFailedException->getViolations();

        foreach ($errors as $error) {
            $errorsData[$error->getPropertyPath()][] = $error->getMessage();
        }

        return $errorsData;
    }
}

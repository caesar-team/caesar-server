<?php

declare(strict_types=1);

namespace App\Event\ExceptionListener;

use App\Exception\ApiException;
use App\Utils\ErrorMessageFormatter;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class ErrorResponseListener
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ErrorMessageFormatter
     */
    private $errorMessageFormatter;

    public function __construct(LoggerInterface $logger, ErrorMessageFormatter $errorMessageFormatter)
    {
        $this->logger = $logger;
        $this->errorMessageFormatter = $errorMessageFormatter;
    }

    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();
        if ('dev' === getenv('APP_ENV')) {
            $this->logError($exception);
        }

        $event->setThrowable($this->createException($exception));
        /** @var ApiException $newException */
        $newException = $event->getThrowable();

        $event->allowCustomResponseCode();
        $response = new JsonResponse($newException->getData(), (int) $newException->getCode());
        $event->setResponse($response);
    }

    private function logError(Throwable $exception)
    {
        $context = [
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ];
        $this->logger->error($exception->getMessage(), $context);
    }

    private function createException(Throwable $exception): ApiException
    {
        if ($exception instanceof ApiException) {
            return $exception;
        }

        $data = $this->errorMessageFormatter->errorFormat($exception);

        return new ApiException($data, $this->getCodeByException($exception));
    }

    private function getCodeByException(Throwable $exception): int
    {
        switch (true) {
            case $exception instanceof HttpExceptionInterface:
                return $exception->getStatusCode();
        }

        return Response::HTTP_BAD_REQUEST;
    }
}

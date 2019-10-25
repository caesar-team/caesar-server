<?php

declare(strict_types=1);

namespace App\Event\ExceptionListener;

use App\Exception\ApiException;
use App\Utils\ErrorMessageFormatter;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

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

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        if ('dev' === getenv('APP_ENV')) {
            $this->logError($exception);
        }

        $event->setException($this->createException($exception));
        /** @var ApiException $newException */
        $newException = $event->getException();

        $event->allowCustomResponseCode();
        $response = new JsonResponse($newException->getData(), $newException->getCode());
        $event->setResponse($response);
    }

    private function logError(\Exception $exception)
    {
        $context = [
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ];
        $this->logger->error($exception->getMessage(), $context);
    }

    private function createException(\Exception $exception): ApiException
    {
        if ($exception instanceof ApiException) {
            return $exception;
        }

        $data = $this->errorMessageFormatter->errorFormat($exception);

        return new ApiException($data, Response::HTTP_BAD_REQUEST);
    }
}
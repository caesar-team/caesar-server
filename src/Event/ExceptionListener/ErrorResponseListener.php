<?php

declare(strict_types=1);

namespace App\Event\ExceptionListener;

use App\Exception\ApiException;
use App\Utils\ErrorMessageFormatter;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Routing\RouterInterface;
use Throwable;

class ErrorResponseListener
{
    private const ADMIN_ROUTE = 'easyadmin';

    private LoggerInterface $logger;

    private ErrorMessageFormatter $errorMessageFormatter;

    private RouterInterface $router;

    public function __construct(
        LoggerInterface $logger,
        ErrorMessageFormatter $errorMessageFormatter,
        RouterInterface $router
    ) {
        $this->logger = $logger;
        $this->errorMessageFormatter = $errorMessageFormatter;
        $this->router = $router;
    }

    public function onKernelException(ExceptionEvent $event)
    {
        $request = $event->getRequest();
        if (self::ADMIN_ROUTE === $request->attributes->get('_route')) {
            $session = $request->getSession();
            if ($session instanceof Session) {
                $session->getFlashBag()->set('danger', $event->getThrowable()->getMessage());
            }

            $event->setResponse(new RedirectResponse(
                $this->router->generate(self::ADMIN_ROUTE)
            ));

            return;
        }

        $exception = $event->getThrowable();
        $this->logError($exception);

        $event->setThrowable($this->createException($exception));
        /** @var ApiException $newException */
        $newException = $event->getThrowable();

        $event->allowCustomResponseCode();
        $response = new JsonResponse($newException->getData(), (int) $newException->getCode());
        $event->setResponse($response);
    }

    private function logError(Throwable $exception): void
    {
        if ($exception instanceof HttpExceptionInterface) {
            return;
        }

        $context = [
            'trace' => $exception->getTraceAsString(),
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

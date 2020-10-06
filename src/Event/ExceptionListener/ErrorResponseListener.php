<?php

declare(strict_types=1);

namespace App\Event\ExceptionListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ErrorResponseListener
{
    private const ADMIN_ROUTE = 'easyadmin';

    private LoggerInterface $logger;

    private RouterInterface $router;

    private SerializerInterface $serializer;

    public function __construct(
        LoggerInterface $logger,
        SerializerInterface $serializer,
        RouterInterface $router
    ) {
        $this->logger = $logger;
        $this->router = $router;
        $this->serializer = $serializer;
    }

    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();
        $this->logError($exception);

        $request = $event->getRequest();
        if (self::ADMIN_ROUTE === $request->attributes->get('_route')) {
            $session = $request->getSession();
            if ($session instanceof Session) {
                $session->getFlashBag()->set('danger', $exception->getMessage());
            }

            return;
        }

        $response = new JsonResponse(
            $this->serializer->serialize($exception, 'json'),
            $this->getCodeByException($exception),
            [],
            true
        );

        $event->setResponse($response);
    }

    private function logError(\Throwable $exception): void
    {
        if ($exception instanceof HttpExceptionInterface) {
            return;
        }

        $context = [
            'trace' => $exception->getTraceAsString(),
        ];
        $this->logger->error($exception->getMessage(), $context);
    }

    private function getCodeByException(\Throwable $exception): int
    {
        switch (true) {
            case $exception instanceof HttpExceptionInterface:
                return $exception->getStatusCode();
        }

        return Response::HTTP_BAD_REQUEST;
    }
}

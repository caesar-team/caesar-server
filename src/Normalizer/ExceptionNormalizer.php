<?php

declare(strict_types=1);

namespace App\Normalizer;

use Fourxxi\RestRequestError\Exception\FormInvalidRequestException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ExceptionNormalizer implements NormalizerInterface
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        return [
            'error' => [
                'message' => $this->getMessage($object),
                'code' => $this->getCodeByException($object),
            ],
        ];
    }

    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof \Exception && !$data instanceof FormInvalidRequestException;
    }

    private function getCodeByException(\Throwable $exception): int
    {
        switch (true) {
            case $exception instanceof HttpExceptionInterface:
                return $exception->getStatusCode();
        }

        return Response::HTTP_BAD_REQUEST;
    }

    private function getMessage(\Throwable $exception)
    {
        $message = 'Internal server error';
        if ($exception instanceof HttpExceptionInterface) {
            $message = $exception->getMessage();
        }

        return $this->translator->trans($message);
    }
}

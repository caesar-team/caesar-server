<?php

declare(strict_types=1);

namespace App\Normalizer;

use Fourxxi\RestRequestError\Exception\InvalidRequestExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class InvalidRequestExceptionNormalizer implements NormalizerInterface
{
    /**
     * @var NormalizerInterface
     */
    private $normalizer;

    public function __construct(NormalizerInterface $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        $data = $this->normalizer->normalize($object, $format, $context);
        if (!$object instanceof InvalidRequestExceptionInterface) {
            return $data;
        }

        return [
            'code' => $object->getStatusCode(),
            'message' => 'Validation Failed',
            'errors' => $data,
        ];
    }

    public function supportsNormalization($data, string $format = null)
    {
        return $this->normalizer->supportsNormalization($data, $format);
    }
}

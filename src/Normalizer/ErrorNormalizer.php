<?php

declare(strict_types=1);

namespace App\Normalizer;

use InvalidArgumentException;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ErrorNormalizer implements NormalizerInterface
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        return [
            'error' => [
                'message' => $object instanceof FormInterface ? implode('; ', $this->getFormErrors($object)) : [],
                'type' => FormError::class,
                'code' => 0,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof FormInterface && $data->isSubmitted() && !$data->isValid();
    }

    private function getFormErrors(FormInterface $form): array
    {
        $errors = [];
        foreach ($form->getErrors() as $error) {
            if (!$error instanceof FormError) {
                continue;
            }
            $errors[] = $this->getErrorMessage($error);
        }
        foreach ($form->all() as $childForm) {
            if ($childErrors = $this->getFormErrors($childForm)) {
                $errors[$childForm->getName()] = $childErrors;
            }
        }

        return $errors;
    }

    private function getErrorMessage(FormError $error): string
    {
        try {
            return $this->translator->trans($error->getMessageTemplate(), $error->getMessageParameters(), 'validators');
        } catch (InvalidArgumentException $exception) {
            return '';
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Normalizer;

use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class ErrorNormalizer.
 */
class ErrorNormalizer implements NormalizerInterface
{
    /** @var TranslatorInterface */
    private $translator;

    /**
     * ErrorNormalizer constructor.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        return [
            'error' => [
                'message' => $object instanceof FormInterface ? implode("; ", $this->getFormErrors($object)) : [],
                'type' => FormError::class,
                'code' => 0,
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof FormInterface && $data->isSubmitted() && !$data->isValid();
    }

    /**
     * @param FormInterface $form
     *
     * @return array
     */
    private function getFormErrors(FormInterface $form): array
    {
        $errors = [];
        foreach ($form->getErrors() as $error) {
            $errors[] = $this->getErrorMessage($error);
        }
        foreach ($form->all() as $childForm) {
            if ($childForm instanceof FormInterface) {
                if ($childErrors = $this->getFormErrors($childForm)) {
                    $errors[$childForm->getName()] = $childErrors;
                }
            }
        }

        return $errors;
    }

    /**
     * @param FormError $error
     *
     * @return string
     */
    private function getErrorMessage(FormError $error): string
    {
        try {
            if (null !== $error->getMessagePluralization()) {
                return $this->translator->transChoice($error->getMessageTemplate(), $error->getMessagePluralization(), $error->getMessageParameters(), 'validators');
            }

            return $this->translator->trans($error->getMessageTemplate(), $error->getMessageParameters(), 'validators');
        } catch (\InvalidArgumentException $exception) {
            return '';
        }
    }
}

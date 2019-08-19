<?php

declare(strict_types=1);

namespace App\Factory\Validator;

use App\Webauthn\Response\WebauthnResponseInterface;
use App\Validator\Webauthn\ResponseValidatorInterface;

final class WebauthnResponseValidatorFactory
{
    /**
     * @var ResponseValidatorInterface[]
     */
    private $responseValidators;

    public function __construct(ResponseValidatorInterface ...$responseValidators)
    {
        $this->responseValidators = $responseValidators;
    }

    public function check(WebauthnResponseInterface $response): ?ResponseValidatorInterface
    {
        foreach ($this->responseValidators as $responseValidator) {
            if ($responseValidator->canCheck($response)) {
                $responseValidator->check($response);



                return $responseValidator;
            }
        }

        return null;
    }
}
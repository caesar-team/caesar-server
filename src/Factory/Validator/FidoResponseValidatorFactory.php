<?php

declare(strict_types=1);

namespace App\Factory\Validator;

use App\Fido\Response\FidoResponseInterface;
use App\Validator\Fido\ResponseValidatorInterface;

final class FidoResponseValidatorFactory
{
    /**
     * @var ResponseValidatorInterface[]
     */
    private $responseValidators;

    public function __construct(ResponseValidatorInterface ...$responseValidators)
    {
        $this->responseValidators = $responseValidators;
    }

    public function check(FidoResponseInterface $response): ?ResponseValidatorInterface
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
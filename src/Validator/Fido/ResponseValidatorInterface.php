<?php

declare(strict_types=1);

namespace App\Validator\Fido;

use App\Fido\Response\FidoResponseInterface;

interface ResponseValidatorInterface
{
    public function canCheck(FidoResponseInterface $response): bool;
    public function check(FidoResponseInterface $response): void;
}
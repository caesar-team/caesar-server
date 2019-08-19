<?php

declare(strict_types=1);


namespace App\Webauthn\Response;

interface WebauthnResponseInterface
{
    /**
     * @return string
     */
    public function getData(): string ;

    /**
     * @return mixed
     */
    public function getOptions(): \JsonSerializable;
}
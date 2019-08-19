<?php

declare(strict_types=1);


namespace App\Fido\Response;

interface FidoResponseInterface
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
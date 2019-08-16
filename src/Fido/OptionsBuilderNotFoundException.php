<?php

declare(strict_types=1);

namespace App\Fido;

final class OptionsBuilderNotFoundException extends \LogicException
{
    private const DEFAULT_MESSAGE = 'No options builders found';
    public function __construct($message = self::DEFAULT_MESSAGE, $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
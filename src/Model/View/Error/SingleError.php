<?php

declare(strict_types=1);

namespace App\Model\View\Error;

class SingleError
{
    /**
     * @var array
     */
    protected $errors;

    public function __construct(array $errors)
    {
        $this->errors = $errors;
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}

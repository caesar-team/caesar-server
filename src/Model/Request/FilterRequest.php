<?php

declare(strict_types=1);

namespace App\Model\Request;

use Symfony\Component\HttpFoundation\Request;

class FilterRequest
{
    private string $email = '';

    public function __construct()
    {
        $this->email = '';
    }

    public static function createFromRequest(Request $request): self
    {
        $filter = new self();
        $filter->email = $request->get('email', '');

        return $filter;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }
}

<?php

declare(strict_types=1);

namespace App\Request;

interface SrpAwareRequestInterface
{
    public function getSeed(): ?string;

    public function getVerifier(): ?string;
}

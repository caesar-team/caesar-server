<?php

declare(strict_types=1);

namespace App\Request;

use App\Entity\Directory\AbstractDirectory;

interface EditListRequestInterface
{
    public function getLabel(): ?string;

    public function getDirectory(): AbstractDirectory;
}

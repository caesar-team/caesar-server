<?php

namespace App\Utils;

interface ChildItemAwareInterface
{
    public function getCause(): ?string;
}

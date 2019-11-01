<?php

declare(strict_types=1);

namespace App\Model\DTO\Message;

interface MessageInterface
{
    public function getTemplate(): ?string;
    public function getRecipients(): array;
    public function getContent(): ?string;
}
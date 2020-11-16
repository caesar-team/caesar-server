<?php

declare(strict_types=1);

namespace App\Request\Item;

final class ItemMetaRequest
{
    private ?int $attachCount;

    private ?string $webSite;

    public function __construct()
    {
        $this->attachCount = null;
        $this->webSite = null;
    }

    public function getAttachCount(): ?int
    {
        return $this->attachCount;
    }

    public function setAttachCount(?int $attachCount): void
    {
        $this->attachCount = $attachCount;
    }

    public function getWebSite(): ?string
    {
        return $this->webSite;
    }

    public function setWebSite(?string $webSite): void
    {
        $this->webSite = $webSite;
    }
}

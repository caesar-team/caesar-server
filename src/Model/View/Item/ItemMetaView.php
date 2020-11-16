<?php

declare(strict_types=1);

namespace App\Model\View\Item;

use Swagger\Annotations as SWG;

final class ItemMetaView
{
    /**
     * @SWG\Property(type="integer", example=1)
     */
    private int $attachCount;

    /**
     * @SWG\Property(type="string", example="http://example.com")
     */
    private ?string $webSite;

    public function getAttachCount(): int
    {
        return $this->attachCount;
    }

    public function setAttachCount(int $attachCount): void
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

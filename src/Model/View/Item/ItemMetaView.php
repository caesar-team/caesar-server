<?php

declare(strict_types=1);

namespace App\Model\View\Item;

use Swagger\Annotations as SWG;

final class ItemMetaView
{
    /**
     * @SWG\Property(type="integer", example=1)
     */
    private int $attachmentsCount;

    /**
     * @SWG\Property(type="string", example="http://example.com")
     */
    private ?string $website;

    /**
     * @SWG\Property(type="string")
     */
    private ?string $title;

    public function getAttachmentsCount(): int
    {
        return $this->attachmentsCount;
    }

    public function setAttachmentsCount(int $attachmentsCount): void
    {
        $this->attachmentsCount = $attachmentsCount;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): void
    {
        $this->website = $website;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }
}

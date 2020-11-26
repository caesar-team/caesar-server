<?php

declare(strict_types=1);

namespace App\Entity\Embedded;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Embeddable
 */
class ItemMeta
{
    /**
     * @ORM\Column(type="integer", options={"default"=0})
     */
    private int $attachmentsCount;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $website;

    /**
     * @ORM\Column(nullable=true)
     */
    private ?string $title;

    public function __construct(int $attachmentsCount = 0, ?string $website = null, ?string $title = null)
    {
        $this->attachmentsCount = $attachmentsCount;
        $this->website = $website;
        $this->title = $title;
    }

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

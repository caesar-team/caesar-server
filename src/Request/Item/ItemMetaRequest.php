<?php

declare(strict_types=1);

namespace App\Request\Item;

use Symfony\Component\Validator\Constraints as Assert;

final class ItemMetaRequest
{
    private ?int $attachmentsCount;

    private ?string $website;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(max="255")
     */
    private ?string $title;

    public function __construct()
    {
        $this->attachmentsCount = null;
        $this->website = null;
    }

    public function getAttachmentsCount(): ?int
    {
        return $this->attachmentsCount;
    }

    public function setAttachmentsCount(?int $attachmentsCount): void
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

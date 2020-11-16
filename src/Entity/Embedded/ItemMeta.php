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
    private int $attachCount;

    /**
     * @ORM\Column(nullable=true)
     */
    private ?string $webSite;

    public function __construct(int $attachCount = 0, ?string $webSite = null)
    {
        $this->attachCount = $attachCount;
        $this->webSite = $webSite;
    }

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

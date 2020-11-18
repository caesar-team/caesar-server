<?php

declare(strict_types=1);

namespace App\Request\Item;

use App\Entity\Item;
use App\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

final class EditItemRequest
{
    private ?User $owner;

    /**
     * @Assert\NotBlank()
     */
    private ?string $title;

    /**
     * @Assert\NotBlank()
     */
    private ?string $secret;

    /**
     * @Assert\Valid
     */
    private ItemMetaRequest $meta;

    private ?string $raws;

    private array $tags;

    private Item $item;

    public function __construct(Item $item)
    {
        $this->tags = $item->getTags()->toArray();
        $this->secret = $item->getSecret();
        $this->title = $item->getTitle();
        $this->owner = $item->getSignedOwner();
        $this->item = $item;
        $this->meta = new ItemMetaRequest();
        $this->meta->setAttachCount($item->getMeta()->getAttachCount());
        $this->meta->setWebSite($item->getMeta()->getWebSite());
        $this->raws = $item->getRaws();
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): void
    {
        $this->owner = $owner;
    }

    public function getSecret(): ?string
    {
        return $this->secret;
    }

    public function setSecret(?string $secret): void
    {
        $this->secret = $secret;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function setTags(array $tags): void
    {
        $this->tags = $tags;
    }

    public function getItem(): Item
    {
        return $this->item;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getMeta(): ItemMetaRequest
    {
        return $this->meta;
    }

    public function setMeta(ItemMetaRequest $meta): void
    {
        $this->meta = $meta;
    }

    public function getRaws(): ?string
    {
        return $this->raws;
    }

    public function setRaws(?string $raws): void
    {
        $this->raws = $raws;
    }
}

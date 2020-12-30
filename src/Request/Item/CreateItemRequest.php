<?php

declare(strict_types=1);

namespace App\Request\Item;

use App\Entity\Directory\AbstractDirectory;
use App\Entity\Directory\TeamDirectory;
use App\Entity\Team;
use App\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

final class CreateItemRequest
{
    private ?User $owner;

    private ?AbstractDirectory $list;

    /**
     * @Assert\NotBlank()
     */
    private ?string $type;

    /**
     * @Assert\Valid
     */
    private ItemMetaRequest $meta;

    /**
     * @Assert\NotBlank()
     */
    private ?string $secret;

    private ?string $raws;

    private array $tags;

    private User $user;

    public function __construct(User $user)
    {
        $this->owner = $user;
        $this->user = $user;
        $this->list = null;
        $this->type = null;
        $this->secret = null;
        $this->meta = new ItemMetaRequest();
        $this->raws = null;
        $this->tags = [];
    }

    public function getOwner(): ?User
    {
        return $this->owner ?? $this->getUser();
    }

    public function setOwner(?User $owner): void
    {
        $this->owner = $owner;
    }

    public function getList(): ?AbstractDirectory
    {
        return $this->list;
    }

    public function setList(?AbstractDirectory $list): void
    {
        $this->list = $list;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): void
    {
        $this->type = $type;
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

    public function getUser(): User
    {
        return $this->user;
    }

    public function getTeam(): ?Team
    {
        $list = $this->getList();

        return $list instanceof TeamDirectory ? $list->getTeam() : null;
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

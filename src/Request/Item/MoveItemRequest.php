<?php

declare(strict_types=1);

namespace App\Request\Item;

use App\Entity\Directory;
use App\Entity\Item;
use App\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

final class MoveItemRequest implements MoveItemRequestInterface
{
    /**
     * @Assert\NotBlank
     */
    private ?Directory $list;

    private ?string $secret;

    private ?string $raws;

    private User $user;

    private Item $item;

    public function __construct(Item $item, User $user)
    {
        $this->list = null;
        $this->secret = null;
        $this->raws = null;
        $this->user = $user;
        $this->item = $item;
    }

    public function getList(): ?Directory
    {
        return $this->list;
    }

    public function setList(?Directory $list): void
    {
        $this->list = $list;
    }

    public function getSecret(): ?string
    {
        return $this->secret;
    }

    public function setSecret(?string $secret): void
    {
        $this->secret = $secret;
    }

    public function getRaws(): ?string
    {
        return $this->raws;
    }

    public function setRaws(?string $raws): void
    {
        $this->raws = $raws;
    }

    public function getItem(): Item
    {
        return $this->item;
    }

    /**
     * @Assert\Callback
     */
    public function callback(ExecutionContextInterface $context)
    {
        if (null === $this->getList()) {
            return;
        }

        if (null !== $this->getList()->getTeam()) {
            return;
        }

        if (!$this->user->isOwnerByDirectory($this->getList())) {
            $context->buildViolation('item.move.invalid_list')
                ->atPath('listId')
                ->addViolation()
            ;
        }
    }
}

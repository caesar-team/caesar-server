<?php

declare(strict_types=1);

namespace App\Request\Item;

use App\Entity\Directory;
use App\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

final class MoveItemRequest
{
    /**
     * @Assert\NotBlank
     */
    private ?Directory $list;

    private User $user;

    public function __construct(User $user)
    {
        $this->list = null;
        $this->user = $user;
    }

    public function getList(): ?Directory
    {
        return $this->list;
    }

    public function setList(?Directory $list): void
    {
        $this->list = $list;
    }

    public function getUser(): User
    {
        return $this->user;
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

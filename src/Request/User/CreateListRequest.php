<?php

declare(strict_types=1);

namespace App\Request\User;

use App\DBAL\Types\Enum\DirectoryEnumType;
use App\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

final class CreateListRequest
{
    /**
     * @Assert\NotBlank
     */
    private ?string $label;

    private string $type;

    private ?int $sort;

    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->type = DirectoryEnumType::LIST;
        $this->sort = 0;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): void
    {
        $this->label = $label;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getSort(): ?int
    {
        return $this->sort;
    }

    public function setSort(?int $sort): void
    {
        $this->sort = $sort;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @Assert\Callback
     */
    public function uniqueValidation(ExecutionContextInterface $context): void
    {
        if (null === $this->getUser()->getDirectoryByLabel($this->getLabel())) {
            return;
        }

        $context->buildViolation('list.create.label.already_exists')
            ->atPath('label')
            ->addViolation()
        ;
    }
}

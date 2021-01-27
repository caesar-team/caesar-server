<?php

declare(strict_types=1);

namespace App\Request\Team;

use App\DBAL\Types\Enum\DirectoryEnumType;
use App\Entity\Team;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

final class CreateListRequest
{
    /**
     * @Assert\NotBlank()
     */
    private ?string $label;

    private string $type;

    private Team $team;

    public function __construct(Team $team)
    {
        $this->team = $team;
        $this->type = DirectoryEnumType::LIST;
        $this->label = '';
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): void
    {
        $this->label = $label;
    }

    public function getTeam(): Team
    {
        return $this->team;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @Assert\Callback
     */
    public function uniqueValidation(ExecutionContextInterface $context)
    {
        if ($this->team->getDirectoryByLabel($this->getLabel())) {
            $context->buildViolation('list.create.label.already_exists')
                ->atPath('label')
                ->addViolation()
            ;
        }
    }
}

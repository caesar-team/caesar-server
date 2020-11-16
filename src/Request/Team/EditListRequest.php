<?php

declare(strict_types=1);

namespace App\Request\Team;

use App\Entity\Directory;
use App\Request\EditListRequestInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

final class EditListRequest implements EditListRequestInterface
{
    /**
     * @Assert\NotBlank()
     */
    private ?string $label;

    private Directory $directory;

    public function __construct(Directory $directory)
    {
        $this->directory = $directory;
        $this->label = $directory->getLabel();
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): void
    {
        $this->label = $label;
    }

    /**
     * @Assert\Callback
     */
    public function uniqueValidation(ExecutionContextInterface $context)
    {
        $list = $this->getDirectory()->getTeam()->getDirectoryByLabel($this->getLabel());

        if ($list && !$this->getDirectory()->equals($list)) {
            $context->buildViolation('list.create.label.already_exists')
                ->atPath('label')
                ->addViolation()
            ;
        }
    }

    public function getDirectory(): Directory
    {
        return $this->directory;
    }
}

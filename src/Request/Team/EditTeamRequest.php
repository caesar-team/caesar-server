<?php

declare(strict_types=1);

namespace App\Request\Team;

use App\Entity\Team;
use App\Validator\Constraints\UniqueEntityProperty;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

final class EditTeamRequest
{
    /**
     * @var string|null
     *
     * @UniqueEntityProperty(
     *     entityClass="App\Entity\Team",
     *     field="title",
     *     currentEntityExpression="this.getTeam()",
     *     message="team.label.unique"
     * )
     */
    private $title;

    /**
     * @var string|null
     */
    private $icon;

    private Team $team;

    public function __construct(Team $team)
    {
        $this->team = $team;
        $this->title = $team->getTitle();
        $this->icon = $team->getIcon();
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): void
    {
        $this->icon = $icon;
    }

    public function getTeam(): Team
    {
        return $this->team;
    }

    /**
     * @Assert\Callback
     */
    public function callback(ExecutionContextInterface $context)
    {
        if (Team::DEFAULT_GROUP_ALIAS !== $this->team->getAlias()) {
            if (empty($this->title)) {
                $context
                    ->buildViolation('This value should not be blank.')
                    ->atPath('title')
                    ->addViolation()
                ;
            }

            return;
        }

        if ($this->title !== $this->team->getTitle()) {
            $context->buildViolation('team.title.default')
                ->atPath('title')
                ->addViolation()
            ;
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Services\Billing;

use App\Entity\Billing\Plan;
use App\Model\DTO\BillingRemains;
use App\Repository\ItemRepository;
use App\Repository\PlanRepository;
use App\Repository\TeamRepository;
use App\Repository\UserRepository;

final class BillingHelper
{
    /**
     * @var ItemRepository
     */
    private $itemRepository;
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var Plan
     */
    private $plan;
    /**
     * @var TeamRepository
     */
    private $teamRepository;

    public function __construct(
        ItemRepository $itemRepository,
        UserRepository $userRepository,
        PlanRepository $planRepository,
        TeamRepository $teamRepository
    )
    {
        $this->itemRepository = $itemRepository;
        $this->userRepository = $userRepository;
        $this->teamRepository = $teamRepository;
        $this->plan = $planRepository->findOneByActive(true);

        if(is_null($this->plan)) {
            throw new \LogicException('An active plan must be defined');
        }
    }

    /**
     * @return BillingRemains
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getRemains(): BillingRemains
    {
        $usersCount = $this->userRepository->getCountCompleted();
        $itemsCount = $this->itemRepository->getCount();
        $memoryUsed = $this->itemRepository->getSecretsSum();
        $teamsCount = $this->teamRepository->getCount();

        $remainingUsers = $this->hasRestriction(Plan::FIELD_USERS_LIMIT) ? $this->plan->getUsersLimit() - $usersCount : null;
        $remainingItems = $this->hasRestriction(Plan::FIELD_ITEMS_LIMIT) ? $this->plan->getItemsLimit() - $itemsCount : null;
        $remainingMemory = $this->hasRestriction(Plan::FIELD_MEMORY_LIMIT) ? $this->plan->getMemoryLimit() - $memoryUsed : null;
        $remainingTeams = $this->hasRestriction(Plan::FIELD_TEAMS_LIMIT) ? $this->plan->getTeamsLimit() - $teamsCount : null;

        return new BillingRemains($this->plan->getName(), $remainingUsers, $remainingItems, $remainingMemory, $remainingTeams);
    }

    public function hasRestriction(string $attribute): bool
    {
        switch($attribute) {
            case Plan::FIELD_USERS_LIMIT:
                return 0 < $this->plan->getUsersLimit();
                break;
            case Plan::FIELD_ITEMS_LIMIT:
                return 0 < $this->plan->getItemsLimit();
                break;
            case Plan::FIELD_TEAMS_LIMIT:
                return 0 < $this->plan->getTeamsLimit();
                break;
            case Plan::FIELD_MEMORY_LIMIT:
                return 0 < $this->plan->getMemoryLimit();
            break;
        }

        throw new \LogicException("This code should not be reached!");
    }
}
<?php

declare(strict_types=1);

namespace App\Services\Billing;

use App\Entity\Billing\Plan;
use App\Repository\PlanRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use App\Model\DTO\UserSubscription;
use Exception;

final class SubscriptionManager
{
    /**
     * @var PlanRepository
     */
    private $planRepository;

    public function __construct(PlanRepository $planRepository)
    {
        $this->planRepository = $planRepository;
    }

    /**
     * @throws ORMException
     */
    public function prepareToSubscription()
    {
        /** @var Plan[] $plans */
        $plans = $this->planRepository->findAll();
        foreach ($plans as $plan) {
            $this->planRepository->remove($plan);
        }
    }

    /**
     * @param UserSubscription $userSubscription
     * @return Plan
     * @throws Exception
     */
    public function createPlan(UserSubscription $userSubscription): Plan
    {
        $newPlan = new Plan();
        $newPlan->setActive(UserSubscription::STATUS_ACTIVE === $userSubscription->getStatus());
        $newPlan->setName($userSubscription->getSubscriptionName());
        $itemsLimit = 0 < (int)$userSubscription->getItemsLimit() ? (int)$userSubscription->getItemsLimit() : -1;
        $newPlan->setItemsLimit($itemsLimit);
        $memoryLimit = 0 < (int)$userSubscription->getMemoryLimit() ? (int)$userSubscription->getMemoryLimit() : -1;
        $newPlan->setMemoryLimit($memoryLimit);
        $teamsLimit = 0 < (int)$userSubscription->getTeamsLimit() ? (int)$userSubscription->getTeamsLimit() : -1;
        $newPlan->setTeamsLimit($teamsLimit);
        $usersLimit = 0 < (int)$userSubscription->getUsersLimit() ? (int)$userSubscription->getUsersLimit() : -1;
        $newPlan->setUsersLimit($usersLimit);
        $newPlan->setUserSubscriptionId($userSubscription->getExternalSubscriptionId());
        $newPlan->setSubscriptionId($userSubscription->getId());
        $newPlan->setSubscribedAt(new \DateTimeImmutable($userSubscription->getSubscribedAt()));

        return $newPlan;
    }

    /**
     * @param Plan $plan
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function applyPlan(Plan $plan)
    {
        $this->planRepository->persist($plan);
        $this->planRepository->flush();
    }
}
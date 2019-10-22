<?php

declare(strict_types=1);

namespace App\Event\EventSubscriber;

use App\Entity\Billing\Plan;
use App\Repository\PlanRepository;
use App\Validator\Constraints\BillingRestriction;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use http\Exception\RuntimeException;
use Symfony\Component\Validator\Exception\InvalidArgumentException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class LimitsReachedSubscriber implements EventSubscriber
{
    /**
     * @var ValidatorInterface
     */
    private $validator;
    /**
     * @var PlanRepository
     */
    private $planRepository;

    public function __construct(ValidatorInterface $validator, PlanRepository $planRepository)
    {
        $this->validator = $validator;
        $this->planRepository = $planRepository;
    }

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return string[]
     */
    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
        ];
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if ($entity instanceof Plan) {
            return;
        }

        /** @var Plan $plan */
        $plan = $this->planRepository->findOneByActive(true);

        if ($plan && ($plan->isExpired() || !$plan->isActive())) {
            throw new \RuntimeException('Subscription is expired.');
        }

        $billingRestriction = new BillingRestriction();
        $errors = $this->validator->validate($entity, $billingRestriction);
        if (0 < count($errors)) {
            throw new InvalidArgumentException($errors[0]->getMessage());
        }
    }
}
<?php

declare(strict_types=1);

namespace App\Controller\Api\Billing;

use App\Controller\AbstractController;
use App\Entity\Billing\Plan;
use App\Model\DTO\UserSubscription;
use App\Repository\PlanRepository;
use App\Repository\UserRepository;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\Serializer\SerializerInterface;

class SubscriptionController extends AbstractController
{
    /**
     * @SWG\Tag(name="Subscription")
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=\App\Model\DTO\UserSubscription::class)
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Handle and promote access to the project under a received subscription"
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     *
     * @Route(path="/api/billing/grant", methods={"POST"})
     * @param Request $request
     * @param UserRepository $userRepository
     * @param PlanRepository $planRepository
     * @param SerializerInterface $serializer
     * @return Plan|FormInterface|null
     * @throws \Exception
     */
    public function grant(
        Request $request,
        UserRepository $userRepository,
        PlanRepository $planRepository,
        SerializerInterface $serializer
    )
    {
        $userSubscription = $serializer->deserialize($request->getContent(), UserSubscription::class, 'json');

        if (!$userSubscription instanceof UserSubscription) {
            return null;
        }

        //find an user by a request
        $user = $userRepository->findOneByEmail($userSubscription->getUser()->getEmail());
        if (!$user) {
            return null;
        }

        /** @var Plan[] $plans */
        $plans = $planRepository->findAll();
        foreach ($plans as $plan) {
            $planRepository->remove($plan);
        }

        $newPlan = new Plan();
        $newPlan->setActive(true);
        $newPlan->setName($userSubscription->getSubscriptionName());
        $itemsLimit = 0 < (int)$userSubscription->getItemsLimit() ? (int)$userSubscription->getItemsLimit() : -1;
        $newPlan->setItemsLimit($itemsLimit);
        $memoryLimit = 0 < (int)$userSubscription->getMemoryLimit() ? (int)$userSubscription->getMemoryLimit() : -1;
        $newPlan->setMemoryLimit($memoryLimit);
        $teamsLimit = 0 < (int)$userSubscription->getTeamsLimit() ? (int)$userSubscription->getTeamsLimit() : -1;
        $newPlan->setTeamsLimit($teamsLimit);
        $newPlan->setUserSubscriptionId($userSubscription->getExternalSubscriptionId());
        $newPlan->setSubscriptionId($userSubscription->getId());

        $planRepository->persist($newPlan);
        $planRepository->flush();

        return $newPlan;
    }
}
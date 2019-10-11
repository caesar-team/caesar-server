<?php

declare(strict_types=1);

namespace App\Controller\Api\Billing;

use App\Controller\AbstractController;
use App\DBAL\Types\Enum\BillingEnumType;
use App\Entity\Billing\Plan;
use App\Form\Request\Billing\SubscriptionGrantType;
use App\Model\DTO\UserSubscription;
use App\Repository\PlanRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
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
     * @return Plan|FormInterface|null
     * @throws NonUniqueResultException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function grant(
        Request $request,
        UserRepository $userRepository,
        PlanRepository $planRepository
    )
    {
        $userSubscription = new UserSubscription();
        $form = $this->createForm(SubscriptionGrantType::class, $userSubscription);

        $form->submit($request->request->all());

        if (!$form->isValid()) {
            return $form;
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
        $newPlan->setName(BillingEnumType::TYPE_EXPANDED);
        $newPlan->setItemsLimit(100);
        $newPlan->setMemoryLimit(-1);
        $newPlan->setUsersLimit(50);

        $planRepository->persist($newPlan);
        $planRepository->flush();

        return $newPlan;
    }
}
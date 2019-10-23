<?php

declare(strict_types=1);

namespace App\Controller\Api\Billing;

use App\Controller\AbstractController;
use App\Entity\Billing\Plan;
use App\Form\Request\Billing\SubscriptionGrantType;
use App\Model\DTO\UserSubscription;
use App\Services\Billing\SubscriptionManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;

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
     * @param SubscriptionManager $subscriptionManager
     * @return Plan|FormInterface|null
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \Exception
     */
    public function grant(
        Request $request,
        SubscriptionManager $subscriptionManager
    )
    {
        $subscriptionGrant = new UserSubscription();
        $form = $this->createForm(SubscriptionGrantType::class, $subscriptionGrant);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $form;
        }

        $subscriptionManager->prepareToSubscription();
        $newPlan = $subscriptionManager->createPlan($subscriptionGrant);

        $subscriptionManager->applyPlan($newPlan);

        return $newPlan;
    }
}
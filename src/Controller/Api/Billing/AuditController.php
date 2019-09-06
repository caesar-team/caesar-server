<?php

declare(strict_types=1);

namespace App\Controller\Api\Billing;

use App\Controller\AbstractController;
use App\Services\Billing\BillingHelper;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;

/**
 * @Route(path="/api/audit")
 */
final class AuditController extends AbstractController
{
    /**
     * @SWG\Tag(name="Audit")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Full list tree with items",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type="\App\Model\View\Audit\AuditView")
     *     )
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     *
     * @Route(path="/status", methods={"GET"})
     * @param BillingHelper $billingHelper
     * @return array
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function status(BillingHelper $billingHelper)
    {
        $remains = $billingHelper->getRemains();

        return $remains;
    }
}
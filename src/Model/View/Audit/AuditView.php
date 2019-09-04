<?php

declare(strict_types=1);

namespace App\Model\View\Audit;

use App\Entity\Billing\Audit;

final class AuditView
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var int
     */
    public $remainingUsers;
    /**
     * @var int
     */
    public $remainingItems;
    /**
     * @var int
     */
    public $remainingMemory;
    /**
     * @var string
     */
    public $billingType;
    /**
     * @var \DateTimeImmutable
     */
    public $createdAt;
    /**
     * @var \DateTimeImmutable
     */
    public $updatedAt;

    public static function create(Audit $audit): self
    {
        $view = new self();
        $plan = $audit->getBillingPlan();
        $view->id = $audit->getId()->toString();
        $view->billingType = $audit->getBillingType();
        $view->createdAt = $audit->getCreatedAt();
        $view->updatedAt = $audit->getUpdatedAt();
        $view->remainingUsers = $plan->getUsersLimit() - $audit->getUsersCount();
        $view->remainingItems = $plan->getItemsLimit() - $audit->getItemsCount();
        $view->remainingMemory = $plan->getMemoryLimit() - $audit->getMemoryUsed();

        return $view;
    }
}
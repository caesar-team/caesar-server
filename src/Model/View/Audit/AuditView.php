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
     * @var string
     */
    public $billingType;
    /**
     * @var int
     */
    public $usersCount;
    /**
     * @var int
     */
    public $itemsCount;
    /**
     * @var int
     */
    public $memoryUsed;
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
        $view->id = $audit->getId()->toString();
        $view->usersCount = $audit->getUsersCount();
        $view->itemsCount = $audit->getItemsCount();
        $view->billingType = $audit->getBillingType();
        $view->memoryUsed = $audit->getMemoryUsed();
        $view->createdAt = $audit->getCreatedAt();
        $view->updatedAt = $audit->getUpdatedAt();

        return $view;
    }
}
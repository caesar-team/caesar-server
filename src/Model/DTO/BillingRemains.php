<?php

declare(strict_types=1);

namespace App\Model\DTO;

final class BillingRemains
{
    /**
     * @var string
     */
    public $billingName;
    /**
     * @var int|null
     */
    public $remainingUsers;
    /**
     * @var int|null
     */
    public $remainingItems;
    /**
     * @var int|null
     */
    public $remainingMemory;

    public function __construct(string $billingName, ?int $remainingUsers, ?int $remainingItems, ?int $remainingMemory)
    {
        $this->billingName = $billingName;
        $this->remainingUsers = $remainingUsers;
        $this->remainingItems = $remainingItems;
        $this->remainingMemory = $remainingMemory;
    }
}
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
    public $remainingStorage;
    /**
     * @var int|null
     */
    public $remainingTeams;

    public function __construct(
        string $billingName,
        ?int $remainingUsers,
        ?int $remainingItems,
        ?int $remainingStorage,
        ?int $remainingTeams
    )
    {
        $this->billingName = $billingName;
        $this->remainingUsers = $remainingUsers;
        $this->remainingItems = $remainingItems;
        $this->remainingStorage = $remainingStorage;
        $this->remainingTeams = $remainingTeams;
    }
}
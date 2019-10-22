<?php

declare(strict_types=1);

namespace App\Security\Voter\Quotes;

use App\Entity\Item;
use App\Model\DTO\BillingRemains;
use App\Security\Voter\QuotesVoterInterface;

final class MemoryQuotesVoter implements QuotesVoterInterface
{
    public function supports($subject): bool
    {
        return $subject instanceof Item;
    }

    /**
     * @param Item $subject
     * @param BillingRemains $billingRemains
     * @return bool
     */
    public function vote($subject, BillingRemains $billingRemains): bool
    {
        if (is_null($billingRemains->remainingMemory)) {
            return true;
        }

        return strlen($subject->getSecret()) < $billingRemains->remainingMemory;
    }
}
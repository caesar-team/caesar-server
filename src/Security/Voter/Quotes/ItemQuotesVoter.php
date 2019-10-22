<?php

declare(strict_types=1);

namespace App\Security\Voter\Quotes;

use App\Entity\Item;
use App\Model\DTO\BillingRemains;
use App\Security\Voter\QuotesVoterInterface;

final class ItemQuotesVoter implements QuotesVoterInterface
{
    public function supports($subject): bool
    {
        return $subject instanceof Item;
    }

    public function vote($subject, BillingRemains $billingRemains): bool
    {
        if (is_null($billingRemains->remainingItems)) {
            return true;
        }

        return 0 < $billingRemains->remainingItems;
    }
}
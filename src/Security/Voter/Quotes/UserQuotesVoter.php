<?php

declare(strict_types=1);

namespace App\Security\Voter\Quotes;

use App\Entity\User;
use App\Model\DTO\BillingRemains;
use App\Security\Voter\QuotesVoterInterface;

final class UserQuotesVoter implements QuotesVoterInterface
{
    public function supports($subject): bool
    {
        return $subject instanceof User;
    }

    /**
     * @param User $subject
     * @param BillingRemains $billingRemains
     * @return bool
     */
    public function vote($subject, BillingRemains $billingRemains): bool
    {
        if (is_null($billingRemains->remainingUsers)) {
            return true;
        }

        return 0 < $billingRemains->remainingUsers;
    }
}
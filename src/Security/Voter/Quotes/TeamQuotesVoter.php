<?php

declare(strict_types=1);

namespace App\Security\Voter\Quotes;

use App\Entity\Team;
use App\Model\DTO\BillingRemains;
use App\Security\Voter\QuotesVoterInterface;

final class TeamQuotesVoter implements QuotesVoterInterface
{
    public function supports($subject): bool
    {
        return $subject instanceof Team;
    }

    /**
     * @param Team $subject
     * @param BillingRemains $billingRemains
     * @return bool
     */
    public function vote($subject, BillingRemains $billingRemains): bool
    {
        if (is_null($billingRemains->remainingTeams)) {
            return true;
        }

        return 0 < $billingRemains->remainingTeams;
    }
}
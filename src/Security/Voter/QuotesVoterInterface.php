<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Model\DTO\BillingRemains;

interface QuotesVoterInterface
{
    public function supports($subject): bool;
    public function vote($subject, BillingRemains $billingRemains): bool;
}
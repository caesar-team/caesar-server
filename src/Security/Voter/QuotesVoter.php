<?php

declare(strict_types=1);


namespace App\Security\Voter;


use App\Entity\Item;
use App\Entity\Team;
use App\Services\Billing\BillingHelper;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class QuotesVoter extends Voter
{
    public const ADD_LIMITED = 'quotes_add_limited';
    /**
     * @var BillingHelper
     */
    private $billingHelper;
    /**
     * @var QuotesVoterInterface[]
     */
    private $quotesVoters;

    public function __construct(BillingHelper $billingHelper, QuotesVoterInterface ...$quotesVoters)
    {
        $this->billingHelper = $billingHelper;
        $this->quotesVoters = $quotesVoters;
    }

    /**
     * Determines if the attribute and subject are supported by this voter.
     *
     * @param string $attribute An attribute
     * @param mixed $subject The subject to secure, e.g. an object the user wants to access or any other PHP type
     *
     * @return bool True if the attribute and subject are supported, false otherwise
     */
    protected function supports($attribute, $subject)
    {
        if (self::ADD_LIMITED !== $attribute) {
            return false;
        }

        return $subject instanceof Item || $subject instanceof Team;
    }

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     * It is safe to assume that $attribute and $subject already passed the "supports()" method check.
     *
     * @param string $attribute
     * @param Item|Team $subject
     * @param TokenInterface $token
     *
     * @return bool
     * @throws NonUniqueResultException
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        $remains = $this->billingHelper->getRemains();

        foreach ($this->quotesVoters as $quotesVoter) {
            if (!$quotesVoter->supports($subject)) {
                continue;
            }

            if (!$quotesVoter->vote($subject, $remains)) {
                return false;
            }
        }

        return true;
    }
}
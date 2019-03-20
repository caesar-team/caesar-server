<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Security\Invitation;
use Doctrine\ORM\EntityRepository;

class InvitationRepository extends EntityRepository
{
    /**
     * @param string $hash
     * @return Invitation|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneFreshByHash(string $hash): ?Invitation
    {
        $qb = $this->createQueryBuilder('invitation');
        $qb
            ->where('invitation.hash =:hash')
            ->setParameter('hash', $hash)
            ->setMaxResults(1)
        ;
        $invitation = $qb->getQuery()->getOneOrNullResult();
        if ($invitation instanceof Invitation && !$this->isFresh($invitation)) {
            $invitation = null;
        }

        return $invitation;
    }

    private function isFresh(Invitation $invitation): bool
    {
        $startdate = $invitation->getCreatedAt()->format('Y-m-d H:i:s');
        $expire = strtotime($startdate.$invitation->getShelfLife());
        $now = strtotime("now");
        return ($now >= $expire) ? false : true;
    }
}
<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Fingerprint;
use App\Entity\User;
use App\Security\Fingerprint\FingerprintRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Fingerprint|null find($id, $lockMode = null, $lockVersion = null)
 * @method Fingerprint|null findOneBy(array $criteria, array $orderBy = null)
 * @method Fingerprint[]    findAll()
 * @method Fingerprint[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FingerprintRepository extends ServiceEntityRepository implements FingerprintRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Fingerprint::class);
    }

    public function save(Fingerprint $fingerprint): void
    {
        $this->getEntityManager()->persist($fingerprint);
        $this->getEntityManager()->flush();
    }

    public function getFingerprint(User $user, string $fingerprint): ?Fingerprint
    {
        return $this->findOneBy(['user' => $user, 'fingerprint' => $fingerprint]);
    }

    public function removeFingerprints(User $user): void
    {
        $this
            ->createQueryBuilder('fingerprint')
            ->delete()
            ->where('fingerprint.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->execute()
        ;
    }
}

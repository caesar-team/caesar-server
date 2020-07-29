<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\SystemLimit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method SystemLimit|null find($id, $lockMode = null, $lockVersion = null)
 * @method SystemLimit|null findOneBy(array $criteria, array $orderBy = null)
 * @method SystemLimit[]    findAll()
 * @method SystemLimit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SystemLimitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SystemLimit::class);
    }

    /**
     * @param string[] $aliases
     *
     * @return SystemLimit[]
     */
    public function getLimitsWithIndexAlias(array $aliases): array
    {
        $group = [];
        foreach ($this->findBy(['inspector' => $aliases]) as $limit) {
            $group[$limit->getInspector()] = $limit;
        }

        return $group;
    }

    public function getLimit(string $inspector): ?SystemLimit
    {
        return $this->findOneBy(['inspector' => $inspector]);
    }

    public function save(SystemLimit $limit): void
    {
        $this->getEntityManager()->persist($limit);
        $this->getEntityManager()->flush();
    }
}

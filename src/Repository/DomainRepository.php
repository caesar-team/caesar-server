<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Domain;
use App\Security\Domain\Repository\AllowedDomainRepositoryInterface;
use App\Security\Domain\Repository\EnvironmentAllowedDomainRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Domain|null find($id, $lockMode = null, $lockVersion = null)
 * @method Domain|null findOneBy(array $criteria, array $orderBy = null)
 * @method Domain[]    findAll()
 * @method Domain[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DomainRepository extends ServiceEntityRepository implements AllowedDomainRepositoryInterface
{
    private EnvironmentAllowedDomainRepository $envDomainRepository;

    public function __construct(ManagerRegistry $registry, EnvironmentAllowedDomainRepository $envDomainRepository)
    {
        parent::__construct($registry, Domain::class);

        $this->envDomainRepository = $envDomainRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedDomains(): array
    {
        $queryBuilder = $this->createQueryBuilder('domain');
        $queryBuilder
            ->select('domain.domain')
            ->where('domain.active = true');

        $domains = array_column($queryBuilder->getQuery()->getResult(), 'domain');
        if (empty($domains)) {
            return $this->envDomainRepository->getAllowedDomains();
        }

        return $domains;
    }
}

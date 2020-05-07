<?php

declare(strict_types=1);

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

class DirectoryRepository extends EntityRepository
{
    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getMaxPosition(): int
    {
        $query = $this->createQueryBuilder('directory');
        $query->select('MAX(directory.sort) AS max_position');

        return (int) $query->setMaxResults(1)->getQuery()->getSingleScalarResult();
    }
}

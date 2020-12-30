<?php

declare(strict_types=1);

namespace App\Repository;

use App\DBAL\Types\Enum\DirectoryEnumType;
use App\Entity\Directory\AbstractDirectory;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method AbstractDirectory|null find($id, $lockMode = null, $lockVersion = null)
 * @method AbstractDirectory|null findOneBy(array $criteria, array $orderBy = null)
 * @method AbstractDirectory[]    findAll()
 * @method AbstractDirectory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DirectoryRepository extends ServiceEntityRepository
{
    private TeamDirectoryRepository $teamDirectoryRepository;

    public function __construct(ManagerRegistry $registry, TeamDirectoryRepository $teamDirectoryRepository)
    {
        parent::__construct($registry, AbstractDirectory::class);
        $this->teamDirectoryRepository = $teamDirectoryRepository;
    }

    public function getMovableListsByUser(User $user): array
    {
        $lists = array_merge($user->getDirectoriesWithoutRoot(), $this->teamDirectoryRepository->getTeamListsByUser($user));
        $lists = array_filter($lists, static function (AbstractDirectory $directory) {
            return DirectoryEnumType::LIST === $directory->getType()
                || DirectoryEnumType::DEFAULT === $directory->getType()
            ;
        });

        return array_values($lists);
    }

    public function save(AbstractDirectory $directory): void
    {
        $this->getEntityManager()->persist($directory);
        $this->getEntityManager()->flush();
    }

    public function remove(AbstractDirectory $directory): void
    {
        $this->getEntityManager()->remove($directory);
        $this->getEntityManager()->flush();
    }
}

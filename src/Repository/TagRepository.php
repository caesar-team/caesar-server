<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Tag|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tag|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tag[]    findAll()
 * @method Tag[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tag::class);
    }

    public function getTagByName(string $name): ?Tag
    {
        return $this->findOneBy(['name' => $name]);
    }

    public function getTags(array $names): array
    {
        $tags = $this->findBy(['name' => $names]);

        $groupTagsByName = [];
        foreach ($tags as $tag) {
            if (null === $tag->getName()) {
                continue;
            }

            /** @psalm-suppress PossiblyNullArrayOffset */
            $groupTagsByName[$tag->getName()] = $tag;
        }

        return $groupTagsByName;
    }
}

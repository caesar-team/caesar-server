<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

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
            $groupTagsByName[$tag->getName()] = $tag;
        }

        return $groupTagsByName;
    }
}

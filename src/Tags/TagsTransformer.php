<?php

declare(strict_types=1);

namespace App\Tags;

use App\Entity\Tag;
use App\Repository\TagRepository;

final class TagsTransformer implements TagsTransformerInterface
{
    private TagRepository $repository;

    public function __construct(TagRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param string[] $tags
     *
     * @return Tag[]
     */
    public function transform(array $tags): array
    {
        if (empty($tags)) {
            return [];
        }

        $uniqueTags = [];
        foreach ($tags as $tag) {
            $uniqueTags[$tag] = $tag;
        }

        $existTags = $this->repository->getTags($uniqueTags);
        $tags = [];
        foreach ($uniqueTags as $tag) {
            $tags[] = $existTags[$tag] ?? new Tag($tag);
        }

        return $tags;
    }
}

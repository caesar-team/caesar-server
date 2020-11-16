<?php

declare(strict_types=1);

namespace App\Tags;

use App\Entity\Tag;

interface TagsTransformerInterface
{
    /**
     * @param string[] $tags
     *
     * @return Tag[]
     */
    public function transform(array $tags): array;
}

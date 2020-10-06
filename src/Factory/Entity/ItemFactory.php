<?php

declare(strict_types=1);

namespace App\Factory\Entity;

use App\Entity\Item;
use App\Request\Item\CreateItemRequest;
use App\Tags\TagsTransformerInterface;
use Doctrine\Common\Collections\ArrayCollection;

class ItemFactory
{
    private TagsTransformerInterface $transformer;

    public function __construct(TagsTransformerInterface $transformer)
    {
        $this->transformer = $transformer;
    }

    public function createFromRequest(CreateItemRequest $request): Item
    {
        $item = new Item($request->getOwner() ?: $request->getUser());
        $item->setParentList($request->getList() ?: $item->getSignedOwner()->getDefaultDirectory());
        $item->setType($request->getType());
        $item->setSecret($request->getSecret());
        $item->setFavorite($request->isFavorite());
        $item->setTags(new ArrayCollection($this->transformer->transform($request->getTags())));
        $item->setRelatedItem($request->getRelatedItem());
        $item->setTeam($request->getTeam());

        return $item;
    }

    public function create(): Item
    {
        return new Item();
    }
}

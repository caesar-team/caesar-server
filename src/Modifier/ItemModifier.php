<?php

declare(strict_types=1);

namespace App\Modifier;

use App\Entity\Item;
use App\Repository\ItemRepository;
use App\Request\Item\EditItemRequest;
use App\Tags\TagsTransformerInterface;
use Doctrine\Common\Collections\ArrayCollection;

class ItemModifier
{
    private ItemRepository $repository;

    private TagsTransformerInterface $transformer;

    public function __construct(ItemRepository $repository, TagsTransformerInterface $transformer)
    {
        $this->repository = $repository;
        $this->transformer = $transformer;
    }

    public function modifyByRequest(EditItemRequest $request): Item
    {
        $item = $request->getItem();
        if (null !== $request->getOwner()) {
            $item->setOwner($request->getOwner());
        }
        $item->setSecret($request->getSecret());
        $item->setTags(new ArrayCollection($this->transformer->transform($request->getTags())));
        $this->repository->save($item);

        return $item;
    }
}

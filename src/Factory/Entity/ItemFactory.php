<?php

declare(strict_types=1);

namespace App\Factory\Entity;

use App\DBAL\Types\Enum\NodeEnumType;
use App\Entity\Embedded\ItemMeta;
use App\Entity\Item;
use App\Request\Item\CreateItemRequest;
use App\Request\Item\CreateKeypairRequest;
use App\Tags\TagsTransformerInterface;
use Doctrine\Common\Collections\ArrayCollection;

class ItemFactory
{
    private TagsTransformerInterface $transformer;

    public function __construct(TagsTransformerInterface $transformer)
    {
        $this->transformer = $transformer;
    }

    public function createTeamKeypairFromRequest(CreateKeypairRequest $request): Item
    {
        $item = new Item($request->getOwner() ?: $request->getUser());
        $item->setParentList($request->getTeam()->getDefaultDirectory());
        $item->setType(NodeEnumType::TYPE_KEYPAIR);
        $item->setSecret($request->getSecret());
        $item->setRelatedItem($request->getRelatedItem());
        $item->setTeam($request->getTeam());

        $meta = new ItemMeta();
        $meta->setTitle(NodeEnumType::TYPE_KEYPAIR);
        $item->setMeta($meta);

        return $item;
    }

    public function createFromRequest(CreateItemRequest $request): Item
    {
        $item = new Item($request->getOwner() ?: $request->getUser());
        $parentList = $request->getList() ?: $item->getSignedOwner()->getDefaultDirectory();
        if (null !== $request->getOwner() && !$request->getUser()->equals($request->getOwner())) {
            $parentList = $request->getList() ?: $item->getSignedOwner()->getInbox();
        }

        $item->setParentList($parentList);
        $item->setType($request->getType());
        $item->setSecret($request->getSecret());
        $item->setFavorite($request->isFavorite());
        $item->setTags(new ArrayCollection($this->transformer->transform($request->getTags())));
        $item->setTeam($request->getTeam());
        $item->setMeta(new ItemMeta(
            $request->getMeta()->getAttachmentsCount() ?: 0,
            $request->getMeta()->getWebsite(),
            $request->getMeta()->getTitle()
        ));
        $item->setRaws($request->getRaws());

        return $item;
    }

    public function create(): Item
    {
        return new Item();
    }
}

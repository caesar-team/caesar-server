<?php

declare(strict_types=1);

namespace App\Factory\Entity;

use App\DBAL\Types\Enum\NodeEnumType;
use App\Entity\Embedded\ItemMeta;
use App\Entity\Item;
use App\Factory\Entity\Directory\DirectoryItemFactory;
use App\Request\Item\CreateItemRequest;
use App\Request\Item\CreateKeypairRequest;
use App\Tags\TagsTransformerInterface;
use Doctrine\Common\Collections\ArrayCollection;

class ItemFactory
{
    private TagsTransformerInterface $transformer;

    private DirectoryItemFactory $directoryItemFactory;

    public function __construct(TagsTransformerInterface $transformer, DirectoryItemFactory $directoryItemFactory)
    {
        $this->transformer = $transformer;
        $this->directoryItemFactory = $directoryItemFactory;
    }

    public function createTeamKeypairFromRequest(CreateKeypairRequest $request): Item
    {
        $item = new Item($request->getOwner() ?: $request->getUser());
        $item->setType(NodeEnumType::TYPE_KEYPAIR);
        $item->setSecret($request->getSecret());
        $item->setRelatedItem($request->getRelatedItem());
        $item->setTeam($request->getTeam());

        $meta = new ItemMeta();
        $meta->setTitle(NodeEnumType::TYPE_KEYPAIR);
        $item->setMeta($meta);

        $this->directoryItemFactory->create($item, $request->getTeam()->getDefaultDirectory());

        return $item;
    }

    public function createFromRequest(CreateItemRequest $request): Item
    {
        $item = new Item($request->getOwner() ?: $request->getUser());
        $item->setType($request->getType());
        $item->setSecret($request->getSecret());
        $item->setTags(new ArrayCollection($this->transformer->transform($request->getTags())));
        $item->setTeam($request->getTeam());
        $item->setMeta(new ItemMeta(
            $request->getMeta()->getAttachmentsCount() ?: 0,
            $request->getMeta()->getWebsite(),
            $request->getMeta()->getTitle()
        ));
        $item->setRaws($request->getRaws());

        $parentList = $request->getList() ?: $item->getSignedOwner()->getDefaultDirectory();
        if (null !== $request->getOwner() && !$request->getUser()->equals($request->getOwner())) {
            $parentList = $request->getList() ?: $item->getSignedOwner()->getInbox();
        }
        $this->directoryItemFactory->create($item, $parentList);

        return $item;
    }

    public function create(): Item
    {
        return new Item();
    }
}

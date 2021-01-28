<?php

declare(strict_types=1);

namespace App\Factory\Entity;

use App\DBAL\Types\Enum\NodeEnumType;
use App\Entity\Embedded\ItemMeta;
use App\Factory\Entity\Directory\DirectoryItemFactory;
use App\Model\DTO\Share;
use App\Request\Item\ShareBatchItemRequest;

class ShareFactory
{
    private ItemFactory $itemFactory;

    private DirectoryItemFactory $directoryItemFactory;

    public function __construct(ItemFactory $itemFactory, DirectoryItemFactory $directoryItemFactory)
    {
        $this->itemFactory = $itemFactory;
        $this->directoryItemFactory = $directoryItemFactory;
    }

    /**
     * @return Share[]
     */
    public function createFromRequest(ShareBatchItemRequest $request): array
    {
        $relatedItem = $request->getItem();

        $result = [];
        foreach ($request->getUsers() as $userRequest) {
            $user = $userRequest->getUser();
            if (isset($result[$user->getId()->toString()])) {
                continue;
            }

            $item = $this->itemFactory->create();
            $item->setOwner($user);
            $item->setType(NodeEnumType::TYPE_KEYPAIR);
            $item->setSecret($userRequest->getSecret());
            $item->setRelatedItem($relatedItem);
            $meta = new ItemMeta();
            $meta->setTitle(NodeEnumType::TYPE_KEYPAIR);
            $item->setMeta($meta);

            if (!$relatedItem->getOwner()->equals($user)) {
                $this->directoryItemFactory->create($relatedItem, $user->getInbox());
            }
            $this->directoryItemFactory->create($item, $user->getInbox());

            $result[$user->getId()->toString()] = new Share($user, $item);
        }

        return array_values($result);
    }
}

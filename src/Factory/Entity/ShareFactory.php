<?php

declare(strict_types=1);

namespace App\Factory\Entity;

use App\DBAL\Types\Enum\NodeEnumType;
use App\Model\DTO\Share;
use App\Request\Item\ShareBatchItemRequest;

class ShareFactory
{
    private ItemFactory $itemFactory;

    public function __construct(ItemFactory $itemFactory)
    {
        $this->itemFactory = $itemFactory;
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

            $item = $this->itemFactory->create();
            $item->setOwner($user);
            $item->setTitle(NodeEnumType::TYPE_KEYPAIR);
            $item->setType(NodeEnumType::TYPE_KEYPAIR);
            $item->setParentList($user->getInbox());
            $item->setSecret($userRequest->getSecret());
            $item->setRelatedItem($relatedItem);

            $result[] = new Share($user, $item);
        }

        return $result;
    }
}

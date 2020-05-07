<?php

declare(strict_types=1);

namespace App\Strategy\ViewFactory;

use App\Entity\Item;
use App\Entity\ItemUpdate;
use App\Entity\User;
use App\Model\View\CredentialsList\ChildItemView;
use App\Model\View\CredentialsList\InviteItemView;
use App\Model\View\CredentialsList\ItemView;
use App\Model\View\CredentialsList\UpdateView;
use App\Utils\ChildItemAwareInterface;
use Countable;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\Security;

final class ItemViewFactory implements ViewFactoryInterface
{
    /**
     * @var User
     */
    private $currentUser;

    public function __construct(Security $security)
    {
        $this->currentUser = $security->getUser();
    }

    /**
     * @param mixed $data
     * @return bool
     */
    public function canView($data): bool
    {
        return $data instanceof Item;
    }

    /**
     * @param Item $item
     *
     * @return ItemView
     */
    public function view($item): ItemView
    {
        $view = new ItemView();

        $view->id = $item->getId();
        $view->type = $item->getType();
        $view->lastUpdated = $item->getLastUpdated();
        $view->listId = $item->getParentList()->getId()->toString();
        $view->previousListId = $item->getPreviousList() ? $item->getPreviousList()->getId()->toString() : null;

        $view->secret = $item->getSecret();
        $view->invited = $this->getInvitesCollection($item);
        $view->shared = $this->getSharesCollection($item);
        $view->update = $this->getUpdateView($item->getUpdate());
        $view->ownerId = $item->getOwner()->getId()->toString();
        $view->favorite = $item->isFavorite();
        $view->sort = $item->getSort();
        $view->originalItemId = $item->getOriginalItem() ? $item->getOriginalItem()->getId()->toString() : null;

        return $view;
    }

    /**
     * @param array|Item[] $items
     *
     * @return array|ItemView[]
     */
    public function viewList(array $items): array
    {
        $list = [];
        foreach ($items as $item) {
            if ($this->currentUser !== $item->getSignedOwner()) {
                continue;
            }

            $list[] = $this->view($item);
        }

        return $list;
    }

    /**
     * @return array
     */
    private function getInvitesCollection(Item $item): array
    {
        $ownerItem = $item;
        if (null !== $item->getOriginalItem()) {
            $ownerItem = $item->getOriginalItem();
        }

        $children = [];
        $sharedItems = $this->extractChildItemByCause($ownerItem->getSharedItems());
        foreach ($sharedItems as $childItem) {
            $childItemView = new InviteItemView();
            $childItemView->id = $childItem->getId()->toString();
            $childItemView->userId = $childItem->getSignedOwner()->getId()->toString();
            $childItemView->access = $childItem->getAccess();
            $children[] = $childItemView;
        }

        return $children;
    }

    /**
     * @param \Countable|ChildItemAwareInterface[]|Collection $childItems
     *
     * @return array|Item[]
     */
    private function extractChildItemByCause(Countable $childItems, string $cause = Item::CAUSE_INVITE): array
    {
        return $childItems->filter(function (ChildItemAwareInterface $childItem) use ($cause) {
            return $cause === $childItem->getCause();
        })->toArray();
    }

    /**
     * @return ChildItemView|null
     */
    private function getSharesCollection(Item $item): ?ChildItemView
    {
        $ownerItem = $item->getOriginalItem() ?? $item;

        $sharedItems = $this->extractChildItemByCause($ownerItem->getSharedItems(), Item::CAUSE_SHARE);

        if (0 === count($sharedItems)) {
            return null;
        }
        $item = current($sharedItems);

        $user = $item->getSignedOwner();

        $childItemView = new ChildItemView();
        $childItemView->id = $item->getId()->toString();
        $childItemView->userId = $user->getId()->toString();
        $childItemView->email = $user->getEmail();
        $childItemView->lastUpdated = $item->getLastUpdated();
        $childItemView->access = $item->getAccess();
        $childItemView->link = $item->getLink();
        $childItemView->isAccepted = User::FLOW_STATUS_FINISHED === $user->getFlowStatus();
        $childItemView->publicKey = $user->getPublicKey();
        $shares = $childItemView;

        return $shares;
    }

    private function getUpdateView(?ItemUpdate $update): ?UpdateView
    {
        if (null === $update) {
            return null;
        }

        $view = new UpdateView();
        $view->userId = $update->getUpdatedBy()->getId()->toString();
        $view->createdAt = $update->getLastUpdated();
        $view->secret = $update->getSecret();

        return $view;
    }
}

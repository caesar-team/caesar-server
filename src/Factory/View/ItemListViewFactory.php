<?php

declare(strict_types=1);

namespace App\Factory\View;

use App\Entity\Item;
use App\Entity\User;
use App\Model\View\CredentialsList\ItemView;
use Symfony\Component\Security\Core\Security;

class ItemListViewFactory
{
    /**
     * @var ItemViewFactory
     */
    private $secretViewFactory;

    /**
     * @var User
     */
    private $currentUser;

    public function __construct(ItemViewFactory $secretViewFactory, Security $security)
    {
        $this->secretViewFactory = $secretViewFactory;
        $this->currentUser = $security->getUser();
    }

    /**
     * @param Item[] $itemCollection
     *
     * @return ItemView[]
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function create(array $itemCollection): array
    {
        $viewCollection = [];
        foreach ($itemCollection as $item) {
            if ($this->currentUser !== $item->getSignedOwner()) {
                continue;
            }

            $viewCollection[] = $this->secretViewFactory->create($item);
        }

        return $viewCollection;
    }
}

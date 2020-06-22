<?php

declare(strict_types=1);

namespace App\Model\View\CredentialsList;

use App\Entity\Item;
use Symfony\Component\Serializer\Annotation\Groups;

final class ShareListView
{
    /**
     * @var ShareView[]
     * @Groups({"child_item"})
     */
    public $shares;

    /**
     * @param array<string, array<int, Item>> $parentItems
     *
     * @return ShareListView
     */
    public static function create(array $parentItems): self
    {
        $view = new self();
        foreach ($parentItems as $id => $items) {
            $view->shares[] = ShareView::create($id, $items);
        }

        return $view;
    }
}

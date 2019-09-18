<?php

declare(strict_types=1);

namespace App\Model\View\CredentialsList;

use Swagger\Annotations as SWG;
use Symfony\Component\Serializer\Annotation\Groups;

class ShareView
{
    /**
     * @var string
     * @Groups({"child_item"})
     *
     * @SWG\Property(example="4fcc6aef-3fd6-4c16-9e4b-5c37486c7d46")
     */
    public $originalItemId;

    /**
     * @var ChildItemView[]
     * @Groups({"child_item"})
     */
    public $items = [];


    public static function create(string $id, array $items): self
    {
        $view = new self();
        $view->originalItemId = $id;
        foreach ($items as $item) {
            $view->items[] = ChildItemView::create($item);
        }

        return $view;
    }
}
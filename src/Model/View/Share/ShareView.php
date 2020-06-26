<?php

declare(strict_types=1);

namespace App\Model\View\Share;

use App\Model\View\Item\ChildItemView;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;

class ShareView
{
    /**
     * @SWG\Property(type="string", example="4fcc6aef-3fd6-4c16-9e4b-5c37486c7d46")
     */
    private string $originalItemId;

    /**
     * @var ChildItemView[]
     *
     * @SWG\Property(type="array", @Model(type=ChildItemView::class))
     */
    private array $items;

    public function __construct()
    {
        $this->items = [];
    }

    public function getOriginalItemId(): ?string
    {
        return $this->originalItemId;
    }

    public function setOriginalItemId(string $originalItemId): void
    {
        $this->originalItemId = $originalItemId;
    }

    /**
     * @return ChildItemView[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param ChildItemView[] $items
     */
    public function setItems(array $items): void
    {
        $this->items = $items;
    }
}

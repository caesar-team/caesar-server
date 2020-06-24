<?php

declare(strict_types=1);

namespace App\Model\View\Item;

use Swagger\Annotations as SWG;

class OfferedTeamItemsView
{
    /**
     * @SWG\Property(type="string", example="a68833af-ab0f-4db3-acde-fccc47641b9e", description="Team id")
     */
    private string $id;

    /**
     * @var OfferedItemView[]
     */
    private array $items;

    public function __construct()
    {
        $this->items = [];
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return OfferedItemView[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param OfferedItemView[] $items
     */
    public function setItems(array $items): void
    {
        $this->items = $items;
    }
}

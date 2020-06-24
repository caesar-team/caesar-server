<?php

declare(strict_types=1);

namespace App\Model\View\Item;

use Swagger\Annotations as SWG;

final class FavoriteItemView
{
    /**
     * @SWG\Property(type="string", example="a68833af-ab0f-4db3-acde-fccc47641b9e")
     */
    private string $listId;

    /**
     * @SWG\Property(type="boolean", example=true)
     */
    private bool $favorite = false;

    public function getListId(): string
    {
        return $this->listId;
    }

    public function setListId(string $listId): void
    {
        $this->listId = $listId;
    }

    public function isFavorite(): bool
    {
        return $this->favorite;
    }

    public function setFavorite(bool $favorite): void
    {
        $this->favorite = $favorite;
    }
}

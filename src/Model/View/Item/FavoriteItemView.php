<?php

declare(strict_types=1);

namespace App\Model\View\Item;

use Swagger\Annotations as SWG;

final class FavoriteItemView
{
    /**
     * @SWG\Property(type="boolean", example=true)
     */
    private bool $favorite = false;

    public function isFavorite(): bool
    {
        return $this->favorite;
    }

    public function setFavorite(bool $favorite): void
    {
        $this->favorite = $favorite;
    }
}

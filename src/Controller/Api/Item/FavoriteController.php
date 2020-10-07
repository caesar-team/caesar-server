<?php

declare(strict_types=1);

namespace App\Controller\Api\Item;

use App\Controller\AbstractController;
use App\Entity\Item;
use App\Factory\View\Item\FavoriteItemViewFactory;
use App\Model\View\Item\FavoriteItemView;
use App\Repository\ItemRepository;
use App\Security\Voter\ItemVoter;
use App\Security\Voter\TeamItemVoter;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @SWG\Response(
 *     response=401,
 *     description="Unauthorized"
 * )
 * @SWG\Response(
 *     response=403,
 *     description="You are not owner of this item"
 * )
 */
final class FavoriteController extends AbstractController
{
    /**
     * Toggle favorite item.
     *
     * @SWG\Tag(name="Item / Favorite")
     * @SWG\Response(
     *     response=200,
     *     description="Set favorite is on or off",
     *     @Model(type=FavoriteItemView::class)
     * )
     * @SWG\Response(
     *     response=404,
     *     description="No such item"
     * )
     *
     * @Route(
     *     path="/api/items/{id}/favorite",
     *     name="api_favorite_item_toggle",
     *     methods={"POST"}
     * )
     */
    public function toggle(Item $item, ItemRepository $repository, FavoriteItemViewFactory $factory): FavoriteItemView
    {
        $this->denyAccessUnlessGranted([ItemVoter::FAVORITE, TeamItemVoter::FAVORITE], $item);

        $item->toggleFavorite($this->getUser());
        $repository->save($item);

        return $factory->createSingle($item);
    }
}

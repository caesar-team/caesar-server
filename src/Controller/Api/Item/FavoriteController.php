<?php

declare(strict_types=1);

namespace App\Controller\Api\Item;

use App\Controller\AbstractController;
use App\Entity\Item;
use App\Entity\Team;
use App\Factory\View\Item\FavoriteItemViewFactory;
use App\Factory\View\Item\ItemViewFactory;
use App\Model\View\Item\FavoriteItemView;
use App\Model\View\Item\ItemView;
use App\Repository\ItemRepository;
use App\Security\Voter\UserTeamVoter;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\Routing\Annotation\Route;

final class FavoriteController extends AbstractController
{
    /**
     * Get list of favourite items.
     *
     * @SWG\Tag(name="Item / Favorite")
     *
     * @SWG\Response(
     *     response=200,
     *     description="List of favourite items",
     *     @SWG\Schema(type="array", @Model(type=ItemView::class))
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     * @SWG\Response(
     *     response=403,
     *     description="You are not owner of this item"
     * )
     *
     * @Route(
     *     path="/api/items/favorite/{team}",
     *     name="api_favorites_item",
     *     methods={"GET"}
     * )
     *
     * @return ItemView[]
     */
    public function favorite(ItemViewFactory $viewFactory, ItemRepository $repository, ?Team $team = null): array
    {
        $user = $this->getUser();
        if (null !== $team) {
            $this->denyAccessUnlessGranted(UserTeamVoter::VIEW, $user->getUserTeamByTeam($team));
        }

        return $viewFactory->createCollection(
            $repository->getFavoritesItems($user, $team)
        );
    }

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
     *     response=401,
     *     description="Unauthorized"
     * )
     * @SWG\Response(
     *     response=403,
     *     description="You are not owner of this item"
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
        $item->toggleFavorite();
        $repository->save($item);

        return $factory->createSingle($item);
    }
}

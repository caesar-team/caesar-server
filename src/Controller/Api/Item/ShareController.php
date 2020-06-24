<?php

declare(strict_types=1);

namespace App\Controller\Api\Item;

use App\Controller\AbstractController;
use App\Entity\Team;
use App\Factory\View\Item\OfferedItemViewFactory;
use App\Factory\View\Item\OfferedTeamItemsViewFactory;
use App\Model\View\Item\OfferedItemsView;
use App\Repository\TeamRepository;
use App\Utils\DirectoryHelper;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\Routing\Annotation\Route;

final class ShareController extends AbstractController
{
    /**
     * Get shared items to me.
     *
     * @SWG\Tag(name="Item")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Items collection",
     *     @SWG\Schema(type="array", @Model(type=OfferedItemsView::class))
     * )
     * @Route("/api/offered_item", methods={"GET"}, name="api_item_offered_list")
     */
    public function getOfferedItemsList(
        TeamRepository $teamRepository,
        OfferedItemViewFactory $itemViewFactory,
        OfferedTeamItemsViewFactory $teamItemsViewFactory
    ): OfferedItemsView {
        $user = $this->getUser();

        $teams = $teamRepository->findByUser($user);
        $teams = array_filter($teams, static function (Team $team) {
            return !empty($team->getOfferedItems());
        });

        return new OfferedItemsView(
            $itemViewFactory->createCollection(
                DirectoryHelper::extractOfferedItemsByUser($user)
            ),
            $teamItemsViewFactory->createCollection($teams)
        );
    }
}

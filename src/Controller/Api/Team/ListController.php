<?php

declare(strict_types=1);

namespace App\Controller\Api\Team;

use App\Controller\AbstractController;
use App\Entity\Team;
use App\Factory\View\Team\TeamListViewFactory;
use App\Model\View\Team\TeamListView;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/api/teams")
 */
final class ListController extends AbstractController
{
    /**
     * Get lists by team.
     *
     * @SWG\Tag(name="Team")
     * @SWG\Response(
     *     response=200,
     *     description="Team lists",
     *     @SWG\Schema(type="array", @Model(type=TeamListView::class))
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     * @Route(path="/{team}/lists", methods={"GET"})
     *
     * @return TeamListView[]
     */
    public function lists(Team $team, TeamListViewFactory $viewFactory): array
    {
        return $viewFactory->createCollection(
            array_merge(
                [$team->getTrash()],
                $team->getLists()->getChildLists()->toArray()
            )
        );
    }
}

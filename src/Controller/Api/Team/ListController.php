<?php

declare(strict_types=1);

namespace App\Controller\Api\Team;

use App\Controller\AbstractController;
use App\Entity\Team;
use App\Entity\User;
use App\Factory\View\Team\TeamListViewFactory;
use App\Factory\View\Team\TeamViewFactory;
use App\Model\View\Team\TeamListView;
use App\Model\View\Team\TeamView;
use App\Repository\TeamRepository;
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

    /**
     * List of teams.
     *
     * @SWG\Tag(name="Team")
     *
     * @SWG\Response(
     *     response=200,
     *     description="List of teams",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type=\App\Model\View\Team\TeamView::class)
     *     )
     * )
     *
     * @Route(
     *     name="api_team_list",
     *     methods={"GET"}
     * )
     *
     * @return TeamView[]
     */
    public function teams(TeamViewFactory $viewFactory, TeamRepository $teamRepository): array
    {
        $user = $this->getUser();
        if ($user->hasRole(User::ROLE_ADMIN) || $user->hasRole(User::ROLE_SUPER_ADMIN)) {
            $teams = $teamRepository->findAll();
        } else {
            $teams = $teamRepository->findByUser($user);
        }

        return $viewFactory->createCollection($teams);
    }
}

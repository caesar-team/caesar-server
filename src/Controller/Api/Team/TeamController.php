<?php

declare(strict_types=1);

namespace App\Controller\Api\Team;

use App\Controller\AbstractController;
use App\Entity\Team;
use App\Entity\UserTeam;
use App\Factory\Entity\TeamFactory;
use App\Factory\View\Team\TeamViewFactory;
use App\Form\Request\Team\CreateTeamType;
use App\Form\Request\Team\EditTeamType;
use App\Limiter\Inspector\TeamCountInspector;
use App\Limiter\LimiterInterface;
use App\Limiter\Model\LimitCheck;
use App\Model\View\Team\TeamView;
use App\Repository\TeamRepository;
use App\Security\Voter\TeamVoter;
use App\Security\Voter\UserTeamVoter;
use App\Services\TeamManager;
use Doctrine\ORM\EntityManagerInterface;
use Fourxxi\RestRequestError\Exception\FormInvalidRequestException;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/api/teams")
 * @SWG\Response(
 *     response=401,
 *     description="Unauthorized"
 * )
 */
class TeamController extends AbstractController
{
    /**
     * Create a team.
     *
     * @SWG\Tag(name="Team")
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=CreateTeamType::class)
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Create a team",
     *     @Model(type=TeamView::class)
     * )
     *
     * @Route(
     *     name="api_team_create",
     *     methods={"POST"}
     * )
     *
     * @deprecated
     */
    public function create(
        Request $request,
        TeamViewFactory $viewFactory,
        EntityManagerInterface $entityManager,
        TeamFactory $teamFactory,
        TeamManager $teamManager,
        LimiterInterface $limiter
    ): TeamView {
        $this->denyAccessUnlessGranted(TeamVoter::CREATE, $this->getUser());

        $limiter->check([
            new LimitCheck(TeamCountInspector::class, 1),
        ]);

        $team = $teamFactory->create();
        $form = $this->createForm(CreateTeamType::class, $team);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            throw new FormInvalidRequestException($form);
        }

        $entityManager->persist($team);
        $teamManager->addTeamToUser($this->getUser(), UserTeam::USER_ROLE_ADMIN, $team);
        $entityManager->flush();

        return $viewFactory->createSingle($team);
    }

    /**
     * Get a team.
     *
     * @SWG\Tag(name="Team")
     *
     * @SWG\Response(
     *     response=200,
     *     description="A team view",
     *     @Model(type=TeamView::class)
     * )
     *
     * @Route(
     *     path="/{team}",
     *     name="api_team_view",
     *     methods={"GET"}
     * )
     */
    public function team(Team $team, TeamViewFactory $viewFactory): TeamView
    {
        $this->denyAccessUnlessGranted(UserTeamVoter::VIEW, $team->getUserTeamByUser($this->getUser()));

        return $viewFactory->createSingle($team);
    }

    /**
     * Edit a team.
     *
     * @SWG\Tag(name="Team")
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=CreateTeamType::class)
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Edit a team",
     *     @Model(type=TeamView::class)
     * )
     *
     * @Route(
     *     path="/{team}",
     *     name="api_team_edit",
     *     methods={"PATCH"}
     * )
     */
    public function update(
        Team $team,
        Request $request,
        TeamViewFactory $viewFactory,
        EntityManagerInterface $entityManager
    ): TeamView {
        $this->denyAccessUnlessGranted(TeamVoter::EDIT, $team);

        $form = $this->createForm(EditTeamType::class, $team);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            throw new FormInvalidRequestException($form);
        }

        $entityManager->flush();

        return $viewFactory->createSingle($team);
    }

    /**
     * Delete a team.
     *
     * @SWG\Tag(name="Team")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Delete a team"
     * )
     *
     * @Route(
     *     path="/{team}",
     *     name="api_team_delete",
     *     methods={"DELETE"}
     * )
     */
    public function delete(Team $team, EntityManagerInterface $entityManager): JsonResponse
    {
        $this->denyAccessUnlessGranted(TeamVoter::DELETE, $team);
        $entityManager->remove($team);
        $entityManager->flush();

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }

    /**
     * List of teams.
     *
     * @SWG\Tag(name="Team")
     *
     * @SWG\Response(
     *     response=200,
     *     description="List of teams",
     *     @SWG\Schema(type="array", @Model(type=TeamView::class))
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
        return $viewFactory->createCollection($teamRepository->findAll());
    }

    /**
     * Toggle pinned team.
     *
     * @SWG\Tag(name="Team")
     * @SWG\Response(
     *     response=200,
     *     description="Set pinned is on",
     *     @Model(type=TeamView::class)
     * )
     * @SWG\Response(
     *     response=404,
     *     description="No such team"
     * )
     *
     * @Route(
     *     path="/{team}/pin",
     *     name="api_pinned_team_toggle",
     *     methods={"POST"}
     * )
     */
    public function pin(Team $team, TeamRepository $repository, TeamViewFactory $factory): TeamView
    {
        $this->denyAccessUnlessGranted(TeamVoter::PINNED, $team);

        $team->togglePinned($this->getUser());
        $repository->save($team);

        return $factory->createSingle($team);
    }

    /**
     * Toggle pinned team.
     *
     * @SWG\Tag(name="Team")
     * @SWG\Response(
     *     response=200,
     *     description="Set unpinned is off",
     *     @Model(type=TeamView::class)
     * )
     * @SWG\Response(
     *     response=404,
     *     description="No such team"
     * )
     *
     * @Route(
     *     path="/{team}/unpin",
     *     name="api_unpinned_team_toggle",
     *     methods={"POST"}
     * )
     */
    public function unpin(Team $team, TeamRepository $repository, TeamViewFactory $factory): TeamView
    {
        $this->denyAccessUnlessGranted(TeamVoter::PINNED, $team);

        $team->togglePinned($this->getUser(), false);
        $repository->save($team);

        return $factory->createSingle($team);
    }
}

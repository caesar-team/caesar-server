<?php

declare(strict_types=1);

namespace App\Controller\Api\Team;

use App\Entity\Team;
use App\Factory\View\TeamViewFactory;
use App\Form\Request\Team\CreateTeamType;
use App\Form\Request\Team\EditTeamType;
use App\Model\View\Team\TeamView;
use App\Security\Voter\TeamVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;

class TeamController extends AbstractController
{
    /**
     * @SWG\Tag(name="Team")
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=\App\Form\Request\Team\CreateTeamType::class)
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Create a team",
     *     @Model(type=\App\Model\View\Team\TeamView::class)
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     *
     * @Route(
     *     path="/api/teams",
     *     name="api_team_create",
     *     methods={"POST"}
     * )
     *
     *
     * @param Request $request
     * @param TeamViewFactory $viewFactory
     * @param EntityManagerInterface $entityManager
     * @return TeamView
     * @throws \Exception
     */
    public  function create(Request $request, TeamViewFactory $viewFactory, EntityManagerInterface $entityManager): TeamView
    {
        $this->denyAccessUnlessGranted(TeamVoter::TEAM_CREATE, $this->getUser());

        $team = new Team();
        $form = $this->createForm(CreateTeamType::class, $team);
        $form->submit($request->request->all());
        if ($form->isValid()) {
            $entityManager->persist($team);
            $entityManager->flush();
        }
        $teamView = $viewFactory->createOne($team);

        return $teamView;
    }

    /**
     * @SWG\Tag(name="Team")
     *
     * @SWG\Response(
     *     response=200,
     *     description="A team view",
     *     @Model(type=\App\Model\View\Team\TeamView::class)
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     *
     * @Route(
     *     path="/api/teams/{team}",
     *     name="api_team_view",
     *     methods={"GET"}
     * )
     *
     *
     * @param Team $team
     * @param TeamViewFactory $viewFactory
     * @return TeamView
     */
    public function team(Team $team, TeamViewFactory $viewFactory)
    {
        $this->denyAccessUnlessGranted(TeamVoter::TEAM_VIEW, $this->getUser());
        $teamView = $viewFactory->createOne($team);

        return $teamView;
    }

    /**
     * @SWG\Tag(name="Team")
     *
     * @SWG\Response(
     *     response=200,
     *     description="List of teams"
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     *
     * @Route(
     *     path="/api/teams",
     *     name="api_team_list",
     *     methods={"GET"}
     * )
     *
     *
     * @param TeamViewFactory $viewFactory
     * @param EntityManagerInterface $entityManager
     * @return TeamView[]
     */
    public function teams(TeamViewFactory $viewFactory, EntityManagerInterface $entityManager)
    {
        $this->denyAccessUnlessGranted(TeamVoter::TEAM_VIEW, $this->getUser());
        $teams = $entityManager->getRepository(Team::class)->findAll();
        $teamView = $viewFactory->createMany($teams);

        return $teamView;
    }

    /**
     * @SWG\Tag(name="Team")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=\App\Form\Request\Team\CreateTeamType::class)
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Edit a team",
     *     @Model(type=\App\Model\View\Team\TeamView::class)
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     *
     * @Route(
     *     path="/api/teams/{team}",
     *     name="api_team_edit",
     *     methods={"PATCH"}
     * )
     *
     *
     * @param Team $team
     * @param Request $request
     * @param TeamViewFactory $viewFactory
     * @param EntityManagerInterface $entityManager
     * @return TeamView
     */
    public function update(Team $team, Request $request, TeamViewFactory $viewFactory, EntityManagerInterface $entityManager)
    {
        $this->denyAccessUnlessGranted(TeamVoter::TEAM_EDIT, $this->getUser());

        $form = $this->createForm(EditTeamType::class, $team);
        $form->submit($request->request->all());
        if ($form->isValid()) {
            $entityManager->persist($team);
            $entityManager->flush();
        }
        $teamView = $viewFactory->createOne($team);

        return $teamView;
    }

    /**
     * @SWG\Tag(name="Team")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Delete a team"
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     *
     * @Route(
     *     path="/api/teams/{team}",
     *     name="api__team_delete",
     *     methods={"DELETE"}
     * )
     *
     *
     * @param Team $team
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    public function delete(Team $team, EntityManagerInterface $entityManager)
    {
        $this->denyAccessUnlessGranted(TeamVoter::TEAM_EDIT, $this->getUser());
        $entityManager->remove($team);

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }
}
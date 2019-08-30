<?php

declare(strict_types=1);

namespace App\Controller\Api\Team;

use App\Context\ViewFactoryContext;
use App\Entity\Team;
use App\Entity\User;
use App\Entity\UserTeam;
use App\Factory\View\TeamViewFactory;
use App\Form\Request\Team\AddMemberType;
use App\Form\Request\Team\CreateTeamType;
use App\Form\Request\Team\EditTeamType;
use App\Model\Request\AddMemberRequest;
use App\Model\View\Team\MemberView;
use App\Model\View\Team\TeamView;
use App\Repository\TeamRepository;
use App\Repository\UserTeamRepository;
use App\Security\Voter\TeamVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;

/**
 * @Route(
 *     path="/api/teams"
 * )
 */
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
     *     path="/",
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
     *     path="/{team}",
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
     *     path="/",
     *     name="api_team_list",
     *     methods={"GET"}
     * )
     *
     *
     * @param TeamViewFactory $viewFactory
     * @param TeamRepository $teamRepository
     * @return TeamView[]
     */
    public function teams(TeamViewFactory $viewFactory, TeamRepository $teamRepository)
    {
        $this->denyAccessUnlessGranted(TeamVoter::TEAM_VIEW, $this->getUser());
        $teams = $teamRepository->findByUser($this->getUser());
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
     *     path="/{team}",
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
     *     path="/{team}",
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

    /**
     * @SWG\Tag(name="Team")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Team members",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type="App\Model\View\Team\MemberView")
     *     )
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     *
     * @Route(
     *     path="/{team}/members",
     *     methods={"GET"}
     * )
     * @param Request $request
     * @param Team $team
     * @param UserTeamRepository $userTeamRepository
     * @return mixed
     */
    public function members(Request $request, Team $team, UserTeamRepository $userTeamRepository)
    {
        $ids = $request->query->get('ids', []);
        $usersTeams = $userTeamRepository->findMembers($team, $ids);

        return MemberView::createMany($usersTeams);
    }

    /**
     * @SWG\Tag(name="Team")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=\App\Form\Request\Team\AddMemberType::class)
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Add team member",
     *     @Model(type="\App\Model\View\Team\MemberView")
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     * @Route(
     *     path="/members",
     *     methods={"POST"}
     * )
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return MemberView|\Symfony\Component\Form\FormInterface
     * @throws \Exception
     */
    public function addMember(Request $request, EntityManagerInterface $entityManager)
    {
        $this->denyAccessUnlessGranted(TeamVoter::TEAM_ADD_MEMBER, $this->getUser());
        $userTeam = new UserTeam();
        $form = $this->createForm(AddMemberType::class, $userTeam);
        $form->submit($request->request->all());

        if(!$form->isValid()) {
            return $form;
        } else {
            $entityManager->persist($userTeam);
            $entityManager->flush();
        }

        return MemberView::create($userTeam);
    }

    /**
     * @SWG\Tag(name="Team")
     * @SWG\Response(
     *     response=204,
     *     description="Remove team member"
     * )
     *
     * @Route(
     *     path="/{team}/members/{user}",
     *     methods={"DELETE"}
     * )
     * @param Team $team
     * @param User $user
     * @param UserTeamRepository $userTeamRepository
     * @return JsonResponse
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function removeMember(Team $team, User $user, UserTeamRepository $userTeamRepository): JsonResponse
    {
        $this->denyAccessUnlessGranted(TeamVoter::TEAM_REMOVE_MEMBER, $this->getUser());

        $userTeam = $userTeamRepository->findOneByUserAndTeam($user, $team);
        if (!$userTeam instanceof UserTeam) {
            throw new NotFoundHttpException('User Team not found');
        }

        $userTeamRepository->remove($userTeam);

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }
}
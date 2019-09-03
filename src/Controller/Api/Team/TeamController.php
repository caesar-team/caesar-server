<?php

declare(strict_types=1);

namespace App\Controller\Api\Team;

use App\Context\ViewFactoryContext;
use App\Entity\Team;
use App\Entity\User;
use App\Entity\UserTeam;
use App\Form\Request\Team\AddMemberType;
use App\Form\Request\Team\CreateTeamType;
use App\Form\Request\Team\EditTeamType;
use App\Model\View\Team\MemberView;
use App\Model\View\Team\TeamView;
use App\Repository\TeamRepository;
use App\Repository\UserTeamRepository;
use App\Security\Voter\TeamVoter;
use App\Security\Voter\UserTeamVoter;
use App\Services\TeamManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
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
     *     name="api_team_create",
     *     methods={"POST"}
     * )
     *
     *
     * @param Request $request
     * @param ViewFactoryContext $viewFactoryContext
     * @param EntityManagerInterface $entityManager
     * @param TeamManager $teamManager
     *
     * @return TeamView|FormInterface
     * @throws \Exception
     */
    public  function create(
        Request $request,
        ViewFactoryContext $viewFactoryContext,
        EntityManagerInterface $entityManager,
        TeamManager $teamManager
    )
    {
        $this->denyAccessUnlessGranted(TeamVoter::TEAM_CREATE, $this->getUser());

        $team = new Team();
        $form = $this->createForm(CreateTeamType::class, $team);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $form;
        }
        $entityManager->persist($team);
        $teamManager->addTeamToUser($this->getUser(), UserTeam::USER_ROLE_ADMIN, $team);
        $entityManager->flush();

        $teamView = $viewFactoryContext->view($team);

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
     * @param ViewFactoryContext $viewFactoryContext
     * @return TeamView
     */
    public function team(Team $team, ViewFactoryContext $viewFactoryContext)
    {
        $this->denyAccessUnlessGranted(UserTeamVoter::USER_TEAM_VIEW, $team);
        $teamView = $viewFactoryContext->view($team);

        return $teamView;
    }

    /**
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
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     *
     * @Route(
     *     name="api_team_list",
     *     methods={"GET"}
     * )
     *
     *
     * @param ViewFactoryContext $viewFactoryContext
     * @param TeamRepository $teamRepository
     * @return TeamView[]
     */
    public function teams(ViewFactoryContext $viewFactoryContext, TeamRepository $teamRepository)
    {
        /** @var User $user */
        $user = $this->getUser();
        if ($user->hasRole(User::ROLE_ADMIN) || $user->hasRole(User::ROLE_SUPER_ADMIN)) {
            $teams = $teamRepository->findAllExceptDefault();

        } else {
            $teams = $teamRepository->findByUserExceptDefault($this->getUser());
        }

        $teamView = $viewFactoryContext->viewList($teams);

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
     * @param ViewFactoryContext $viewFactoryContext
     * @param EntityManagerInterface $entityManager
     * @return TeamView
     */
    public function update(Team $team, Request $request, ViewFactoryContext $viewFactoryContext, EntityManagerInterface $entityManager)
    {
        $this->denyAccessUnlessGranted(UserTeamVoter::USER_TEAM_EDIT, $team);

        $form = $this->createForm(EditTeamType::class, $team);
        $form->submit($request->request->all());
        if ($form->isValid()) {
            $entityManager->flush();
        }
        $teamView = $viewFactoryContext->view($team);

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
        $this->denyAccessUnlessGranted(TeamVoter::TEAM_CREATE, $this->getUser());
        $entityManager->remove($team);
        $entityManager->flush();

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
        $this->denyAccessUnlessGranted(UserTeamVoter::USER_TEAM_VIEW, $team);
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
     * @return MemberView|FormInterface
     * @throws \Exception
     */
    public function addMember(Request $request, EntityManagerInterface $entityManager)
    {
        $userTeam = new UserTeam();
        $form = $this->createForm(AddMemberType::class, $userTeam);
        $form->submit($request->request->all());

        if(!$form->isValid()) {
            return $form;
        } else {
            $this->denyAccessUnlessGranted(UserTeamVoter::USER_TEAM_EDIT, $userTeam->getTeam());
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
        $userTeam = $userTeamRepository->findOneByUserAndTeam($user, $team);
        if (!$userTeam instanceof UserTeam) {
            throw new NotFoundHttpException('User Team not found');
        }

        $this->denyAccessUnlessGranted(UserTeamVoter::USER_TEAM_REMOVE_MEMBER, $team);
        //todo: удалять расшаренные на него итемы
        $userTeamRepository->remove($userTeam);

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route(
     *     path="/{team}/lists",
     *     methods={"GET"}
     * )
     * @param Team $team
     * @param ViewFactoryContext $viewFactoryContext
     * @return mixed
     */
    public function lists(Team $team, ViewFactoryContext $viewFactoryContext)
    {
        $lists = $viewFactoryContext->viewList($team->getLists()->getChildLists()->toArray());
        array_push($lists, $viewFactoryContext->view($team->getInbox()));
        array_push($lists, $viewFactoryContext->view($team->getTrash()));

        return $lists;
    }
}
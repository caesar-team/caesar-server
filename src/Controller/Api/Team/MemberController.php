<?php

declare(strict_types=1);

namespace App\Controller\Api\Team;

use App\Controller\AbstractController;
use App\Entity\Team;
use App\Entity\User;
use App\Entity\UserTeam;
use App\Event\Team\LeaveTeamEvent;
use App\Factory\View\Team\MemberViewFactory;
use App\Factory\View\Team\TeamViewFactory;
use App\Form\Type\Request\Team\BatchCreateMemberRequestType;
use App\Form\Type\Request\Team\CreateMemberRequestType;
use App\Form\Type\Request\Team\EditUserTeamType;
use App\Model\Query\MemberListQuery;
use App\Model\View\Team\MemberView;
use App\Model\View\Team\TeamView;
use App\Repository\UserTeamRepository;
use App\Request\Team\BatchCreateMemberRequest;
use App\Request\Team\CreateMemberRequest;
use App\Request\Team\EditUserTeamRequest;
use App\Security\Voter\TeamVoter;
use App\Security\Voter\UserTeamVoter;
use App\Team\MemberCreator;
use Fourxxi\RestRequestError\Exception\FormInvalidRequestException;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/api/teams")
 * @SWG\Response(
 *     response=401,
 *     description="Unauthorized"
 * )
 */
final class MemberController extends AbstractController
{
    /**
     * Get default team members.
     *
     * @SWG\Tag(name="Team / Member")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Default team members",
     *     @SWG\Schema(type="array", @Model(type=MemberView::class))
     * )
     *
     * @Route(
     *     path="/default/members",
     *     methods={"GET"}
     * )
     *
     * @return MemberView[]
     */
    public function defaultTeamMembers(UserTeamRepository $userTeamRepository, MemberViewFactory $viewFactory): array
    {
        $team = $this->getDefaultTeam();

        $this->denyAccessUnlessGranted(UserTeamVoter::VIEW, $team->getUserTeamByUser($this->getUser()));
        $usersTeams = $userTeamRepository->findMembersByTeam($team);

        return $viewFactory->createCollection($usersTeams);
    }

    /**
     * Get team members.
     *
     * @SWG\Tag(name="Team / Member")
     *
     * @SWG\Parameter(
     *     name="ids",
     *     in="query",
     *     description="User ids",
     *     type="array",
     *     @SWG\Items(type="string")
     * )
     * @SWG\Parameter(
     *     name="without_keypair",
     *     in="query",
     *     description="Get members without keypairs",
     *     type="boolean",
     * )
     *
     * @SWG\Response(
     *     response=200,
     *     description="Team members",
     *     @SWG\Schema(type="array", @Model(type=MemberView::class))
     * )
     *
     * @Route(
     *     path="/{team}/members",
     *     name="api_team_members",
     *     methods={"GET"}
     * )
     *
     * @return MemberView[]
     */
    public function members(Request $request, Team $team, UserTeamRepository $repository, MemberViewFactory $viewFactory)
    {
        $this->denyAccessUnlessGranted(UserTeamVoter::VIEW, $team->getUserTeamByUser($this->getUser()));

        return $viewFactory->createCollection(
            $repository->getMembersByQuery(
                new MemberListQuery($team, $request)
            )
        );
    }

    /**
     * Add batch members to team.
     *
     * @SWG\Tag(name="Team / Member")
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=BatchCreateMemberRequestType::class)
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Add team member",
     *     @SWG\Schema(type="array", @Model(type=MemberView::class))
     * )
     *
     * @Route(
     *     path="/{team}/members/batch",
     *     name="api_team_member_batch",
     *     methods={"POST"}
     * )
     */
    public function batchMember(
        Request $request,
        Team $team,
        MemberViewFactory $viewFactory,
        MemberCreator $memberCreator
    ): array {
        $this->denyAccessUnlessGranted(UserTeamVoter::ADD, $team->getUserTeamByUser($this->getUser()));

        $batchRequest = new BatchCreateMemberRequest($team);

        $form = $this->createForm(BatchCreateMemberRequestType::class, $batchRequest);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            throw new FormInvalidRequestException($form);
        }

        $result = [];
        foreach ($batchRequest->getMembers() as $memberRequest) {
            if (isset($result[$memberRequest->getUser()->getId()->toString()])) {
                continue;
            }

            $member = $memberCreator->createAndSave($memberRequest);
            $result[$memberRequest->getUser()->getId()->toString()] = $member->getUserTeam();
        }

        return $viewFactory->createCollection(array_values($result));
    }

    /**
     * Add member to team.
     *
     * @SWG\Tag(name="Team / Member")
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=CreateMemberRequestType::class)
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Add team member",
     *     @Model(type=MemberView::class)
     * )
     *
     * @Route(
     *     path="/{team}/members",
     *     name="api_team_member_add",
     *     methods={"POST"}
     * )
     */
    public function addMember(
        Request $request,
        Team $team,
        MemberCreator $memberCreator,
        MemberViewFactory $viewFactory
    ): MemberView {
        $this->denyAccessUnlessGranted(UserTeamVoter::ADD, $team->getUserTeamByUser($this->getUser()));

        $createRequest = new CreateMemberRequest($team);

        $form = $this->createForm(CreateMemberRequestType::class, $createRequest);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            throw new FormInvalidRequestException($form);
        }
        $member = $memberCreator->createAndSave($createRequest);

        return $viewFactory->createSingle($member->getUserTeam());
    }

    /**
     * Remove team member.
     *
     * @SWG\Tag(name="Team / Member")
     * @SWG\Response(
     *     response=204,
     *     description="Remove team member"
     * )
     *
     * @Route(
     *     path="/{team}/members/{user}",
     *     name="api_team_member_remove",
     *     methods={"DELETE"}
     * )
     */
    public function removeMember(
        Team $team,
        User $user,
        UserTeamRepository $userTeamRepository,
        EventDispatcherInterface $dispatcher
    ): JsonResponse {
        $userTeam = $userTeamRepository->findOneByUserAndTeam($user, $team);
        if (!$userTeam instanceof UserTeam) {
            throw new NotFoundHttpException('User Team not found');
        }

        $this->denyAccessUnlessGranted(UserTeamVoter::REMOVE, $userTeam);

        $userTeamRepository->remove($userTeam);
        $dispatcher->dispatch(new LeaveTeamEvent($team, $user));

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }

    /**
     * Leave team.
     *
     * @SWG\Tag(name="Team / Member")
     * @SWG\Response(
     *     response=200,
     *     description="Leave team member",
     *     @Model(type=TeamView::class)
     * )
     *
     * @Route(
     *     path="/{team}/leave",
     *     name="api_team_member_leave",
     *     methods={"POST"}
     * )
     */
    public function leaveTeam(
        Team $team,
        TeamViewFactory $viewFactory,
        UserTeamRepository $userTeamRepository,
        EventDispatcherInterface $dispatcher
    ): TeamView {
        $this->denyAccessUnlessGranted(TeamVoter::LEAVE, $team);
        $userTeam = $team->getUserTeamByUser($this->getUser());

        $userTeamRepository->remove($userTeam);
        $dispatcher->dispatch(new LeaveTeamEvent($team, $this->getUser()));

        return $viewFactory->createSingle($team);
    }

    /**
     * Edit team member.
     *
     * @SWG\Tag(name="Team / Member")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=EditUserTeamType::class)
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Edit team member",
     *     @Model(type=MemberView::class)
     * )
     *
     * @Route(
     *     path="/{team}/members/{user}",
     *     name="api_team_member_edit",
     *     methods={"PATCH"}
     * )
     */
    public function editMember(
        Request $request,
        Team $team,
        User $user,
        MemberViewFactory $viewFactory,
        UserTeamRepository $userTeamRepository
    ): MemberView {
        $userTeam = $userTeamRepository->findOneByUserAndTeam($user, $team);
        if (!$userTeam instanceof UserTeam) {
            throw new NotFoundHttpException('User Team not found');
        }

        $this->denyAccessUnlessGranted(UserTeamVoter::EDIT, $userTeam);

        $editUserTeamRequest = new EditUserTeamRequest();
        $form = $this->createForm(EditUserTeamType::class, $editUserTeamRequest);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            throw new FormInvalidRequestException($form);
        }

        $userTeam->setUserRole($editUserTeamRequest->getTeamRole());
        $userTeamRepository->save($userTeam);

        return $viewFactory->createSingle($userTeam);
    }
}

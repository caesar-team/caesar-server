<?php

declare(strict_types=1);

namespace App\Controller\Api\Team;

use App\Controller\AbstractController;
use App\Entity\Team;
use App\Entity\User;
use App\Entity\UserTeam;
use App\Factory\View\Team\MemberViewFactory;
use App\Form\Request\Team\AddMemberType;
use App\Form\Request\Team\EditUserTeamType;
use App\Mailer\MailRegistry;
use App\Model\Request\Team\EditUserTeamRequest;
use App\Model\View\Team\MemberView;
use App\Notification\MessengerInterface;
use App\Notification\Model\Message;
use App\Repository\UserTeamRepository;
use App\Security\Voter\UserTeamVoter;
use Fourxxi\RestRequestError\Exception\FormInvalidRequestException;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

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
        $usersTeams = $userTeamRepository->findMembers($team);

        return $viewFactory->createCollection($usersTeams);
    }

    /**
     * Get team members.
     *
     * @SWG\Tag(name="Team / Member")
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
    public function members(Request $request, Team $team, UserTeamRepository $userTeamRepository, MemberViewFactory $viewFactory)
    {
        $this->denyAccessUnlessGranted(UserTeamVoter::VIEW, $team->getUserTeamByUser($this->getUser()));
        $ids = $request->query->get('ids', []);
        $usersTeams = $userTeamRepository->findMembers($team, $ids);

        return $viewFactory->createCollection($usersTeams);
    }

    /**
     * Add member to team.
     *
     * @SWG\Tag(name="Team / Member")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=AddMemberType::class)
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Add team member",
     *     @Model(type=MemberView::class)
     * )
     *
     * @Route(
     *     path="/{team}/members/{user}",
     *     name="api_team_member_add",
     *     methods={"POST"}
     * )
     */
    public function addMember(
        Request $request,
        Team $team,
        User $user,
        MemberViewFactory $viewFactory,
        UserTeamRepository $repository,
        MessengerInterface $messenger
    ): MemberView {
        $this->denyAccessUnlessGranted(UserTeamVoter::EDIT, $team->getUserTeamByUser($this->getUser()));
        $userTeam = new UserTeam();
        $form = $this->createForm(AddMemberType::class, $userTeam);
        $form->submit($request->request->all());

        if (!$form->isValid()) {
            throw new FormInvalidRequestException($form);
        }

        $userTeam->setUser($user);
        $userTeam->setTeam($team);
        $repository->save($userTeam);

        $messenger->send(Message::createFromUser(
            $user,
            MailRegistry::ADD_TO_TEAM,
            [
                'team_name' => $team->getTitle(),
                'url' => $this->generateUrl('root', [], RouterInterface::ABSOLUTE_URL),
            ]
        ));

        return $viewFactory->createSingle($userTeam);
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
        UserTeamRepository $userTeamRepository
    ): JsonResponse {
        $this->denyAccessUnlessGranted(UserTeamVoter::REMOVE, $team->getUserTeamByUser($this->getUser()));

        $userTeam = $userTeamRepository->findOneByUserAndTeam($user, $team);
        if (!$userTeam instanceof UserTeam) {
            throw new NotFoundHttpException('User Team not found');
        }

        $userTeamRepository->remove($userTeam);

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }

    /**
     * Leave team.
     *
     * @SWG\Tag(name="Team / Member")
     * @SWG\Response(
     *     response=204,
     *     description="Remove team member"
     * )
     *
     * @Route(
     *     path="/{team}/leave",
     *     name="api_team_member_leave",
     *     methods={"POST"}
     * )
     */
    public function leaveTeam(Team $team, UserTeamRepository $userTeamRepository): JsonResponse
    {
        $userTeam = $team->getUserTeamByUser($this->getUser());
        if (null === $userTeam || $userTeam->hasRole(UserTeam::USER_ROLE_ADMIN)) {
            throw new NotFoundHttpException('User Team not found');
        }

        $userTeamRepository->remove($userTeam);

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
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
        $this->denyAccessUnlessGranted(UserTeamVoter::EDIT, $team->getUserTeamByUser($this->getUser()));

        $userTeam = $userTeamRepository->findOneByUserAndTeam($user, $team);
        if (!$userTeam instanceof UserTeam) {
            throw new NotFoundHttpException('User Team not found');
        }

        $editUserTeamRequest = new EditUserTeamRequest();
        $form = $this->createForm(EditUserTeamType::class, $editUserTeamRequest);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            throw new FormInvalidRequestException($form);
        }

        $userTeam->setUserRole($editUserTeamRequest->getUserRole());
        $userTeamRepository->save($userTeam);

        return $viewFactory->createSingle($userTeam);
    }
}

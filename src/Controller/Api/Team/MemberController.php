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
use App\Model\Request\Team\EditUserTeamRequest;
use App\Model\View\Team\MemberView;
use App\Repository\ItemRepository;
use App\Repository\UserTeamRepository;
use App\Security\Voter\UserTeamVoter;
use App\Utils\ItemExtractor;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\Form\FormInterface;
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
     *
     * @return MemberView|FormInterface
     */
    public function addMember(
        Request $request,
        Team $team,
        User $user,
        MemberViewFactory $viewFactory,
        UserTeamRepository $repository
    ) {
        $this->denyAccessUnlessGranted(UserTeamVoter::EDIT, $team->getUserTeamByUser($this->getUser()));
        $userTeam = new UserTeam();
        $form = $this->createForm(AddMemberType::class, $userTeam);
        $form->submit($request->request->all());

        if (!$form->isValid()) {
            return $form;
        }

        $userTeam->setUser($user);
        $userTeam->setTeam($team);
        $repository->save($userTeam);

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
        UserTeamRepository $userTeamRepository,
        ItemRepository $itemRepository
    ): JsonResponse {
        if (Team::DEFAULT_GROUP_ALIAS === $team->getAlias()) {
            throw new \LogicException('Illegal team');
        }

        $this->denyAccessUnlessGranted(UserTeamVoter::REMOVE, $team->getUserTeamByUser($this->getUser()));

        $userTeam = $userTeamRepository->findOneByUserAndTeam($user, $team);
        if (!$userTeam instanceof UserTeam) {
            throw new NotFoundHttpException('User Team not found');
        }

        $items = ItemExtractor::getTeamItemsForUser($team, $user);
        foreach ($items as $item) {
            $itemRepository->remove($item);
        }
        $itemRepository->flush();
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
     *
     * @return MemberView|FormInterface
     */
    public function editMember(
        Request $request,
        Team $team,
        User $user,
        MemberViewFactory $viewFactory,
        UserTeamRepository $userTeamRepository)
    {
        if (Team::DEFAULT_GROUP_ALIAS === $team->getAlias()) {
            throw new \LogicException('Illegal team');
        }

        $this->denyAccessUnlessGranted(UserTeamVoter::EDIT, $team->getUserTeamByUser($this->getUser()));

        $userTeam = $userTeamRepository->findOneByUserAndTeam($user, $team);
        if (!$userTeam instanceof UserTeam) {
            throw new NotFoundHttpException('User Team not found');
        }

        $editUserTeamRequest = new EditUserTeamRequest();
        $form = $this->createForm(EditUserTeamType::class, $editUserTeamRequest);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $form;
        }

        $userTeam->setUserRole($editUserTeamRequest->getUserRole());
        $userTeamRepository->save($userTeam);

        return $viewFactory->createSingle($userTeam);
    }
}

<?php

declare(strict_types=1);

namespace App\Controller\Api\User;

use App\Controller\AbstractController;
use App\Entity\Team;
use App\Entity\User;
use App\Entity\UserTeam;
use App\Factory\View\User\PublicUserKeyViewFactory;
use App\Factory\View\UserKeysViewFactory;
use App\Form\Request\Invite\PublicKeysRequestType;
use App\Form\Request\SaveKeysType;
use App\Model\Request\PublicKeysRequest;
use App\Model\View\User\PublicUserKeyView;
use App\Model\View\User\UserKeysView;
use App\Repository\UserRepository;
use App\Security\Voter\UserVoter;
use App\Services\InvitationManager;
use App\Services\TeamManager;
use Doctrine\ORM\EntityManagerInterface;
use Fourxxi\RestRequestError\Exception\FormInvalidRequestException;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/api")
 * @SWG\Response(
 *     response=401,
 *     description="Unauthorized"
 * )
 */
final class KeysController extends AbstractController
{
    /**
     * Get user public key.
     *
     * @SWG\Tag(name="Keys")
     * @SWG\Response(
     *     response=200,
     *     description="User public key",
     *     @Model(type=PublicUserKeyView::class)
     * )
     *
     * @Route(
     *     path="/key/{email}",
     *     name="api_user_get_public_key",
     *     methods={"GET"}
     * )
     * @Entity("user", expr="repository.findOneByEmail(email)")
     */
    public function publicKey(User $user, PublicUserKeyViewFactory $viewFactory): PublicUserKeyView
    {
        return $viewFactory->createSingle($user);
    }

    /**
     * Get users public keys by emails.
     *
     * @SWG\Tag(name="Keys")
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=PublicKeysRequestType::class)
     * )
     * @SWG\Response(
     *     response=200,
     *     description="User public key",
     *     @SWG\Schema(type="array", @Model(type=PublicUserKeyView::class))
     * )
     *
     * @Route(
     *     path="/key/batch",
     *     methods={"POST"}
     * )
     *
     * @return array<PublicUserKeyView>
     */
    public function batchPublicKeyAction(Request $request, PublicUserKeyViewFactory $factory, UserRepository $repository): array
    {
        $keysRequest = new PublicKeysRequest();
        $form = $this->createForm(PublicKeysRequestType::class, $keysRequest);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            throw new FormInvalidRequestException($form);
        }

        return $factory->createCollection(
            $repository->getUsersWithKeysByEmails($keysRequest->getEmails())
        );
    }

    /**
     * @SWG\Tag(name="Keys")
     *
     * @SWG\Response(
     *     response=200,
     *     description="List of user keys",
     *     @Model(type=UserKeysView::class)
     * )
     * @SWG\Response(
     *     response=204,
     *     description="User has no keys"
     * )
     *
     * @Route(
     *     path="/keys",
     *     name="api_keys_list",
     *     methods={"GET"}
     * )
     *
     * @return UserKeysView|null
     */
    public function keyList(UserKeysViewFactory $viewFactory)
    {
        /** @var User $user */
        $user = $this->getUser();
        //@todo @frontend candidate to refactoring, always return View
        if (!$user->hasKeys()) {
            return null;
        }

        return $viewFactory->createSingle($user);
    }

    /**
     * Update keys.
     *
     * @SWG\Tag(name="Keys")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=SaveKeysType::class)
     * )
     * @SWG\Response(
     *     response=204,
     *     description="Success keys update",
     * )
     *
     * @Route(
     *     path="/keys",
     *     name="api_keys_save",
     *     methods={"POST"}
     * )
     *
     * @throws \Exception
     */
    public function saveKeys(Request $request, EntityManagerInterface $entityManager, TeamManager $teamManager): void
    {
        $user = $this->getUser();

        $form = $this->createForm(SaveKeysType::class, $user);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            throw new FormInvalidRequestException($form);
        }

        /** @var User $oldUser */
        $oldUser = $entityManager->getUnitOfWork()->getOriginalEntityData($user);
        if (!$user->isFullUser()) {
            $user->setFlowStatus(User::FLOW_STATUS_FINISHED);
        } else {
            $this->setFlowStatusByPrivateKeys($oldUser, $user);
        }

        if ($user->isFullUser()) {
            InvitationManager::removeInvitation($user, $entityManager);
            $userTeam = $teamManager->findUserTeamByAlias($user, Team::DEFAULT_GROUP_ALIAS);
            if (null !== $userTeam) {
                $userTeam->setUserRole(UserTeam::USER_ROLE_MEMBER);
            }
        }

        $entityManager->flush();
    }

    /**
     * Update keys for user without keys.
     *
     * @SWG\Tag(name="Keys")
     * @SWG\Response(
     *     response=204,
     *     description="Success keys update",
     * )
     *
     * @Route(
     *     path="/keys/{email}",
     *     name="api_user_update_keys",
     *     methods={"POST"}
     * )
     * @Entity("user", expr="repository.findOneByEmail(email)")
     */
    public function updateKeys(Request $request, EntityManagerInterface $entityManager, User $user): void
    {
        $this->denyAccessUnlessGranted(UserVoter::UPDATE_KEY, $user);

        $form = $this->createForm(SaveKeysType::class, $user);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            throw new FormInvalidRequestException($form);
        }

        $user->setFlowStatus(User::FLOW_STATUS_CHANGE_PASSWORD);

        $entityManager->flush();
    }

    //@todo candidate to refactoring
    private function setFlowStatus(string $currentFlowStatus): string
    {
        if (User::FLOW_STATUS_CHANGE_PASSWORD === $currentFlowStatus) {
            return $currentFlowStatus;
        }

        return User::FLOW_STATUS_FINISHED;
    }

    //@todo candidate to refactoring
    private function setFlowStatusByPrivateKeys($oldUser, User $user)
    {
        if ($oldUser['encryptedPrivateKey'] !== $user->getEncryptedPrivateKey()) {
            $user->setFlowStatus($this->setFlowStatus($user->getFlowStatus()));
        }
    }
}

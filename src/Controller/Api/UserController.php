<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AbstractController;
use App\Entity\Team;
use App\Entity\Security\Invitation;
use App\Entity\Srp;
use App\Entity\User;
use App\Entity\UserTeam;
use App\Factory\View\SecurityBootstrapViewFactory;
use App\Factory\View\SelfUserInfoViewFactory;
use App\Factory\View\UserKeysViewFactory;
use App\Factory\View\UserListViewFactory;
use App\Factory\View\UserSecurityInfoViewFactory;
use App\Form\Query\UserQueryType;
use App\Form\Request\CreateInvitedUserType;
use App\Form\Request\Invite\PublicKeysRequestType;
use App\Form\Request\SaveKeysType;
use App\Form\Request\SendInvitesType;
use App\Form\Request\SendInviteType;
use App\Mailer\MailRegistry;
use App\Model\DTO\Message;
use App\Model\Query\UserQuery;
use App\Model\Request\PublicKeysRequest;
use App\Model\Request\SendInviteRequest;
use App\Model\Request\SendInviteRequests;
use App\Model\View\User\SelfUserInfoView;
use App\Model\View\User\UserKeysView;
use App\Model\View\User\UserView;
use App\Repository\UserRepository;
use App\Security\AuthorizationManager\InvitationEncoder;
use App\Services\GroupManager;
use App\Services\InvitationManager;
use App\Services\Messenger;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\Model;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Swagger\Annotations as SWG;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

final class UserController extends AbstractController
{
    /**
     * @SWG\Tag(name="User")
     *
     * @SWG\Response(
     *     response=200,
     *     description="User information response",
     *     @Model(type="\App\Model\View\User\SelfUserInfoView")
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     *
     * @Route(
     *     path="/api/user/self",
     *     name="api_user_get_info",
     *     methods={"GET"}
     * )
     * @Rest\View(serializerGroups={"public"})
     *
     * @param SelfUserInfoViewFactory $viewFactory
     *
     * @return SelfUserInfoView
     */
    public function userInfoAction(SelfUserInfoViewFactory $viewFactory)
    {
        /** @var User $user */
        $user = $this->getUser();

        return $user ? $viewFactory->create($user) : null;
    }

    /**
     * @SWG\Tag(name="Invitation")
     *
     * @SWG\Response(
     *     response=200,
     *     description="User public key",
     *     @Model(type="App\Model\View\User\UserKeysView", groups={"public"})
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     *
     * @Route(
     *     path="/api/key/{email}",
     *     name="api_user_get_public_key",
     *     methods={"GET"}
     * )
     * @Entity(
     *     "user",
     *     expr="repository.findByEmail(email)"
     * )
     * @Rest\View(serializerGroups={"public"})
     *
     * @param User                $user
     * @param UserKeysViewFactory $viewFactory
     *
     * @return UserKeysView
     */
    public function publicKeyAction(User $user, UserKeysViewFactory $viewFactory)
    {
        return $viewFactory->create($user);
    }

    /**
     * @SWG\Tag(name="User")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Users list",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type="\App\Model\View\User\UserView")
     *     )
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     *
     * @Route(
     *     path="/api/user",
     *     name="api_users_list",
     *     methods={"GET"}
     * )
     *
     * @param Request             $request
     * @param UserListViewFactory $factory
     *
     * @return UserView[]|array|FormInterface
     */
    public function userListAction(Request $request, UserListViewFactory $factory)
    {
        $userQuery = new UserQuery($this->getUser());

        $form = $this->createForm(UserQueryType::class, $userQuery);
        $form->submit($request->query->all());
        if (!$form->isValid()) {
            return $form;
        }

        $userCollection = $this->getDoctrine()->getRepository(User::class)->getByQuery($userQuery);

        return $factory->create($userCollection);
    }

    /**
     * @SWG\Tag(name="Keys")
     *
     * @SWG\Response(
     *     response=200,
     *     description="List of user keys",
     *     @Model(type="\App\Model\View\User\UserKeysView", groups={"key_detail_read"})
     * )
     * @SWG\Response(
     *     response=204,
     *     description="User has no keys"
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     *
     * @Route(
     *     path="/api/keys",
     *     name="api_keys_list",
     *     methods={"GET"}
     * )
     * @Rest\View(serializerGroups={"key_detail_read"})
     *
     * @param UserKeysViewFactory $viewFactory
     *
     * @return UserKeysView|null
     */
    public function keyListAction(UserKeysViewFactory $viewFactory)
    {
        /** @var User $user */
        $user = $this->getUser();

        return $viewFactory->create($user);
    }

    /**
     * @SWG\Tag(name="Keys")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=\App\Form\Request\SaveKeysType::class)
     * )
     * @SWG\Response(
     *     response=204,
     *     description="Success keys update",
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     *
     * @Route(
     *     path="/api/keys",
     *     name="api_keys_save",
     *     methods={"POST"}
     * )
     *
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     *
     * @param GroupManager $groupManager
     * @return FormInterface|null
     * @throws \Exception
     */
    public function saveKeysAction(Request $request, EntityManagerInterface $entityManager, GroupManager $groupManager)
    {
        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(SaveKeysType::class, $user);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $form;
        }

        /** @var User $oldUser */
        $oldUser = $entityManager->getUnitOfWork()->getOriginalEntityData($user);
        if (!$user->isFullUser()) {
            $user->setFlowStatus(User::FLOW_STATUS_FINISHED);

        } else {
            $this->setFlowStatusByPrivateKeys($oldUser, $user);
        }

        if ($user->isFullUser()) {
            $this->removeInvitation($user, $entityManager);
            $userGroup = $groupManager->findUserGroupByAlias($user, Team::DEFAULT_GROUP_ALIAS);
            $userGroup->setUserRole(UserTeam::USER_ROLE_MEMBER);
        }

        $entityManager->flush();

        return null;
    }

    /**
     * @SWG\Tag(name="Invitation")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=\App\Form\Request\CreateInvitedUserType::class)
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Success user created update",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(
     *             type="string",
     *             property="user",
     *             example="553d9b8d-fce0-4a53-8cba-f7d334160bc4"
     *         )
     *     )
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     *
     * @Route(
     *     path="/api/user",
     *     name="api_user_create",
     *     methods={"POST"}
     * )
     *
     * @param Request $request
     * @param UserRepository $userRepository
     * @param EntityManagerInterface $entityManager
     *
     * @param GroupManager $groupManager
     * @return array|FormInterface
     * @throws \Exception
     */
    public function createUser(
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        GroupManager $groupManager
    )
    {
        /** @var User $user */
        $user = $userRepository->findOneBy(['email' => $request->request->get('email')]);
        if (!$user) {
            $user = new User(new Srp());
        } elseif (null !== $user->getPublicKey()) {
            $message = $this->translator->trans('app.exception.user_already_exists');
            throw new BadRequestHttpException($message);
        }

        $form = $this->createForm(CreateInvitedUserType::class, $user);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $form;
        }

        if ($user->isFullUser()) {
            $groupManager->addGroupToUser($user, UserTeam::USER_ROLE_PRETENDER);
            $this->removeInvitation($user, $entityManager);
            $invitation = new Invitation();
            $invitation->setHash($user->getEmail());
            $entityManager->persist($invitation);
        }

        $entityManager->persist($user);
        $entityManager->flush();

        return [
            'user' => $user->getId()->toString(),
        ];
    }

    /**
     * @SWG\Tag(name="Security")
     *
     * @SWG\Response(
     *     response=200,
     *     description="User's permissions",
     *     @Model(type="\App\Model\View\User\UserSecurityInfoView")
     * )
     * )
     *
     * @SWG\Response(
     *     response=401,
     *     description="Access denied"
     * )
     *
     * @Route(
     *     path="/api/user/permissions",
     *     name="api_user_permissions",
     *     methods={"GET"}
     * )
     *
     * @param UserSecurityInfoViewFactory $infoViewFactory
     * @return JsonResponse
     */
    public function permissions(UserSecurityInfoViewFactory $infoViewFactory): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        return new JsonResponse($infoViewFactory->create($user));
    }

    /**
     * @SWG\Tag(name="Security")
     *
     * @SWG\Response(
     *     response=200,
     *     description="User's security bootstrap",
     *     @Model(type="\App\Model\View\User\SecurityBootstrapView")
     * )
     * )
     *
     * @SWG\Response(
     *     response=401,
     *     description="Access denied"
     * )
     *
     * @Route(
     *     path="/api/user/security/bootstrap",
     *     name="api_user_security_bootstrap",
     *     methods={"GET"}
     * )
     *
     * @param SecurityBootstrapViewFactory $bootstrapViewFactory
     * @return JsonResponse
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException
     */
    public function bootstrap(SecurityBootstrapViewFactory $bootstrapViewFactory): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        return new JsonResponse($bootstrapViewFactory->create($user));
    }

    /**
     * @SWG\Tag(name="Invitation")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type="\App\Form\Request\SendInviteType")
     * )
     * @SWG\Response(
     *     response=201,
     *     description="Success message sent"
     * )
     * @SWG\Response(
     *     response=400,
     *     description="Returns send mail errors",
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     *
     * @Route(
     *     "/api/invitation",
     *     name="api_send_invitation",
     *     methods={"POST"}
     * )
     *
     * @param Request $request
     * @param Messenger $messenger
     * @param LoggerInterface $logger
     * @return FormInterface
     * @throws \Exception
     */
    public function sendInvitation(Request $request, Messenger $messenger, LoggerInterface $logger)
    {
        $sendRequest = new SendInviteRequest();

        $form = $this->createForm(SendInviteType::class, $sendRequest);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $form;
        }

        $message = new Message($sendRequest->getUser()->getId()->toString(),$sendRequest->getUser()->getEmail(), MailRegistry::INVITE_SEND_MESSAGE, [
            'url' => $sendRequest->getUrl(),
        ]);
        $messenger->send($sendRequest->getUser(), $message);

        $logger->debug('Registered in UserController::sendInvitation');
        $logger->debug(sprintf('Username: %s', $sendRequest->getUser()->getUsername()));

        return null;
    }

    /**
     * @SWG\Tag(name="Invitation")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type="\App\Form\Request\SendInvitesType")
     * )
     * @SWG\Response(
     *     response=201,
     *     description="Success message sent"
     * )
     * @SWG\Response(
     *     response=400,
     *     description="Returns send mail errors",
     * )
     *
     * @Route(
     *     "/api/invitations",
     *     name="api_send_invitations",
     *     methods={"POST"}
     * )
     *
     * @param Request $request
     * @param Messenger $messenger
     * @return null|FormInterface
     * @throws \Exception
     */
    public function sendInvitations(Request $request, Messenger $messenger)
    {
        $sendRequests = new SendInviteRequests();

        $form = $this->createForm(SendInvitesType::class, $sendRequests);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $form;
        }

        foreach ($sendRequests->getMessages() as $requestMessage) {
            $message = new Message($requestMessage->getUser()->getId()->toString(), $requestMessage->getUser()->getEmail(), MailRegistry::INVITE_SEND_MESSAGE, [
                'url' => $requestMessage->getUrl(),
            ]);
            $messenger->send($requestMessage->getUser(), $message);
        }

        return null;
    }

    /**
     * @SWG\Tag(name="Invitation")
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type="\App\Form\Request\Invite\PublicKeysRequestType")
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     *
     * @Route(
     *     path="/api/key/batch",
     *     methods={"POST"}
     * )
     * @Rest\View(serializerGroups={"public"})
     *
     * @param Request $request
     * @param UserKeysViewFactory $viewFactory
     * @return array|FormInterface
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function batchPublicKeyAction(Request $request, UserKeysViewFactory $viewFactory, UserRepository $userRepository)
    {
        $keysRequest = new PublicKeysRequest();
        $form = $this->createForm(PublicKeysRequestType::class, $keysRequest);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $form;
        }

        $view = [];
        foreach ($keysRequest->getEmails() as $email) {
            if ($user = $userRepository->findByEmail($email)) {
                $view[] = $viewFactory->create($user);
            }
        }

        return $view;
    }

    /**
     * @Route(
     *     path="/api/user/batch",
     *     methods={"POST"}
     * )
     * @param Request $request
     * @return array|FormInterface
     * @throws \Exception
     */
    public function batchCreateUser(
        Request $request,
        EntityManagerInterface $entityManager,
        GroupManager $groupManager
    )
    {
        $requestUsers = $request->request->get('users');
        $users = [];
        foreach ($requestUsers as $requestUser) {
            $user = new User(new Srp());
            $form = $this->createForm(CreateInvitedUserType::class, $user);
            $form->submit($requestUser);
            if (!$form->isValid()) {
                return $form;
            }

            if ($user->isFullUser()) {
                $groupManager->addGroupToUser($user, UserTeam::USER_ROLE_PRETENDER);
                $this->removeInvitation($user, $entityManager);
                $invitation = new Invitation();
                $invitation->setHash($user->getEmail());
                $entityManager->persist($invitation);
            }

            $entityManager->persist($user);
            $users[] = $user->getId()->toString();
        }

        $entityManager->flush();

        return ["users" => $users];
    }

    private function setFlowStatus(string $currentFlowStatus): string
    {
        if (User::FLOW_STATUS_CHANGE_PASSWORD === $currentFlowStatus) {
            return $currentFlowStatus;
        }

        return User::FLOW_STATUS_FINISHED;
    }

    private function setFlowStatusByPrivateKeys($oldUser, User $user)
    {
        if ($oldUser['encryptedPrivateKey'] !== $user->getEncryptedPrivateKey()) {
            $user->setFlowStatus($this->setFlowStatus($user->getFlowStatus()));
        }
    }

    private function removeInvitation(User $user, EntityManagerInterface $entityManager)
    {
        InvitationManager::removeInvitation($user, $entityManager);
    }
}

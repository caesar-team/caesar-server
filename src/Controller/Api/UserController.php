<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Context\ViewFactoryContext;
use App\Controller\AbstractController;
use App\Entity\Security\Invitation;
use App\Entity\Srp;
use App\Entity\Team;
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
use App\Services\InvitationManager;
use App\Services\Messenger;
use App\Services\TeamManager;
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
     */
    public function userInfo(SelfUserInfoViewFactory $viewFactory): ?SelfUserInfoView
    {
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
     */
    public function publicKey(User $user, UserKeysViewFactory $viewFactory): ?UserKeysView
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
     * @return UserView[]|array|FormInterface
     */
    public function userList(Request $request, UserListViewFactory $factory, UserRepository $repository)
    {
        $userQuery = new UserQuery($this->getUser());

        $form = $this->createForm(UserQueryType::class, $userQuery);
        $form->submit($request->query->all());
        if (!$form->isValid()) {
            return $form;
        }

        $userCollection = $repository->getByQuery($userQuery);

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
     * @return UserKeysView|null
     */
    public function keyList(UserKeysViewFactory $viewFactory)
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
     * @throws \Exception
     *
     * @return FormInterface|null
     */
    public function saveKeys(Request $request, EntityManagerInterface $entityManager, TeamManager $teamManager)
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
            $userTeam = $teamManager->findUserTeamByAlias($user, Team::DEFAULT_GROUP_ALIAS);
            $userTeam->setUserRole(UserTeam::USER_ROLE_MEMBER);
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
     * @throws \Exception
     *
     * @return array|FormInterface
     */
    public function createUser(
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        TeamManager $teamManager
    ) {
        $user = $userRepository->findOneBy(['email' => $request->request->get('email')]);
        if (null === $user) {
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
            $teamManager->addTeamToUser($user, UserTeam::USER_ROLE_PRETENDER);
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
     * @throws \Exception
     */
    public function sendInvitation(Request $request, Messenger $messenger, LoggerInterface $logger): ?FormInterface
    {
        $sendRequest = new SendInviteRequest();

        $form = $this->createForm(SendInviteType::class, $sendRequest);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $form;
        }

        $message = new Message($sendRequest->getUser()->getId()->toString(), $sendRequest->getUser()->getEmail(), MailRegistry::INVITE_SEND_MESSAGE, [
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
     * @throws \Exception
     *
     * @return FormInterface|null
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
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @return array|FormInterface
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
            if ($user = $userRepository->findOneWithPublicKeyByEmail($email)) {
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
     *
     * @throws \Exception
     *
     * @return array|FormInterface
     */
    public function batchCreateUser(
        Request $request,
        EntityManagerInterface $entityManager,
        TeamManager $groupManager
    ) {
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
                $groupManager->addTeamToUser($user, UserTeam::USER_ROLE_PRETENDER);
                $this->removeInvitation($user, $entityManager);
                $invitation = new Invitation();
                $invitation->setHash($user->getEmail());
                $entityManager->persist($invitation);
            }

            $entityManager->persist($user);
            $users[] = $user->getId()->toString();
        }

        $entityManager->flush();

        return ['users' => $users];
    }

    /**
     * @SWG\Tag(name="User")
     * @SWG\Parameter(
     *     name="ids",
     *     in="query",
     *     description="users ids",
     *     type="array",
     *     @Model(type="App\Model\View\User\UserView")
     * )
     * @SWG\Response(
     *     response=200,
     *     description="List of users",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type="App\Model\View\User\UserView")
     *     )
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     *
     * @Route(
     *     path="/api/users",
     *     methods={"GET"}
     * )
     *
     * @return UserView[]
     */
    public function users(Request $request, UserRepository $userRepository, ViewFactoryContext $viewFactoryContext): array
    {
        $ids = $request->query->get('ids', []);

        $users = 0 < count($ids) ? $userRepository->findByIds($ids) : $userRepository->findAllExceptAnonymous();

        return $viewFactoryContext->viewList($users);
    }

    /**
     * @SWG\Tag(name="User")
     *
     * @SWG\Response(
     *     response=200,
     *     description="User by email",
     *     @Model(type="\App\Model\View\User\UserView", groups={"search_by_email"})
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     *
     * @Route(
     *     path="/api/users/email/{email}",
     *     methods={"GET"}
     * )
     * @Rest\View(serializerGroups={"search_by_email"})
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @return UserView|null
     */
    public function searchOneByEmail(string $email, UserRepository $userRepository, ViewFactoryContext $viewFactoryContext)
    {
        $user = $userRepository->findOneByEmail($email);

        return $user ? $viewFactoryContext->view($user) : null;
    }

    /**
     * @SWG\Tag(name="User")
     *
     * @SWG\Response(
     *     response=200,
     *     description="User by part of email",
     *     @Model(type="\App\Model\View\User\UserView", groups={"search_by_email"})
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     *
     * @Route(
     *     path="/api/users/search/{partOfEmail}",
     *     methods={"GET"}
     * )
     * @Rest\View(serializerGroups={"search_by_email"})
     *
     * @return UserView[]|array
     */
    public function autocompleteForEmail(string $partOfEmail, UserRepository $userRepository, ViewFactoryContext $viewFactoryContext): array
    {
        $users = $userRepository->findByPartOfEmail($partOfEmail);

        return $viewFactoryContext->viewList($users);
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

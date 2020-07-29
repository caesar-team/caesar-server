<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AbstractController;
use App\Entity\Security\Invitation;
use App\Entity\Srp;
use App\Entity\User;
use App\Factory\View\SecurityBootstrapViewFactory;
use App\Factory\View\UserSecurityInfoViewFactory;
use App\Form\Request\CreateInvitedUserType;
use App\Form\Request\SendInvitesType;
use App\Form\Request\SendInviteType;
use App\Limiter\Inspector\UserCountInspector;
use App\Limiter\Limiter;
use App\Limiter\Model\LimitCheck;
use App\Mailer\MailRegistry;
use App\Model\Request\SendInviteRequest;
use App\Model\Request\SendInviteRequests;
use App\Model\View\User\SecurityBootstrapView;
use App\Model\View\User\UserSecurityInfoView;
use App\Notification\MessengerInterface;
use App\Notification\Model\Message;
use App\Repository\UserRepository;
use App\Services\InvitationManager;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Psr\Log\LoggerInterface;
use Swagger\Annotations as SWG;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

final class UserController extends AbstractController
{
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
        Limiter $limiter
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
            $limiter->check([
                new LimitCheck(UserCountInspector::class, 1),
            ]);

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
     *     @Model(type=UserSecurityInfoView::class)
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
    public function permissions(UserSecurityInfoViewFactory $infoViewFactory): UserSecurityInfoView
    {
        return $infoViewFactory->createSingle($this->getUser());
    }

    /**
     * @SWG\Tag(name="Security")
     *
     * @SWG\Response(
     *     response=200,
     *     description="User's security bootstrap",
     *     @Model(type=SecurityBootstrapView::class)
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
     */
    public function bootstrap(SecurityBootstrapViewFactory $bootstrapViewFactory): SecurityBootstrapView
    {
        return $bootstrapViewFactory->createSingle($this->getUser());
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
    public function sendInvitation(Request $request, MessengerInterface $messenger, LoggerInterface $logger): ?FormInterface
    {
        $sendRequest = new SendInviteRequest();

        $form = $this->createForm(SendInviteType::class, $sendRequest);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $form;
        }

        $messenger->send(Message::createFromUser(
            $sendRequest->getUser(),
            MailRegistry::INVITE_SEND_MESSAGE,
            ['url' => $sendRequest->getUrl()]
        ));

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
    public function sendInvitations(Request $request, MessengerInterface $messenger)
    {
        $sendRequests = new SendInviteRequests();

        $form = $this->createForm(SendInvitesType::class, $sendRequests);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $form;
        }

        foreach ($sendRequests->getMessages() as $requestMessage) {
            $messenger->send(Message::createFromUser(
                $requestMessage->getUser(),
                MailRegistry::INVITE_SEND_MESSAGE,
                ['url' => $requestMessage->getUrl()]
            ));
        }

        return null;
    }

    /**
     * @Route(
     *     path="/api/user/batch",
     *     methods={"POST"},
     *     name="api_user_batch_create"
     * )
     *
     * @throws \Exception
     *
     * @return array|FormInterface
     */
    public function batchCreateUser(
        Request $request,
        EntityManagerInterface $entityManager,
        Limiter $limiter
    ) {
        $requestUsers = $request->request->get('users');

        $newUsers = [];
        foreach ($requestUsers as $requestUser) {
            $user = new User(new Srp());
            $form = $this->createForm(CreateInvitedUserType::class, $user);
            $form->submit($requestUser);
            if (!$form->isValid()) {
                return $form;
            }
            $newUsers[] = $user;
        }

        $fullUsers = array_filter($newUsers, static function (User $user) {
            return !$user->hasRole(User::ROLE_ANONYMOUS_USER);
        });

        $limiter->check([
            new LimitCheck(UserCountInspector::class, count($fullUsers)),
        ]);

        $users = [];
        foreach ($newUsers as $user) {
            if ($user->isFullUser()) {
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

    private function removeInvitation(User $user, EntityManagerInterface $entityManager)
    {
        InvitationManager::removeInvitation($user, $entityManager);
    }
}

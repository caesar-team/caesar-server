<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AbstractController;
use App\Entity\Security\Invitation;
use App\Entity\User;
use App\Factory\Entity\UserFactory;
use App\Factory\View\SecurityBootstrapViewFactory;
use App\Factory\View\User\UserViewFactory;
use App\Form\Type\Request\Invite\SendInvitesType;
use App\Form\Type\Request\Invite\SendInviteType;
use App\Form\Type\Request\User\CreateBatchInvitedUserRequestType;
use App\Form\Type\Request\User\CreateInvitedUserRequestType;
use App\Invitation\InvitationReplacer;
use App\Limiter\Inspector\UserCountInspector;
use App\Limiter\LimiterInterface;
use App\Limiter\Model\LimitCheck;
use App\Mailer\MailRegistry;
use App\Model\View\User\SecurityBootstrapView;
use App\Model\View\User\UserView;
use App\Notification\MessengerInterface;
use App\Notification\Model\Message;
use App\Request\Invite\SendInviteRequest;
use App\Request\Invite\SendInviteRequests;
use App\Request\User\CreateBatchInvitedUserRequest;
use App\Request\User\CreateInvitedUserRequest;
use App\Security\Fingerprint\FingerprintRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Fourxxi\RestRequestError\Exception\FormInvalidRequestException;
use Nelmio\ApiDocBundle\Annotation\Model;
use Psr\Log\LoggerInterface;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class UserController extends AbstractController
{
    /**
     * @SWG\Tag(name="User")
     * @SWG\Response(
     *     response=200,
     *     description="Success user logout"
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     * @Route(
     *     path="/api/logout",
     *     name="api_user_logout",
     *     methods={"POST"}
     * )
     */
    public function logout(FingerprintRepositoryInterface $repository)
    {
        $repository->removeFingerprints($this->getUser());
    }

    /**
     * @SWG\Tag(name="Invitation")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=CreateInvitedUserRequestType::class)
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Success user created or updated keys",
     *     @Model(type=UserView::class)
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
     */
    public function createUser(
        Request $request,
        UserFactory $factory,
        UserViewFactory $viewFactory,
        InvitationReplacer $invitationReplacer,
        EntityManagerInterface $entityManager,
        LimiterInterface $limiter
    ): UserView {
        $createRequest = new CreateInvitedUserRequest();
        $form = $this->createForm(CreateInvitedUserRequestType::class, $createRequest);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            throw new FormInvalidRequestException($form);
        }

        $user = $factory->createFromInvitedRequest($createRequest);
        if ($user->isFullUser()) {
            $limiter->check([
                new LimitCheck(UserCountInspector::class, 1),
            ]);

            $entityManager->persist($invitationReplacer->replaceByUser($user));
        }

        $entityManager->persist($user);
        $entityManager->flush();

        return $viewFactory->createSingle($user);
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
    public function sendInvitation(Request $request, MessengerInterface $messenger, LoggerInterface $logger): void
    {
        $sendRequest = new SendInviteRequest();

        $form = $this->createForm(SendInviteType::class, $sendRequest);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            throw new FormInvalidRequestException($form);
        }

        $messenger->send(Message::createFromUser(
            $sendRequest->getUser(),
            MailRegistry::INVITE_SEND_MESSAGE,
            ['url' => $sendRequest->getUrl()]
        ));

        $logger->debug('Registered in UserController::sendInvitation');
        $logger->debug(sprintf('Username: %s', $sendRequest->getUser()->getUsername()));
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
     * @Route("/api/invitations", name="api_send_invitations", methods={"POST"})
     *
     * @throws \Exception
     */
    public function sendInvitations(Request $request, MessengerInterface $messenger): void
    {
        $sendRequests = new SendInviteRequests();

        $form = $this->createForm(SendInvitesType::class, $sendRequests);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            throw new FormInvalidRequestException($form);
        }

        foreach ($sendRequests->getMessages() as $requestMessage) {
            $messenger->send(Message::createFromUser(
                $requestMessage->getUser(),
                MailRegistry::INVITE_SEND_MESSAGE,
                ['url' => $requestMessage->getUrl()]
            ));
        }
    }

    /**
     * @SWG\Tag(name="Invitation")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=CreateInvitedUserRequestType::class)
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Success user created update",
     *     @SWG\Schema(type="array", @Model(type=UserView::class))
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     *
     * @Route(path="/api/user/batch", methods={"POST"}, name="api_user_batch_create")
     *
     * @return UserView[]
     */
    public function batchCreateUser(
        Request $request,
        UserFactory $factory,
        UserViewFactory $viewFactory,
        InvitationReplacer $invitationReplacer,
        EntityManagerInterface $entityManager,
        LimiterInterface $limiter
    ): array {
        $batchRequest = new CreateBatchInvitedUserRequest();

        $form = $this->createForm(CreateBatchInvitedUserRequestType::class, $batchRequest);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            throw new FormInvalidRequestException($form);
        }

        $users = [];
        foreach ($batchRequest->getUsers() as $createRequest) {
            $users[] = $factory->createFromInvitedRequest($createRequest);
        }
        $fullUsers = array_filter($users, static function (User $user) {
            return $user->isFullUser();
        });

        $limiter->check([
            new LimitCheck(UserCountInspector::class, count($fullUsers)),
        ]);

        foreach ($fullUsers as $user) {
            $entityManager->persist($invitationReplacer->replaceByUser($user));
        }

        array_map([$entityManager, 'persist'], $users);
        $entityManager->flush();

        return $viewFactory->createCollection($users);
    }
}

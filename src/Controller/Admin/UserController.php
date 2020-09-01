<?php

namespace App\Controller\Admin;

use App\Controller\AbstractController;
use App\Entity\User;
use App\Mailer\FosUserMailer;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\CrudUrlGenerator;
use FOS\UserBundle\Event\GetResponseNullableUserEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Model\UserManagerInterface;
use FOS\UserBundle\Util\TokenGeneratorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    /**
     * @var SessionInterface|Session
     */
    private SessionInterface $session;

    private CrudUrlGenerator $crudUrlGenerator;

    public function __construct(
        SessionInterface $session,
        CrudUrlGenerator $crudUrlGenerator
    ) {
        $this->session = $session;
        $this->crudUrlGenerator = $crudUrlGenerator;
    }

    /**
     * @Route("/admin/users/{user}/reset-2fa", name="admin_user_reset_2fa", methods={"GET"})
     */
    public function reset2fa(User $user, UserManagerInterface $userManager)
    {
        $user->setGoogleAuthenticatorSecret(null);
        $userManager->updateUser($user);

        $this->session->getFlashBag()->set('info', 'Success resetting 2FA');

        return $this->buildRedirectResponse();
    }

    /**
     * @Route("/admin/users/{user}/reset-password", name="admin_user_reset_password", methods={"GET"})
     */
    public function resetPassword(
        Request $request,
        User $user,
        FosUserMailer $fosUserMailer,
        UserManagerInterface $userManager,
        TokenGeneratorInterface $tokenGenerator,
        EventDispatcherInterface $dispatcher
    ) {
        if ($user && is_null($user->getSrp())) {
            $this->session->getFlashBag()->set('danger', 'Invalid Srp');

            return $this->buildRedirectResponse();
        }

        $event = new GetResponseNullableUserEvent($user, $request);
        /**
         * @phpstan-ignore-next-line
         * @psalm-suppress InvalidArgument
         * @psalm-suppress TooManyArguments
         */
        $dispatcher->dispatch(FOSUserEvents::RESETTING_SEND_EMAIL_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        if (null !== $user) {
            $event = new GetResponseUserEvent($user, $request);
            /**
             * @phpstan-ignore-next-line
             * @psalm-suppress InvalidArgument
             * @psalm-suppress TooManyArguments
             */
            $dispatcher->dispatch(FOSUserEvents::RESETTING_RESET_REQUEST, $event);
            if (null !== $event->getResponse()) {
                return $event->getResponse();
            }

            $user->setConfirmationToken($tokenGenerator->generateToken());
            $user->setEnabled(false);

            $event = new GetResponseUserEvent($user, $request);
            /**
             * @phpstan-ignore-next-line
             * @psalm-suppress InvalidArgument
             * @psalm-suppress TooManyArguments
             */
            $dispatcher->dispatch(FOSUserEvents::RESETTING_SEND_EMAIL_CONFIRM, $event);
            if (null !== $event->getResponse()) {
                return $event->getResponse();
            }
            $fosUserMailer->sendResettingEmailMessage($user);
            $user->setPasswordRequestedAt(new \DateTime());
            $userManager->updateUser($user);
            $event = new GetResponseUserEvent($user, $request);
            /**
             * @phpstan-ignore-next-line
             * @psalm-suppress InvalidArgument
             * @psalm-suppress TooManyArguments
             */
            $dispatcher->dispatch(FOSUserEvents::RESETTING_SEND_EMAIL_COMPLETED, $event);
            if (null !== $event->getResponse()) {
                return $event->getResponse();
            }
        }

        $this->session->getFlashBag()->set('info', 'Success resetting password');

        return $this->buildRedirectResponse();
    }

    private function buildRedirectResponse(): RedirectResponse
    {
        return new RedirectResponse($this->crudUrlGenerator
            ->build()
            ->setController(UserCrudController::class)
            ->setAction(Action::INDEX)
        );
    }
}

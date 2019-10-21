<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Mailer\FosUserMailer;
use EasyCorp\Bundle\EasyAdminBundle\Controller\EasyAdminController as BaseController;
use EasyCorp\Bundle\EasyAdminBundle\Event\EasyAdminEvents;
use FOS\UserBundle\Doctrine\UserManager;
use FOS\UserBundle\Event\GetResponseNullableUserEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Util\TokenGeneratorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AdminController.
 */
class UserController extends BaseController
{
    /**
     * @var UserManager
     */
    private $userManager;
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;
    /**
     * @var TokenGeneratorInterface
     */
    private $tokenGenerator;
    /**
     * @var FosUserMailer
     */
    private $fosUserMailer;

    public function __construct(
        UserManager $userManager,
        EventDispatcherInterface $eventDispatcher,
        TokenGeneratorInterface $tokenGenerator,
        FosUserMailer $fosUserMailer
    )
    {
        $this->userManager = $userManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->tokenGenerator = $tokenGenerator;
        $this->fosUserMailer = $fosUserMailer;
    }

    public function createNewUserEntity(): User
    {
        $user = $this->userManager->createUser();
        $user->setPlainPassword(md5(uniqid('', true)));

        return $user;
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function reset_2faAction()
    {
        $id = $this->request->query->get('id');
        /** @var $user User */
        $user = $this->em->getRepository(User::class)->find($id);
        $this->em->flush();

        $user->setGoogleAuthenticatorSecret(null);
        $this->userManager->updateUser($user);

        return $this->redirectToRoute('easyadmin', array(
            'action' => 'list',
            'entity' => $this->request->query->get('entity'),
        ));
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function resetPasswordAction()
    {
        $id = $this->request->query->get('id');
        /** @var $user User */
        $user = $this->em->getRepository(User::class)->find($id);
        $this->em->flush();

        if (is_null($user->getSrp())) {
            return $this->redirectToRoute('easyadmin', array(
                'action' => 'list',
                'errors' => ['resetPassword' => 'Invalid Srp'],
                'entity' => $this->request->query->get('entity'),
            ));
        }

        $event = new GetResponseNullableUserEvent($user, $this->request);
        $this->eventDispatcher->dispatch(FOSUserEvents::RESETTING_SEND_EMAIL_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        if (null !== $user) {
            $event = new GetResponseUserEvent($user, $this->request);
            $this->eventDispatcher->dispatch(FOSUserEvents::RESETTING_RESET_REQUEST, $event);
            if (null !== $event->getResponse()) {
                return $event->getResponse();
            }

            $user->setConfirmationToken($this->tokenGenerator->generateToken());
            $user->setEnabled(false);

            $event = new GetResponseUserEvent($user, $this->request);
            $this->eventDispatcher->dispatch(FOSUserEvents::RESETTING_SEND_EMAIL_CONFIRM, $event);
            if (null !== $event->getResponse()) {
                return $event->getResponse();
            }
            $this->fosUserMailer->sendResettingEmailMessage($user);
            $user->setPasswordRequestedAt(new \DateTime());
            $this->userManager->updateUser($user);
            $event = new GetResponseUserEvent($user, $this->request);
            $this->eventDispatcher->dispatch(FOSUserEvents::RESETTING_SEND_EMAIL_COMPLETED, $event);
            if (null !== $event->getResponse()) {
                return $event->getResponse();
            }
        }

        return $this->redirectToRoute('easyadmin', array(
            'action' => 'list',
            'entity' => $this->request->query->get('entity'),
        ));
    }
}

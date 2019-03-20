<?php

declare(strict_types=1);

namespace App\Mailer;

use FOS\UserBundle\Mailer\Mailer;
use FOS\UserBundle\Mailer\MailerInterface;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Templating\EngineInterface;

class FosUserMailer extends Mailer implements MailerInterface
{
    const RESETTING_TEMPLATE = ':email:password_resetting.email.html.twig';
    const FROM_EMAIL = 'no-reply@caesar.team';

    /**
     * Mailer constructor.
     *
     * @param \Swift_Mailer         $mailer
     * @param UrlGeneratorInterface $router
     * @param EngineInterface       $templating
     */
    public function __construct(\Swift_Mailer $mailer, UrlGeneratorInterface  $router, EngineInterface $templating)
    {
        parent::__construct($mailer, $router, $templating, []);
    }

    /**
     * Send an email to a user to confirm the password reset.
     *
     * @param UserInterface $user
     */
    public function sendResettingEmailMessage(UserInterface $user)
    {
        $url = getenv('WEB_CLIENT_URL').'/resetting/'.$user->getConfirmationToken();

        $rendered = $this->templating->render('email/password_resetting.email.html.twig', array(
            'user' => $user,
            'confirmationUrl' => $url,
        ));
        $this->sendEmailMessage($rendered, self::FROM_EMAIL, (string) $user->getEmail());
    }
}
<?php

declare(strict_types=1);

namespace App\Mailer;

use FOS\UserBundle\Mailer\Mailer;
use FOS\UserBundle\Mailer\MailerInterface;
use FOS\UserBundle\Model\UserInterface;
use Swift_Mailer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Templating\EngineInterface;

class FosUserMailer extends Mailer implements MailerInterface
{
    public const RESETTING_TEMPLATE = ':email:password_resetting.email.html.twig';
    public const FROM_EMAIL = 'no-reply@caesar.team';

    /**
     * Mailer constructor.
     */
    public function __construct(Swift_Mailer $mailer, UrlGeneratorInterface $router, EngineInterface $templating)
    {
        parent::__construct($mailer, $router, $templating, []);
    }

    /**
     * Send an email to a user to confirm the password reset.
     */
    public function sendResettingEmailMessage(UserInterface $user): void
    {
        $webClientUrl = getenv('WEB_CLIENT_URL')
            ?: $this->router->generate('root', [], UrlGeneratorInterface::ABSOLUTE_URL);

        $url = $webClientUrl.'resetting/'.$user->getEmail().'/'.$user->getConfirmationToken();

        $rendered = $this->templating->render('email/password_resetting.email.html.twig', [
            'user' => $user,
            'confirmationUrl' => $url,
        ]);

        $senderAddress = getenv('SENDER_ADDRESS') ?: self::FROM_EMAIL;
        $this->sendEmailMessage($rendered, $senderAddress, (string) $user->getEmail());
    }
}

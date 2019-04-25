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
        $webClientUrl
            = getenv('WEB_CLIENT_URL') ?:
            $this->router->generate('root', [], UrlGeneratorInterface::ABSOLUTE_URL)
        ;
        $webClientUrl = preg_replace('/\\/$/', '', $webClientUrl);
        $url = $webClientUrl.'/resetting/'.$user->getEmail().'/'.$user->getConfirmationToken();

        $rendered = $this->templating->render('email/password_resetting.email.html.twig', array(
            'user' => $user,
            'confirmationUrl' => $url,
        ));

        $senderAddress = getenv('SENDER_ADDRESS') ?: self::FROM_EMAIL;
        $this->sendEmailMessage($rendered, $senderAddress, (string) $user->getEmail());
    }
}